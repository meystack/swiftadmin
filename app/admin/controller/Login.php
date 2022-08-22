<?php

namespace app\admin\controller;

use app\AdminController;
use app\common\model\system\Admin;
use app\common\model\system\LoginLog;
use Psr\SimpleCache\InvalidArgumentException;
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Event\Event;
use Webman\Http\Request;

class Login extends AdminController
{
    /**
     * 初始化方法
     * @return void
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new Admin();
        $this->JumpUrl = '/admin/index';
    }

    /**
     * 登录函数
     * @return Response
     * @throws InvalidArgumentException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index(): \support\Response
    {
        // 禁止重复访问
        if (isset(request()->adminInfo['id'])) {
            return $this->redirect('/admin/index');
        }

        if (request()->isPost()) {

            $user = request()->post('name');
            $pwd = request()->post('pwd');
            $captcha = request()->post('captcha');
            if ((isset(request()->adminInfo['count'])
                    && request()->adminInfo['count'] >= 5)
                && (isset(request()->adminInfo['time'])
                    && request()->adminInfo['time'] >= strtotime('- 5 minutes'))
            ) {
                $error = '错误次数过多，请稍后再试！';
                $this->writeLoginLogs($error);
                return $this->error($error);
            }

            // 验证码
            if (isset(request()->adminInfo['isCaptcha'])) {
                if (!$captcha || !$this->captchaCheck($captcha)) {
                    $error = '验证码错误！';
                    $this->writeLoginLogs($error);
                    return $this->error($error);
                }
            }

            // 验证表单令牌
            if (!request()->checkToken('__token__', \request()->all())) {
                $error = '表单令牌错误！';
                $this->writeLoginLogs($error);
                return $this->error($error, '', ['token' => token()]);
            } else {

                $result = Admin::checkLogin($user, $pwd);
                if (empty($result)) {
                    request()->adminInfo['time'] = time();
                    request()->adminInfo['isCaptcha'] = true;
                    request()->adminInfo['count'] = isset(request()->adminInfo['count']) ? request()->adminInfo['count'] + 1 : 1;
                    request()->session()->set(AdminSession, request()->adminInfo);
                    $error = '用户名或密码错误！';
                    $this->writeLoginLogs($error);
                    Event::emit('adminLoginError', \request()->all());
                    return $this->error($error, '', ['token' => token()]);
                }

                if ($result['status'] !== 1) {
                    $error = '账号已被禁用！';
                    $this->writeLoginLogs($error);
                    return $this->error($error);
                }

                $result->login_ip = request()->getRemoteIp();
                $result->login_time = time();
                $result->count = $result->count + 1;

                try {

                    $result->save();
                    $session = array_merge(request()->adminInfo, $result->toArray());
                    request()->session()->set(AdminSession, $session);
                } catch (\Throwable $th) {
                    return $this->error($th->getMessage());
                }

                $success = '登录成功！';
                $this->writeLoginLogs($success, true);
                Event::emit('adminLoginSuccess', $result->toArray());
                return $this->success($success, $this->JumpUrl);
            }
        }

        return view('login/index', [
            'captcha' => request()->adminInfo['isCaptcha'] ?? false,
        ]);
    }

    /**
     * 写入登录日志
     * @param string $error
     * @param int $status
     * @return void
     */
    private function writeLoginLogs(string $error, int $status = 0)
    {
        $name = \request()->input('name');
        $userAgent = \request()->header('user-agent');
        $nickname = $this->model->where('name', $name)->value('nickname');
        if (preg_match('/.*?\((.*?)\).*?/', $userAgent, $matches)) {
            $user_os = substr($matches[1], 0, strpos($matches[1], ';'));
        } else {
            $user_os = '未知';
        }

        $user_browser = preg_replace('/[^(]+\((.*?)[^)]+\) .*?/','$1',$userAgent);

        $data = [
            'user_ip'      => request()->getRemoteIp(),
            'user_agent'   => $userAgent,
            'user_os'      => $user_os,
            'user_browser' => $user_browser,
            'name'         => $name,
            'nickname'     => $nickname ?? '未知',
            'error'        => $error,
            'status'       => $status,
        ];

        LoginLog::create($data);
    }
}