<?php

namespace support;

use ReflectionClass;
use ReflectionException;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Cache\Psr16Cache;
use InvalidArgumentException;
use WeakMap;
use Workerman\Coroutine\Utils\DestructionWatcher;

/**
 * Class Cache
 * @package support\bootstrap
 *
 * Strings methods
 * @method static mixed get($key, $default = null)
 * @method static bool set($key, $value, $ttl = null)
 * @method static bool delete($key)
 * @method static bool clear()
 * @method static iterable getMultiple($keys, $default = null)
 * @method static bool setMultiple($values, $ttl = null)
 * @method static bool deleteMultiple($keys)
 * @method static bool has($key)
 */
class Cache
{
    /**
     * @var Psr16Cache[]
     */
    public static array $instances = [];

    /**
     * @var WeakMap
     */
    public static WeakMap $weakMap;

    /***
     * @param string|null $name
     * @return Psr16Cache
     * @throws ReflectionException
     */
    public static function store(?string $name = null): Psr16Cache
    {
        static::$weakMap ??= new WeakMap();

        $name = $name ?: config('cache.default', 'redis');
        $stores = !config('cache') ? [
            'redis' => [
                'driver' => 'redis',
                'connection' => 'default'
            ],
        ] : config('cache.stores', []);
        if (!isset($stores[$name])) {
            throw new InvalidArgumentException("cache.store.$name is not defined. Please check config/cache.php");
        }

        if (!isset(static::$instances[$name])) {
            $driver = $stores[$name]['driver'];
            switch ($driver) {
                case 'redis':
                    // Redis has pool, so we do not need to create pool.
                    $redis = Redis::connection($stores[$name]['connection']);
                    if (isset(static::$weakMap[$redis])) {
                        $cache = static::$weakMap[$redis];
                    } else {
                        $cache = new Psr16Cache(new RedisAdapter($redis->client()));
                        static::$weakMap[$redis] = $cache;
                        // When the redis instance is destroyed by the connection pool,
                        // the corresponding cache instance needs to be destroyed as well.
                        DestructionWatcher::watch($redis, function() use ($cache) {
                            $reflection = new ReflectionClass(Psr16Cache::class);
                            $property = $reflection->getProperty('createCacheItem');
                            $property->setAccessible(true);
                            $property->setValue($cache, null);
                        });
                    }
                    // Do not save the cache instance in static::$instances, because the cache instance is weakly referenced.
                    return $cache;
                case 'file':
                    $adapter = new FilesystemAdapter('', 0, $stores[$name]['path']);
                    break;
                case 'array':
                    $adapter = new ArrayAdapter(0, $stores[$name]['serialize'] ?? false, 0, 0);
                    break;
                case 'apcu':
                    try {
                      $adapter = new ApcuAdapter('', 0);
                    } catch (CacheException) {
                      throw new InvalidArgumentException("cache.store.$name.driver=$driver is not supported.");
                    }
                    break;
                /**
                 * Pdo can not reconnect when the connection is lost. So we can not use pdo as cache.
                 */
                /*case 'database':
                    $adapter = new PdoAdapter(Db::connection($stores[$name]['connection'])->getPdo());
                    break;*/
                default:
                    throw new InvalidArgumentException("cache.store.$name.driver=$driver is not supported.");
            }
            static::$instances[$name] = new Psr16Cache($adapter);
        }

        return static::$instances[$name];
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws ReflectionException
     */
    public static function __callStatic($name, $arguments)
    {
        return static::store()->{$name}(... $arguments);
    }
}
