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
namespace app\index\controller;

use app\HomeController;
use Psr\SimpleCache\InvalidArgumentException;
use support\Response;

class Index extends HomeController
{
    /**
     * 前端首页
     * @return Response
     * @throws InvalidArgumentException
     */
    public function index(): Response
    {
        return $this->view('index/index', ['name' => 'meystack']);
    }
}

