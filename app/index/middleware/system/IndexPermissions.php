<?php

namespace app\index\middleware\system;

use app\common\library\Auth;
use support\View;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

/**
 * 管理员权限
 * @package app\admin\middleware\system
 * @author meystack <
 */
class IndexPermissions implements MiddlewareInterface
{
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
     * 校验权限
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        $app = request()->getApp();
        $controller = request()->getController();
        $action = request()->getAction();

        // 控制器是否存在
        $className = '\app' . $app . '\\controller\\' . $controller;
        $className = str_replace('/', '\\', $className);

        if (class_exists($className)) {
            $refClass = new \ReflectionClass($className);
            $property = $refClass->getDefaultProperties();
            $this->needLogin = $property['needLogin'] ?? false;
            $this->noNeedAuth = $property['noNeedAuth'] ?? [];
            $this->repeatLogin = $property['repeatLogin'] ?? ['login', 'register'];
            $this->JumpUrl = $property['JumpUrl'] ?? '/user/index';
        }

        // 是否验证登录器
        $auth = Auth::instance();
        if ($auth->isLogin()) {
            $request->userId = $auth->userInfo['id'];
            $request->userInfo = $auth->userInfo;
            if (in_array($action, $this->repeatLogin)) {
                return redirect($this->JumpUrl);
            }

            View::assign('user', $auth->userInfo);
        } else {
            if ($this->needLogin && !in_array($action, $this->noNeedAuth)) {
                return redirect('/');
            }
        }

        return $handler($request);
    }
}