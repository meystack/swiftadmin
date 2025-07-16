<?php

namespace app\common\middleware;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 全局跨域中间件
 * @package app\common\middleware
 * @author  meystack
 */
class AccessCross implements MiddlewareInterface
{
    public function process(Request $request, callable $handler) : Response
    {
        $response = strtoupper($request->method()) === 'OPTIONS' ? response('', 204) : $handler($request);
        $header = [
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => 1800,
            'Access-Control-Allow-Methods'     => '*',
            'Access-Control-Allow-Headers'     => '*',
        ];

        $domains = array_merge(config('app.cors_domain'), [request()->host(true)]);
        $domains = array_unique($domains);
        $header['Access-Control-Allow-Origin'] = $request->header('Origin', $domains);
        $response->withHeaders($header);
        return $response;
    }
}