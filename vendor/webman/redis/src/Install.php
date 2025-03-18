<?php
namespace Webman\Redis;

class Install
{
    const WEBMAN_PLUGIN = true;

    /**
     * @var array
     */
    protected static array $pathRelation = [];

    /**
     * Install
     * @return void
     */
    public static function install(): void
    {
        $redis_file = config_path('redis.php');
        if (!is_file($redis_file)) {
            echo 'Create config/redis.php' . PHP_EOL;
            copy(__DIR__ . '/config/redis.php', $redis_file);
        }
        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall(): void
    {
        $redis_file = config_path('redis.php');
        if (is_file($redis_file)) {
            echo 'Remove config/redis.php' . PHP_EOL;
            unlink($redis_file);
        }
        self::uninstallByRelation();
    }

    /**
     * installByRelation
     * @return void
     */
    public static function installByRelation(): void
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path().'/'.substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0777, true);
                }
            }
            copy_dir(__DIR__ . "/$source", base_path()."/$dest");
        }
    }

    /**
     * uninstallByRelation
     * @return void
     */
    public static function uninstallByRelation(): void
    {
        foreach (static::$pathRelation as $source => $dest) {
            $path = base_path()."/$dest";
            if (!is_dir($path) && !is_file($path)) {
                continue;
            }
            remove_dir($path);
        }
    }
    
}