<?php
declare (strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------

namespace app\common\middleware;
use Webman\Event\Event;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
/**
 * 应用全局中间件
 * Class AppInitialize
 * @package app\common\middleware
 * @author meystack <
 */
class AppInitialize implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if (in_array($request->app, config('app.deny_app_list'))) {
            return \response(request_error(), 404);
        }

        // 执行插件初始化钩子
        Event::emit('appInit', $request);
        return $handler($request);
    }
}