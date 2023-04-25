<?php

namespace Webman\ThinkCache;

use Webman\Bootstrap;
use Workerman\Timer;
use think\facade\Cache;
use think\Container;
use think\DbManager;

class ThinkCache implements Bootstrap
{
    public static function start($worker)
    {
        $config = config('thinkcache');
        if (!$config) {
            return;
        }
        Cache::config($config);
        if ($worker && $config['default'] === 'redis') {
            Timer::add(55, function () {
                Cache::get('ping');
            });
        }

        if (class_exists(DbManager::class)) {
            $manager_instance = Container::getInstance()->make(DbManager::class);
            $manager_instance->setCache(Cache::store());
        }
    }
}
