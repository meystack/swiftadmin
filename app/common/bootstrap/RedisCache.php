<?php

namespace app\common\bootstrap;

use support\Cache;
use support\Log;
use think\Container;
use think\DbManager;
use Webman\Bootstrap;
use Workerman\Timer;
use Workerman\Worker;

class RedisCache implements Bootstrap
{
    public static function start(?Worker $worker)
    {
        // TODO: Implement start() method.
        if ($worker) {
            try {
                Timer::add(55, function () {
                    Cache::get('ping');
                });
            } catch (\Throwable $e) {
                Log::error('RedisCache error: ' . $e->getMessage());
            }
        }

        if (class_exists(DbManager::class)) {
            $manager_instance = Container::getInstance()->make(DbManager::class);
            $manager_instance->setCache(Cache::instance());
        }
    }
}