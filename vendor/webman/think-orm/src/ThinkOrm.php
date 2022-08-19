<?php

namespace Webman\ThinkOrm;

use Webman\Bootstrap;
use Workerman\Timer;
use think\Paginator;
use think\facade\Db;
use think\db\connector\Mysql;

class ThinkOrm implements Bootstrap
{
    // 进程启动时调用
    public static function start($worker)
    {
        $config = config('thinkorm');
        $default = $config['default'] ?? false;
        $connections = $config['connections'] ?? [];
        // 配置
        Db::setConfig($config);
        // 维持mysql心跳
        if ($worker) {
            Timer::add(55, function () use ($connections, $default) {
                if (!class_exists(Mysql::class, false)) {
                    return;
                }
                foreach ($connections as $key => $item) {
                    if ($item['type'] == 'mysql') {
                        try {
                            if ($key == $default) {
                                Db::query('select 1');
                            } else {
                                Db::connect($key)->query('select 1');
                            }
                        } catch (Throwable $e) {}
                    }
                }
                Db::getDbLog(true);
            });
        }
        Paginator::currentPageResolver(function ($pageName = 'page') {
            $page = request()->input($pageName, 1);
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int)$page >= 1) {
                return (int)$page;
            }
            return 1;
        });
    }
}
