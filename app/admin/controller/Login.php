<?php

namespace app\admin\controller;

use app\admin\service\LoginService;
use app\common\exception\OperateException;
use support\Response;
use app\AdminController;
use app\common\model\system\Admin;
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
     * @throws OperateException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index(): Response
    {
        // 禁止重复访问
        $adminInfo = get_admin_info();
        if (isset($adminInfo['id'])) {
            return $this->redirect('/admin/index');
        }

        if (request()->isPost()) {
            $user = request()->post('name');
            $pwd = request()->post('pwd');
            $captcha = request()->post('captcha');
            validate(\app\common\validate\system\Admin::class)->scene('login')->check([
                'name' => $user,
                'pwd'  => $pwd,
            ]);

            LoginService::accountLogin($user, $pwd, $captcha, $adminInfo);
            return $this->success('登录成功！', $this->JumpUrl);
        }

        return view('login/index', [
            'captcha' => $session['isCaptcha'] ?? false,
        ]);
    }
}