<?php
declare (strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com>  Apache 2.0 License
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\ApiController;

/**
 * API接口前端示例文件
 */
class Index extends ApiController
{
    // 首页展示
    public function index(): \support\Response
    {
        return json(['msg' => 'success', 'data' => 'Hello']);
    }

}
