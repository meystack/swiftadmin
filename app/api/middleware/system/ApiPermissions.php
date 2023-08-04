<?php

namespace app\api\middleware\system;
use app\common\library\ResultCode;
use app\common\service\user\UserTokenService;
use Psr\SimpleCache\InvalidArgumentException;
use Webman\Event\Event;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

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
    public bool $needLogin = false;

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
     * @throws \ReflectionException
     */
    public function process(\support\Request|Request $request, callable $handler): Response
    {
        $app        = request()->getApp();
        $controller = request()->getController();
        $action     = request()->getAction();
        $method     = $controller . '/' . $action;
        $refClass = new \ReflectionClass($request->controller);
        $property = $refClass->getDefaultProperties();
        $this->needLogin = $property['needLogin'] ?? $this->needLogin;
        $this->noNeedLogin = $property['noNeedLogin'] ?? $this->noNeedLogin;
        $this->repeatLogin  = $property['repeatLogin'] ?? $this->repeatLogin;

        // 是否验证登录器
        $userInfo = UserTokenService::isLogin();
        if (!empty($userInfo) && isset($userInfo['id'])) {
            $request->userId = $userInfo['id'];
            $request->userInfo = $userInfo;
            // 是否验证API权限
            if ($this->authWorkflow && Event::hasListener('apiAuth')) {
                $result = Event::emit('apiAuth', ['method' => $method, 'user_id' => $userInfo['id']], true);
                if (isset($result['code']) && $result['code'] != 200) {
                    return json($result);
                }
            }
        } else {
            if ($this->needLogin && !in_array($action, $this->noNeedLogin)) {
                return json(ResultCode::AUTH_ERROR);
            }
        }

        return $handler($request);
    }
}