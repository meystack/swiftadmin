<?php

namespace app\api\middleware\system;

use app\common\library\Auth;
use app\common\library\ResultCode;
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
     * 非鉴权方法
     * @var array
     */
    public array $noNeedAuth = [];

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
        $method     = $controller . '/' . $action;

        $refClass = new \ReflectionClass($request->controller);
        $property = $refClass->getDefaultProperties();
        $this->needLogin = $property['needLogin'] ?? $this->needLogin;
        $this->noNeedAuth = $property['noNeedAuth'] ?? $this->noNeedAuth;

        $auth = Auth::instance();
        if ($auth->isLogin()) {
            // 验证权限
            if ($this->authWorkflow && Event::hasListener('apiAuth')) {
                $result = Event::emit('apiAuth', ['method' => $method, 'user_id' => $auth->user_id], true);
                if (isset($result['code']) && $result['code'] != 200) {
                    return json($result);
                }
            }
        } else {
            if ($this->needLogin && !in_array($action, $this->noNeedAuth)) {
                return json(ResultCode::AUTH_ERROR);
            }
        }

        return $handler($request);
    }
}