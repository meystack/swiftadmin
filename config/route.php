<?php
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于 WebMan 开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2022-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------

use Webman\Route;
use think\facade\Cache;

// 加载自定义路由
$defineRoute = require __DIR__ . '/defineRoute.php';
if ($defineRoute && is_array($defineRoute)) {
    foreach ($defineRoute as $key => $value) {
        Route::any($key, $value);
    }
}

Route::any('/manage', function () {
    // 登录入口session缓存
    request()->session()->set('AdminLogin', ['_security' => request()->buildToken()]);
    return redirect('/admin/login');
});

Route::any('/captcha', [app\BaseController::class, 'captcha']);

// 遍历默认应用文件夹
$default_app = config('app.default_app', 'index');
$files = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator(root_path('app/' . $default_app), \FilesystemIterator::SKIP_DOTS),
    \RecursiveIteratorIterator::CHILD_FIRST
);

foreach ($files as $file) {

    if ($file->isDir()) {
        continue;
    }
    $filePath = str_replace('\\', '/', $file->getPathname());
    $fileExt = pathinfo($filePath, PATHINFO_EXTENSION);

    // 只处理PHP文件
    if (strpos(strtolower($filePath), '/controller/') === false || strtolower($fileExt) !== 'php') {
        continue;
    }

    // 获取路由URL路径
    $urlPath = str_replace(['/controller/', '/Controller/'], '/', substr(substr($filePath, strlen(app_path())), 0, -4));
    $urlPath = str_replace($default_app . '/', '', $urlPath);
    $className = str_replace('/', '\\', substr(substr($filePath, strlen(base_path())), 0, -4));
    if (!class_exists($className)) {
        continue;
    }

    $refClass = new \ReflectionClass($className);
    $className = $refClass->name;
    $methods = $refClass->getMethods(\ReflectionMethod::IS_PUBLIC);

    $route = function ($url, $action) {
        if (!in_array($url, get_routes()) && !empty($url)) {
            $url = strtolower($url);
            Route::any($url, $action);
        }
    };

    foreach ($methods as $item) {
        $action = $item->name;
        $magic = [
            '__construct', '__destruct', '__call', '__callStatic', '__get', '__set','__isset', '__unset', '__sleep', '__wakeup', '__toString',
            '__invoke', '__set_state', '__clone', '__autoload', '__debugInfo', 'initialize',
        ];
        if (in_array($action,$magic)) {
            continue;
        }
        if ($action === 'index') {
            if (strtolower(substr($urlPath, -6)) === '/index' && $urlPath === '/Index') {
                $route('/', [$className, $action]);
                $route(substr($urlPath, 0, -6), [$className, $action]);
            }
            $route($urlPath, [$className, $action]);
        }
        $route($urlPath . '/' . $action, [$className, $action]);
    }
}

Route::fallback(function ($request) {
    $pathInfo = parse_url(request()->url());
    if (!isset($pathInfo['path'])) {
        $parseApp = ['index'];
    } else {
        $parseApp = explode('/', ltrim($pathInfo['path'], '/'));
    }
    if ($request->expectsJson()) {
        return json(['code' => 404, 'msg' => '404 not found']);
    }
    return response(request_error(current($parseApp)), 404);
});