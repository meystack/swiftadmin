<?php
declare (strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.NET High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------
namespace app;

use app\common\library\Auth;

/**
 * Api全局控制器基类
 * Class ApiController
 * @package app
 * @author meystack <
 */
class ApiController extends BaseController
{
    /**
     * 初始化方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->auth = Auth::instance();
    }
}
