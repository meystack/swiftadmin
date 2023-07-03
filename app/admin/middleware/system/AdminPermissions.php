<?php

namespace app\admin\middleware\system;

use app\admin\enums\AdminEnum;
use app\admin\service\AuthService;
use support\View;
use app\common\model\system\SystemLog;
use Psr\SimpleCache\InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

/**
 * 管理员权限
 * @package app\admin\middleware\system
 * @author meystack <
 */
class AdminPermissions implements MiddlewareInterface
{

    /**
     * 不需要鉴权的方法
     * @var array
     */
    protected array $noNeedLogin = [
        '/Index/index',
        '/Login/index',
        '/Login/logout',
    ];

    /**
     * 校验权限
     * @param Request $request
     * @param callable $handler
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException|\ReflectionException
     */
    public function process(Request $request, callable $handler): Response
    {
        // 控制器鉴权
        $app = request()->getApp();
        $controller = request()->getController();
        $action = request()->getAction();
        $method = '/' . $controller . '/' . $action;

        $AdminLogin = request()->session()->get(AdminEnum::ADMIN_SESSION);
        if (!isset($AdminLogin['id']) && strtolower($controller) !== 'login') {
            return redirect(url('/login/index'));
        }

        // 获取管理员信息
        $request->adminInfo = $AdminLogin;
        $request->adminId = $AdminLogin['id'] ?? 0;

        // 获取权限列表
        $class = new \ReflectionClass($request->controller);
        $properties = $class->getDefaultProperties();
        $this->noNeedLogin = $properties['noNeedLogin'] ?? $this->noNeedLogin;
        // 开始校验菜单权限
        $authService = AuthService::instance();
        if (!in_array('*', $this->noNeedLogin)
            && !in_array(strtolower($method), array_map('strtolower', $this->noNeedLogin))) {
            $superAdmin = $authService->superAdmin();
            if (!$superAdmin && !$authService->permissions($method, $AdminLogin['id'])) {
                return request()->isAjax() ? json(['code' => 101, 'msg' => '没有权限']) : $this->abortPage('没有权限！', 401);
            }
        }

        // 分配当前管理员信息
        View::assign('app', $app);
        View::assign('controller', $controller);
        View::assign('action', $action);
        View::assign(AdminEnum::ADMIN_SESSION, $AdminLogin);
        self::writeAdminRequestLogs();
        return $handler($request);
    }

    /**
     * 写入后台操作日志
     * @throws InvalidArgumentException
     */
    public static function writeAdminRequestLogs()
    {
        if (saenv('system_logs')) {

            $actionLogs = [
                'module'     => request()->app,
                'controller' => request()->controller,
                'action'     => request()->action,
                'params'     => serialize(request()->all()),
                'method'     => request()->method(),
                'code'       => 200,
                'url'        => request()->url(),
                'ip'         => request()->getRealIp(),
                'name'       => session('AdminLogin.name'),
            ];

            if (empty($actionLogs['name'])) {
                $actionLogs['name'] = 'system';
            }

            $actionLogs['type'] = 2;
            SystemLog::write($actionLogs);
        }
    }

    /**
     * 错误页面
     * @param int $code
     * @param string $msg
     * @return \support\Response
     */
    public function abortPage(string $msg = '', int $code = 404): Response
    {
        $exception = config('app.exception_template');
        if (isset($exception[$code])) {
            $template = @file_get_contents($exception[$code]);
        } else {
            $template = $msg;
        }

        return \response($template, $code);
    }
}