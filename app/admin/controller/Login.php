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
        $session = get_admin_info();
        if (isset($session['id'])) {
            return $this->redirect('/admin/index');
        }

        if (request()->isPost()) {
            $user = request()->post('name');
            $pwd = request()->post('pwd');
            $captcha = request()->post('captcha');
            if ((isset($session['count']) && $session['count'] >= 5)
                && (isset($session['time']) && $session['time'] >= strtotime('- 5 minutes'))) {
                return $this->displayResponse('错误次数过多，请稍后再试！');
            }

            // 验证码
            if (isset($session['isCaptcha'])) {
                if (!$captcha || !$this->captchaCheck($captcha)) {
                    return $this->displayResponse('验证码错误！');
                }
            }

            // 验证表单令牌
            if (!request()->checkToken('__token__', request()->all())) {
                return $this->displayResponse('表单令牌错误！', ['token' => token()]);
            } else {

                $result = Admin::checkLogin($user, $pwd);
                if (empty($result)) {
                    $session['time'] = time();
                    $session['isCaptcha'] = true;
                    $session['count'] = isset($session['count']) ? $session['count'] + 1 : 1;
                    request()->session()->set(AdminSession, $session);
                    // 执行登录失败事件
                    Event::emit('adminLoginError', request()->all());
                    return $this->displayResponse('用户名或密码错误！', ['token' => token()]);
                }

                if ($result['status'] !== 1) {
                    return $this->displayResponse('账号已被禁用！');
                }

                $result->login_ip = request()->getRealIp();
                $result->login_time = time();
                $result->count = $result->count + 1;

                try {

                    $result->save();
                    $session = array_merge($session, $result->toArray());
                    request()->session()->set(AdminSession, $session);
                } catch (\Throwable $th) {
                    return $this->error($th->getMessage());
                }

                Event::emit('adminLoginSuccess', $result->toArray());
                return $this->displayResponse('登录成功！', [] , $this->JumpUrl);
            }
        }

        return view('login/index', [
            'captcha' => $session['isCaptcha'] ?? false,
        ]);
    }

    /**
     * 退出登录
     * @param string $msg
     * @param array $data
     * @param string $url
     * @return Response
     */
    private function displayResponse(string $msg = 'error', array $data = [], string $url = ''): Response
    {
        $this->adminLoginLog($msg, $url ? 1 : 0);
        return empty($url) ? $this->error($msg, $url, $data) : $this->success($msg, $url);
    }

    /**
     * 写入登录日志
     * @param string $error
     * @param int $status
     */
    private function adminLoginLog(string $error, int $status = 0)
    {
        $name = \request()->input('name');
        $userAgent = \request()->header('user-agent');
        $nickname = $this->model->where('name', $name)->value('nickname');
        if (preg_match('/.*?\((.*?)\).*?/', $userAgent, $matches)) {
            $user_os = substr($matches[1], 0, strpos($matches[1], ';'));
        } else {
            $user_os = '未知';
        }

        $user_browser = preg_replace('/[^(]+\((.*?)[^)]+\) .*?/', '$1', $userAgent);

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