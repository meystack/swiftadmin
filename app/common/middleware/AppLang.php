<?php
namespace app\common\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AppLang implements MiddlewareInterface
{
    public function process(Request $request, callable $handler) : Response
    {
        locale(session('lang', 'zh_CN'));
        return $handler($request);
    }
}