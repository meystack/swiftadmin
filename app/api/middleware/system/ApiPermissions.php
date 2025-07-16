<?php

namespace app\api\middleware\system;

use app\common\exception\OperateException;
use app\common\library\ResultCode;
use app\common\service\user\UserService;
use app\common\service\user\UserTokenService;
use Psr\SimpleCache\InvalidArgumentException;
use Webman\Event\Event;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * API权限中间件
 * @package app\api\middleware\system
 * @author meystack <
 */
class ApiPermissions implements MiddlewareInterface
{
    /**
     * 控制器登录鉴权
     * @var bool
     */
    public bool $needLogin = true;

    /**
     * API验证流程
     * @var bool
     */
    public bool $authWorkflow = true;

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
     * 校验权限
     * @param \support\Request|Request $request
     * @param callable $handler
     * @return Response
     * @throws InvalidArgumentException
     * @throws \ReflectionException|OperateException
     */
    public function process(\support\Request|Request $request, callable $handler): Response
    {
        $app = request()->getApp();
        $controller = request()->getController();
        $action = request()->getAction();
        $method = $controller . '/' . $action;
        $refClass = new \ReflectionClass($request->controller);
        $property = $refClass->getDefaultProperties();
        $this->needLogin = $property['needLogin'] ?? $this->needLogin;
        $this->noNeedLogin = $property['noNeedLogin'] ?? $this->noNeedLogin;
        $this->repeatLogin = $property['repeatLogin'] ?? $this->repeatLogin;

        // 是否验证登录器// 默认首次访问login接口不会命中禁言时间
        // 会导致当前直接将登录的token和IMToken的有效返回
        $userInfo = UserTokenService::isLogin();
        if (!empty($userInfo) && isset($userInfo['id'])) {

            $request->userId = $userInfo['id'];
            $request->userInfo = $userInfo;

            // 判断是否用户被禁言
            UserService::isBanned($userInfo);

            // 是否验证API权限
            if ($this->authWorkflow && Event::hasListener('apiAuth')) {
                $result = Event::emit('apiAuth', ['method' => $method, 'user_id' => $userInfo['id']], true);
                if (isset($result['code']) && $result['code'] != 200) {
                    return json($result);
                }
            }
        } else {
            if ($this->needLogin && !in_array($action, $this->noNeedLogin)) {
                return json(ResultCode::PLEASE_LOGIN);
            }
        }

        return $handler($request);
    }
}