<?php

namespace Webman\ThinkOrm;

use Webman\Bootstrap;
use Workerman\Timer;
use Throwable;
use think\Paginator;
use think\facade\Db;
use think\DbManager;
use think\Container;

class ThinkOrm implements Bootstrap
{
    // 进程启动时调用
    public static function start($worker)
    {
        $config = config('thinkorm');
        // 配置
        Db::setConfig($config);
        // 维持mysql心跳
        if ($worker) {
            if (class_exists(Container::class, false)) {
                $manager_instance = Container::getInstance()->make(DbManager::class);
            } else {
                $reflect = new \ReflectionClass(Db::class);
                $property = $reflect->getProperty('instance');
                $property->setAccessible(true);
                $manager_instance = $property->getValue();
            }
            Timer::add(55, function () use ($manager_instance) {
                $instances = [];
                if (method_exists($manager_instance, 'getInstance')) {
                    $instances = $manager_instance->getInstance();
                } else {
                    $reflect = new \ReflectionClass($manager_instance);
                    $property = $reflect->getProperty('instance');
                    $property->setAccessible(true);
                    $instances = $property->getValue($manager_instance);
                }
                foreach ($instances as $connection) {
                    /* @var \think\db\connector\Mysql $connection */
                    if ($connection->getConfig('type') == 'mysql') {
                        try {
                            $connection->query('select 1');
                        } catch (Throwable $e) {}
                    }
                }
                Db::getDbLog(true);
            });
        }

        // 自定义分页组件类
        $bootstrap = $config['connections'][$config['default']]['bootstrap'] ?? false;
        if($bootstrap && class_exists($bootstrap)){
            Paginator::maker(function ($items, $listRows, $currentPage, $total, $simple, $options) use ($bootstrap){
                return (new \ReflectionClass($bootstrap))->newInstanceArgs(func_get_args());
            });
        }


        Paginator::currentPageResolver(function ($pageName = 'page') {
            $request = request();
            if (!$request) {
                return 1;
            }
            $page = $request->input($pageName, 1);
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int)$page >= 1) {
                return (int)$page;
            }
            return 1;
        });

        // 设置分页url中域名与参数之间的path字符串
        Paginator::currentPathResolver(function (){
            $request = request();
            return $request ? $request->path() : '/';
        });
    }
}
