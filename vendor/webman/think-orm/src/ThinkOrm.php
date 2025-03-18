<?php

namespace Webman\ThinkOrm;

use think\Paginator;
use support\think\Db;
use Webman\Bootstrap;

class ThinkOrm implements Bootstrap
{
    /**
     * @var bool
     */
    private static bool $initialized = false;

    /**
     * @return void
     */
    public static function start($worker): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        $config = config('think-orm', config('thinkorm'));
        if (!$config) {
            return;
        }

        if (!class_exists(\think\facade\Db::class, false)) {
            class_alias(\support\think\Db::class, \think\facade\Db::class);
        }

        // 配置
        Db::setConfig($config);

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
