<?php /** @noinspection ALL */

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
    public $needLogin = false;

    /**
     * API验证流程
     * @var bool
     */
    public $authWorkflow = true;

    /**
     * 非鉴权方法
     * @var array
     */
    public $noNeedAuth = [];

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
        $method = $controller . '/' . $action;
        $className = '\app' . $app . '\\controller\\' . $controller;
        $className = str_replace('/', '\\', $className);
        if (class_exists($className)) {
            $refClass = new \ReflectionClass($className);
            $property = $refClass->getDefaultProperties();
            $this->needLogin = $property['needLogin'] ?? false;
            $this->noNeedAuth = $property['noNeedAuth'] ?? [];
        }

        $auth = Auth::instance();
        if ($auth->isLogin()) {
            $request->userId = $auth->userInfo['id'];
            $request->userInfo = $auth->userInfo;
            if ($this->authWorkflow && Event::hasListener('apiAuth')) {
                $result = Event::emit('apiAuth', ['method' => $method, 'userId' => $request->userId], true);
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