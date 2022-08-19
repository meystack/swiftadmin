<?php

declare(strict_types=1);
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
 * 前台全局控制器基类
 * Class HomeController
 * @package app
 * @author meystack <
 */
class HomeController extends BaseController
{
    /**
     * 数据库实例
     * @var object
     */
    public $model = null;

    /**
     * 是否验证
     * @var bool
     */
    public $isValidate = true;

    /**
     * 验证场景
     * @var string
     */
    public $scene = '';

    /**
     * 控制器/类名
     * @var string
     */
    public $controller = null;

    /**
     * 控制器方法
     * @var string
     */
    public $action = null;

    /**
     * 操作状态
     * @var int
     */
    public $status = false;

    /**
     * 错误消息
     * @var string
     */
    public $error = null;

    /**
     * 接口权限
     * @var object
     */
    public $auth = null;

    /**
     * 控制器登录鉴权
     * @var bool
     */
    public $needLogin = false;

    /**
     * 禁止登录重复
     * @var array
     */
    public $repeatLogin = ['login', 'register'];

    /**
     * 非鉴权方法
     * @var array
     */
    public $noNeedAuth = [];

    /**
     * 跳转URL地址
     * @var string
     */
    public $JumpUrl = '/user/index';
    /**
     * 初始化函数
     */
    public function __construct()
    {
        // 获取权限实例
        parent::__construct();
        $this->auth = Auth::instance();
    }

    /**
     * 视图过滤
     * @param string $template
     * @param array $argc
     * @param string|null $app
     * @return \support\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function view(string $template = '', array $argc = [], string $app = 'index'): \support\Response
    {
        if (saenv('site_state')) {
            $site_mobile = saenv('site_mobile');
            if (!empty($site_mobile) && !saenv('site_type')) {
                $domain = parse_url($site_mobile, PHP_URL_HOST);
                if ($domain === request()->header('host')) {
                    return view($template, $argc, 'mobile');
                }
            }
            else if (request()->isMobile() && saenv('site_type')) {
                return view($template, $argc, 'mobile');
            }
        }

        return view($template, $argc, $app);
    }
}
