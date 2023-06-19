<?php

// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------

namespace app\index\controller;

use app\HomeController;
use support\Response;

/**
 * Ajax控制器
 * @ 异步调用
 */
class Ajax extends HomeController
{
    /**
     * 首页
     */
    public function index(): Response
    {
        return \response('Hello swiftadmin!', 200);
    }
}
