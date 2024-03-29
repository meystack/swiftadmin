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

namespace app\index\middleware\system;
use Psr\SimpleCache\InvalidArgumentException;
use support\View;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use function redirect;
use function root_path;

/**
 * 前台应用中间件
 * Class AppInitialize
 * @package app\index\middleware\system
 * @author meystack <
 */

class IndexInitialize implements MiddlewareInterface
{
    /**
     * @param Request $request
     * @param callable $handler
     * @return Response
     * @throws InvalidArgumentException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function process(Request $request, callable $handler): Response
    {
        if (!is_file(root_path('extend/conf') . 'install.lock')) {
            return redirect('/install/index');
        } else {
            try {
                if (saenv('site_status')) {
                    $content = file_get_contents(root_path('extend/conf/tpl') . 'close.tpl');
                    $content = str_replace('{text}', saenv('site_notice'), $content);
                    return \response($content, 503);
                }
            } catch (\Throwable $th) {
                return \response('Web site has been closed', 503);
            }
        }

        $siteInfo = saenv('site', true);
        if ($siteInfo && is_array($siteInfo)) {
            foreach ($siteInfo as $key => $value) {
                View::assign($key,$value);
            }
        }

        return $handler($request);
    }
}