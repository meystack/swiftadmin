<?php

namespace app\admin\middleware\system;
use app\admin\enums\AdminEnum;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 管理员登录中间件
 * @package app\common\middleware
 * @author  meystack
 */
class AdminLogin implements MiddlewareInterface
{
    public function process(Request $request, callable $handler) : Response
    {
        $AdminLogin = \request()->session()->get(AdminEnum::ADMIN_SESSION);
        if (!isset($AdminLogin['_security'])) {
            $request->session()->delete(AdminEnum::ADMIN_SESSION);
            return response(request_error(), 404);
        }
        return $handler($request);
    }
}