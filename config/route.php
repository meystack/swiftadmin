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

// 验证码路由
Route::any('/captcha', [app\BaseController::class, 'captcha']);

/**
 * 默认管理员路由
 * @var string $manage
 */
Route::any('/manage', function () {
//    var_dump(request()->buildToken());
    request()->session()->set('AdminLogin', ['_security' => request()->buildToken()]);
    return redirect('/admin/login');
});

/**
 * 加载插件全局初始化路由
 * @var array $pluginRoute
 */
Route::any('/static/system/js/plugin.js', function () {
    $plugins = get_plugin_list();
    $pluginFiles = [];
    foreach ($plugins as $item) {
        if (!$item['status']) {
            continue;
        }
        // 是否存在javascript文件
        $pluginJs = plugin_path($item['name']) . 'plugin.js';
        if (file_exists($pluginJs)) {
            $pluginFiles[] = read_file($pluginJs);
        }
    }
    return $pluginFiles ? implode(PHP_EOL, $pluginFiles) : '';
});

/**
 * 全局404路由fallback
 * @var array $request
 * @var array $response
 */
Route::fallback(function () {

    $pathInfo = parse_url(request()->url());
    if (!isset($pathInfo['path'])) {
        $parseApp = ['index'];
    } elseif ($pathInfo['path'] == '/') {
        // 默认应用路由
        return redirect('/index');
    } else {
        $parseApp = explode('/', ltrim($pathInfo['path'], '/'));
    }

    // 判断是否为API接口
    if (request()->expectsJson()) {
        return json(['code' => 404, 'msg' => '404 not found']);
    }

    return response(request_error(current($parseApp)), 404);
});

// 执行插件初始化
$pluginList = get_plugin_list();
foreach ($pluginList as $item) {
    if (!$item['status']) {
        continue;
    }

    $routeList = $item['rewrite'] ?? [];
    foreach ($routeList as $route => $value) {
        $separator = explode('/', $value);
        $method = end($separator);
        array_pop($separator);
        $filePath = implode('\\', $separator);
        $controller = 'app\\index\\controller\\' . $filePath;
        if (class_exists($controller) && method_exists($controller, $method)) {
            try {
                $classFullName = (new ReflectionClass($controller))->getName();
                Route::any($route, [$classFullName, $method]);
            } catch (\Throwable $e) {
                var_dump('error: ' . $e->getMessage());
            }
        } else {
            var_dump("\033[31m$controller or $method does not exist!\033[0m");
        }
    }

}