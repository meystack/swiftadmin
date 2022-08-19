<?php

namespace app\admin\middleware\system;
use app\common\library\Auth;
use think\facade\Cache;
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
        $_security = Auth::instance()->getToken('_security');
        $_buildToken = 'salt_' . $_security;
        if (empty($_security) || !Cache::get($_buildToken)) {
            $request->session()->delete('AdminLogin');
            return response(request_error(), 404);
        }

        return $handler($request);
    }
}