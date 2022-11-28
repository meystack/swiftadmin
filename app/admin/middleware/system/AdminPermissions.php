<?php

namespace app\admin\middleware\system;

use support\View;
use app\admin\library\Auth;
use app\common\library\ResultCode;
use app\common\model\system\Admin as AdminModel;
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
    protected array $noNeedAuth = [
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
     * @throws ModelNotFoundException
     */
    public function process(Request $request, callable $handler): Response
    {
        $app        = request()->getApp();
        $controller = request()->getController();
        $action     = request()->getAction();
        $AdminLogin = request()->session()->get(AdminSession);
        if (!isset($AdminLogin['id']) && strtolower($controller) !== 'login') {
            return redirect(url('/login/index'));
        }

        // 判断是否需要鉴权
        $request->admin_id  = $AdminLogin['id'] ?? 0;
        $request->adminData = $AdminLogin ?? [];
        $method = '/' . $controller. '/' .$action;
        if (!in_array($method, $this->noNeedAuth) && !in_array('*', $this->noNeedAuth)) {
            if (!Auth::instance()->SuperAdmin() && !Auth::instance()->check($method, $request->admin_id)) {
                if (request()->isAjax()) {
                    return json(['code' => 101, 'msg' => '没有权限']);
                } else {
                    return $this->abortPage('没有权限!', 401);
                }
            }
        }

        // 控制器中间件分发
        $id = input('id');
        if (\request()->isPost()) {
            if ($controller == 'system/Admin') {
                if ($data = AdminModel::getById($id)) {
                    $group_id = input('group_id');
                    $group_id = !empty($group_id) ? $group_id . ',' . $data['group_id'] : $data['group_id'];
                    $group_id = array_unique(explode(',', $group_id));
                    if (!Auth::instance()->checkRulesForGroup($group_id)) {
                        return json(ResultCode::AUTH_ERROR);
                    }
                }
            } else if ($controller == 'system/AdminGroup') {
                if (!empty($id) && $id >= 1) {
                    if (!Auth::instance()->checkRulesForGroup((array)$id)) {
                        return json(ResultCode::AUTH_ERROR);
                    }
                }
            }
        }

        View::assign('app', $app);
        View::assign('controller', $controller);
        View::assign('action', $action);
        View::assign('AdminLogin', $AdminLogin);
        $this->writeAdminRequestLogs();
        return $handler($request);
    }

    /**
     * 写入后台操作日志
     * @throws InvalidArgumentException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function writeAdminRequestLogs()
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