<?php

namespace app\common\middleware;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

/**
 * 全局跨域中间件
 * @package app\common\middleware
 * @author  meystack
 */
class AccessCross implements MiddlewareInterface
{
    public function process(Request $request, callable $handler) : Response
    {
        $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
        $header = [
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => 1800,
            'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
        ];

        $origin = request()->server('HTTP_ORIGIN');
        $parseUrl = parse_url($origin);
        $domains = array_merge(config('app.cors_domain'), [request()->host(true)]);
        if (in_array("*", $domains) || in_array($origin, $domains)
            || (isset($parseUrl['host']) && in_array($parseUrl['host'], $domains))) {
            $header['Access-Control-Allow-Origin'] = $request->header('Origin', '*');
        }

        $response->withHeaders($header);
        return $response;
    }
}