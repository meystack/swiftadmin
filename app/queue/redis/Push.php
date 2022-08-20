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
namespace app\queue\redis;

use app\AdminController;
use Webman\Push\Api;

class Push extends AdminController
{
    /**
     * api推送
     * @var null
     */
    protected $api = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * 消息推送首页
     * @return mixed
     */
    public function index()
    {
        return response('success');
    }
}