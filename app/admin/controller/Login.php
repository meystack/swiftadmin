<?php

namespace app\admin\controller;

use support\Response;
use Webman\Event\Event;
use app\AdminController;
use app\common\model\system\Admin;
use app\common\model\system\AdminLog;
use Psr\SimpleCache\InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

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
        if (isset(request()->adminData['id'])) {
            return $this->redirect('/admin/index');
        }

        if (request()->isPost()) {

            $user = request()->post('name');
            $pwd = request()->post('pwd');
            $captcha = request()->post('captcha');
            if ((isset(request()->adminData['count'])
                    && request()->adminData['count'] >= 5)
                && (isset(request()->adminData['time'])
                    && request()->adminData['time'] >= strtotime('- 5 minutes'))
            ) {
                $error = '错误次数过多，请稍后再试！';
                $this->writeLoginLogs($error);
                return $this->error($error);
            }

            // 验证码
            if (isset(request()->adminData['isCaptcha'])) {
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
                    request()->adminData['time'] = time();
                    request()->adminData['isCaptcha'] = true;
                    request()->adminData['count'] = isset(request()->adminData['count']) ? request()->adminData['count'] + 1 : 1;
                    request()->session()->set(AdminSession, request()->adminData);
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

                $result->login_ip = request()->getRealIp();
                $result->login_time = time();
                $result->count = $result->count + 1;

                try {

                    $result->save();
                    $session = array_merge(request()->adminData, $result->toArray());
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
            'captcha' => request()->adminData['isCaptcha'] ?? false,
        ]);
    }

    /**
     * 写入登录日志
     * @param string $error
     * @param int $status
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
            'user_ip'      => request()->getRealIp(),
            'user_agent'   => $userAgent,
            'user_os'      => $user_os,
            'user_browser' => $user_browser,
            'name'         => $name,
            'nickname'     => $nickname ?? '未知',
            'error'        => $error,
            'status'       => $status,
        ];

        AdminLog::create($data);
    }
}