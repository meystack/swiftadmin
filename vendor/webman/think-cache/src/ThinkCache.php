<?php

namespace Webman\ThinkCache;

use Webman\Bootstrap;
use Workerman\Timer;
use think\facade\Cache;

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
    }
}