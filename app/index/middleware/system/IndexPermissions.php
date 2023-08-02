<?php

namespace app\index\middleware\system;

use app\common\library\ResultCode;
use app\common\service\user\UserTokenService;
use Psr\SimpleCache\InvalidArgumentException;
use support\View;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

/**
 * 前端权限
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
    public array $noNeedLogin = [];

    /**
     * 跳转URL地址
     * @var string
     */
    public string $JumpUrl = '/index/';

    /**
     * 校验权限
     * @param \support\Request|Request $request
     * @param callable $handler
     * @return Response
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function process(\support\Request|Request $request, callable $handler): Response
    {
        $app        = request()->getApp();
        $controller = request()->getController();
        $action     = request()->getAction();

        $refClass = new \ReflectionClass($request->controller);
        $property = $refClass->getDefaultProperties();
        $this->needLogin    = $property['needLogin'] ?? false;
        $this->noNeedLogin   = $property['noNeedLogin'] ?? $this->noNeedLogin;
        $this->repeatLogin  = $property['repeatLogin'] ?? $this->repeatLogin;
        $this->JumpUrl      = $property['JumpUrl'] ?? $this->JumpUrl;

        // 是否验证登录器
        $userInfo = UserTokenService::isLogin();
        if (!empty($userInfo) && isset($userInfo['id'])) {
            if (in_array($action, $this->repeatLogin)) {
                return redirect($this->JumpUrl);
            }

            $request->userId = $userInfo['id'];
            $request->userInfo = $userInfo;
            View::assign('user', $userInfo);
        } else {
            if ($this->needLogin && !in_array($action, $this->noNeedLogin)) {
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