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

/**
 * 插件控制器基类
 * Class PluginController
 * @package app
 * @author meystack <
 */
abstract class PluginController
{
    /**
     * 视图实例对象
     * @var null
     */
    public $view = null;

    /**
     * 构造方法
     */
    public function __construct()
    {}

    /**
     * 获取当前插件名
     * @return string
     */
    final public function getPluginName(): string
    {
        $data = explode('\\', get_class($this));
        return strtolower(array_pop($data));
    }

    /**
     * 必须实现以下方法
     * @return mixed
     */
    abstract public function install();
    abstract public function uninstall();
    abstract public function enabled();
    abstract public function disabled();
}