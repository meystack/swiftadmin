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

use app\common\service\user\UserService;
use support\Response;

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
    }

    /**
     * 操作成功跳转
     * @access protected
     * @param mixed $msg
     * @param mixed $url
     * @param mixed $data
     * @param int $count
     * @param int $code
     * @param int $wait
     * @param array $header
     * @return Response
     */
    protected function success(mixed $msg = '', mixed $url = '', mixed $data = '', int $count = 0, int $code = 200, int $wait = 3, array $header = []): Response
    {
        $msg = !empty($msg) ? __($msg) : __('操作成功！');
        $result = ['code' => $code, 'msg' => $msg, 'data' => $data, 'count' => $count, 'url' => (string)$url, 'wait' => $wait];
        return json($result);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param mixed $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param int $code
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     * @return Response
     */
    protected function error(mixed $msg = '', mixed $url = '', mixed $data = '', int $code = 101, int $wait = 3, array $header = []): Response
    {
        $msg = !empty($msg) ? __($msg) : __('操作失败！');
        $result = ['code' => $code, 'msg' => $msg, 'data' => $data, 'url' => (string)$url, 'wait' => $wait];
        return json($result);
    }

    /**
     * 退出登录
     * @access public
     */
    public function logOut(): Response
    {
        UserService::logout();
        return $this->success('退出成功', '/');
    }
}
