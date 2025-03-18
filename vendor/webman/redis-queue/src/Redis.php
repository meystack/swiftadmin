<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Webman\RedisQueue;

use RedisException;
use Webman\Context;
use Workerman\Coroutine\Pool;

/**
 * Class RedisQueue
 * @package support
 *
 * Strings methods
 * @method static bool send($queue, $data, $delay=0)
 */
class Redis
{

    /**
     * @var Pool[]
     */
    protected static array $pools = [];

    /**
     * @param string $name
     * @return RedisConnection
     */
    public static function connection($name = 'default') {
        $name = $name ?: 'default';
        $key = "redis-queue.connections.$name";
        $connection = Context::get($key);
        if (!$connection) {
            if (!isset(static::$pools[$name])) {
                $configs = config('redis_queue', config('plugin.webman.redis-queue.redis', []));
                if (!isset($configs[$name])) {
                    throw new \RuntimeException("RedisQueue connection $name not found");
                }
                $config = $configs[$name];
                $pool = new Pool($config['pool']['max_connections'] ?? 10, $config['pool'] ?? []);
                $pool->setConnectionCreator(function () use ($config) {
                    return static::connect($config);
                });
                $pool->setConnectionCloser(function ($connection) {
                    $connection->close();
                });
                $pool->setHeartbeatChecker(function ($connection) {
                    return $connection->ping();
                });
                static::$pools[$name] = $pool;
            }

            try {
                $connection = static::$pools[$name]->get();
                Context::set($key, $connection);
            } finally {
                Context::onDestroy(function () use ($connection, $name) {
                    try {
                        $connection && static::$pools[$name]->put($connection);
                    } catch (Throwable) {
                        // ignore
                    }
                });
            }
        }
        return $connection;
    }

    /**
     * Connect to redis.
     *
     * @param $config
     * @return RedisConnection
     * @throws RedisException
     */
    protected static function connect($config): RedisConnection
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('Please make sure the PHP Redis extension is installed and enabled.');
        }
        $redis = new RedisConnection();
        $address = $config['host'];
        $config = [
            'host' => parse_url($address, PHP_URL_HOST),
            'port' => parse_url($address, PHP_URL_PORT),
            'db' => $config['options']['database'] ?? $config['options']['db'] ?? 0,
            'auth' => $config['options']['auth'] ?? '',
            'timeout' => $config['options']['timeout'] ?? 2,
            'ping' => $config['options']['ping'] ?? 55,
            'prefix' => $config['options']['prefix'] ?? '',
        ];
        $redis->connectWithConfig($config);
        return $redis;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::connection('default')->{$name}(... $arguments);
    }
}
