<?php

namespace app\index\middleware\system;

use app\common\library\Auth;
use app\common\library\ResultCode;
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
    public bool $needLogin = false;

    /**
     * 禁止登录重复
     * @var array
     */
    public array $repeatLogin = ['login', 'register'];

    /**
     * 非鉴权方法
     * @var array
     */
    public array $noNeedAuth = [];

    /**
     * 跳转URL地址
     * @var string
     */
    public string $JumpUrl = '/index/user/index';

    /**
     * 校验权限
     * @param Request $request
     * @param callable $handler
     * @return Response
     * @throws \ReflectionException
     */
    public function process(Request $request, callable $handler): Response
    {
        $app        = request()->getApp();
        $controller = request()->getController();
        $action     = request()->getAction();

        $refClass = new \ReflectionClass($request->controller);
        $property = $refClass->getDefaultProperties();
        $this->needLogin    = $property['needLogin'] ?? false;
        $this->noNeedAuth   = $property['noNeedAuth'] ?? $this->noNeedAuth;
        $this->repeatLogin  = $property['repeatLogin'] ?? $this->repeatLogin;
        $this->JumpUrl      = $property['JumpUrl'] ?? $this->JumpUrl;

        // 是否验证登录器
        $auth = Auth::instance();
        if ($auth->isLogin()) {

            // 禁止重复登录
            if (in_array($action, $this->repeatLogin)) {
                return redirect($this->JumpUrl);
            }

            View::assign('user', $auth->userInfo);
        } else {

            if ($this->needLogin && !in_array($action, $this->noNeedAuth)) {
                if (\request()->isAjax()) {
                    return json(ResultCode::PLEASELOGININ);
                } else {
                    return redirect('/index/user/login');
                }
            }
        }

        return $handler($request);
    }
}