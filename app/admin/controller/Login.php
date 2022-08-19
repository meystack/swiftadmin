<?php

namespace app\admin\controller;

use app\AdminController;
use app\common\model\system\Admin;
use app\common\model\system\LoginLog;
use Webman\Event\Event;
use Webman\Http\Request;

class Login extends AdminController
{
    /**
     * 初始化方法
     * @param Request $request
     * @return \support\Response|void
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
     * @return \support\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(): \support\Response
    {
        // 禁止重复访问
        if (isset($this->admin['id'])) {
            return $this->redirect('/admin/index');
        }

        if (request()->isPost()) {

            $user = request()->post('name');
            $pwd = request()->post('pwd');
            $captcha = request()->post('captcha');
            if ((isset($this->admin['count'])
                    && $this->admin['count'] >= 5)
                && (isset($this->admin['time'])
                    && $this->admin['time'] >= strtotime('- 5 minutes'))
            ) {
                $error = '错误次数过多，请稍后再试！';
                $this->writeLoginLogs($error);
                return $this->error($error);
            }

            // 验证码
            if (isset($this->admin['isCaptcha'])) {
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
                    $this->admin['time'] = time();
                    $this->admin['isCaptcha'] = true;
                    $this->admin['count'] = isset($this->admin['count']) ? $this->admin['count'] + 1 : 1;
                    \request()->session()->set($this->sename, $this->admin);
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
                    request()->session()->set($this->sename, $result->toArray());
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
            'captcha' => $this->admin['isCaptcha'] ?? false,
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