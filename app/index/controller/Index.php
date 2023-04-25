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
use support\Response;
use Psr\SimpleCache\InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class Index extends HomeController
{
    /**
     * 前端首页
     * @return Response
     * @throws InvalidArgumentException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index(): Response
    {
        $data = [
            '欢迎使用swiftAdmin极速开发框架',
            __DIR__.'\Index.php 正在使用halt函数输出到浏览器',
            '请在app\index\controller\Index.php中删除halt函数',
        ];

        halt($data);
        return $this->view('index/index', ['name' => 'meystack']);
    }
}

