<?php

declare(strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com>  MIT License Code
// +----------------------------------------------------------------------
namespace app\index\controller;

use app\common\library\ResultCode;
use app\HomeController;
use app\common\library\Sms;
use app\common\library\Email;
use app\common\library\Upload;
use app\common\model\system\User as UserModel;
use support\Request;
use support\Response;
use system\Random;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class User extends HomeController
{
    /**
     * 鉴权控制器
     */
    public $needLogin = true;

    /**
     * 非登录鉴权方法
     */
    public $noNeedAuth = ['login', 'register', 'forgot', 'check'];

    public function __construct()
    {
        parent::__construct();
        $this->model = new UserModel();
    }

    /**
     * 退出登录
     * @return Response
     * @throws \Exception
     */
    public function logout(): \support\Response
    {
        $this->auth->logout();
        return $this->success('退出成功', url('index/index'));
    }

    /**
     * 用户中心
     * @return mixed
     */
    public function index(): \support\Response
    {
        return view('/user/index');
    }

    /**
     * 用户注册
     * @return \support\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function register(): \support\Response
    {
        if (request()->isPost()) {

            // 获取参数
            $post = \request()->post();
            $post = request_validate_rules($post, get_class($this->model));

            if (!is_array($post)) {
                return $this->error($post);
            }

            // 手机号注册
            if (saenv('user_register') == 'mobile') {
                $mobile = input('mobile');
                $captcha = input('captcha');
                if (!Sms::instance()->check($mobile, $captcha, 'register')) {
                    return $this->error(Sms::instance()->getError());
                }
            }

            $response = $this->auth->register($post);
            if (!$response) {
                return $this->error($this->auth->getError());
            }

            return $response->withBody(json_encode(ResultCode::REGISTERSUCCESS));
        }

        return view('/user/register', [
            'style' => saenv('user_register'),
        ]);
    }

    /**
     * 用户登录
     * @return \support\Response
     */
    public function login(): \support\Response
    {

        if (request()->isPost()) {
            $nickname = \request()->post('nickname');
            $password = \request()->post('pwd');

            $response = $this->auth->login($nickname, $password);
            if (!$response) {
                return $this->error($this->auth->getError());
            }

            $response->withBody(json_encode(ResultCode::LOGINSUCCESS));
            return $response;
        }

        return view('/user/login', [
            'referer' => request()->server('HTTP_REFERER', '/'),
        ]);
    }

    /**
     * 找回密码
     * @return \support\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function forgot(): \support\Response
    {
        if (request()->isPost()) {

            $email = input('email');
            $mobile = input('mobile');
            $event = input('event');
            $captcha = input('captcha');
            $pwd = input('pwd');

            if (!empty($email)) {
                if (!Email::instance()->check($email, $captcha, $event)) {
                    return $this->error(Email::instance()->getError());
                }
            } else {
                if (!Sms::instance()->check($mobile, $captcha, $event)) {
                    return $this->error(Sms::instance()->getError());
                }
            }

            $where = $email ? ['email' => $email] : ['mobile' => $mobile];
            $userInfo = $this->model->where($where)->find();
            if (!$userInfo) {
                return $this->error('用户不存在');
            }

            try {
                $salt = Random::alpha();
                $pwd = encryptPwd($pwd, $salt);
                $this->model->update(['id' => $userInfo['id'], 'pwd' => $pwd, 'salt' => $salt]);

            } catch (\Exception $e) {
                return $this->error('修改密码失败，请联系管理员');
            }

            return $this->success('修改密码成功！');
        }

        return view('/user/forgot');
    }

    /**
     * 用户资料
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function profile(Request $request): \support\Response
    {
        if (request()->isPost()) {

            $nickname = input('nickname');
            $post = request_validate_rules(request()->Post(), get_class($this->model), 'nickname');
            if (!is_array($post)) {
                return $this->error($post);
            }

            if ($this->model->where('nickname', $nickname)->find()) {
                return $this->error('当前昵称已被占用，请更换！');
            }

            if ($this->model->update(['id' => $request->userId, 'nickname' => $nickname])) {
                return $this->success('修改昵称成功！', (string)url('/user/index'));
            }

            return $this->error();
        }
        return view('/user/profile');
    }

    /**
     * 修改密码
     * @param Request $request
     * @return Response
     */
    public function changePwd(Request $request): \support\Response
    {
        if (request()->isPost()) {

            // 获取参数
            $pwd = input('pwd');
            $oldPwd = input('oldpwd');
            $yPwd = encryptPwd($oldPwd, $request->userInfo->salt);

            if ($yPwd != $request->userInfo->pwd) {
                return $this->error('原密码输入错误！');
            }

            $salt = Random::alpha();
            $pwd = encryptPwd($pwd, $salt);
            $result = $this->model->update(['id' => $request->userId, 'pwd' => $pwd, 'salt' => $salt]);
            if (!empty($result)) {
                return $this->success('修改密码成功！');
            }

            return $this->error();
        }

        return view('/user/changepwd');
    }

    /**
     * 申请appKey
     * @return Response|void
     */
    public function appid(Request $request)
    {
        if (request()->isPost()) {
            $data = array();
            $data['id'] = $request->userId;
            $data['app_id'] = 10000 + $request->userId;
            $data['app_secret'] = Random::alpha(22);
            if ($this->model->update($data)) {
                return $this->success();
            }
            return $this->error();
        }
    }

    /**
     * 修改邮箱
     * @return \support\Response
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function email(Request $request): \support\Response
    {
        if (request()->isPost()) {

            $email = input('email');
            $event = input('event');
            $captcha = input('captcha');

            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->error("您输入的邮箱格式不正确！");
            }

            if (UserModel::getByEmail($email)) {
                return $this->error("您输入的邮箱已被占用！");
            }

            $Ems = Email::instance();
            if (!empty($email) && !empty($captcha)) {

                if ($Ems->check($email, $captcha, $event)) {
                    $this->model->update(['id' => $request->userId, 'email' => $email]);
                    return $this->success('修改邮箱成功！');
                }

                return $this->error($Ems->getError());
            }

            $last = $Ems->getLast($email);
            if ($last && (time() - strtotime($last['create_time'])) < 60) {
                return $this->error(__('发送频繁'));
            }

            if ($Ems->captcha($email, $event)->send()) {
                return $this->success("邮件发送成功，请查收！");
            } else {
                return $this->error($Ems->getError());
            }
        }

        return view('/user/email');
    }


    /**
     * 修改手机号
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function mobile(Request $request): Response
    {

        if (request()->isPost()) {

            $mobile = input('mobile');
            $event = input('event');
            $captcha = input('captcha');

            if (!is_mobile($mobile)) {
                return $this->error('手机号码不正确');
            }

            if ($mobile && UserModel::getByMobile($mobile)) {
                return $this->error("您输入的手机号已被占用！");
            }

            $Sms = Sms::instance();
            if (!empty($mobile) && !empty($captcha)) {

                if ($Sms->check($mobile, $captcha, $event)) {
                    $this->model->update(['id' => $request->userId, 'mobile' => (int)$mobile]);
                    return $this->success('修改手机号成功！');
                }

                return $this->error($Sms->getError());
            } else {

                $data = $Sms->getLast($mobile);
                if ($data && (time() - strtotime($data['create_time'])) < 60) {
                    return $this->error(__('发送频繁'));
                }

                if ($Sms->send($mobile, $event)) {
                    return $this->success("验证码发送成功");
                } else {
                    return $this->error($Sms->getError());
                }
            }
        }

        return view('/user/mobile');
    }

    /**
     * 设置密保
     * @param Request $request
     * @return Response
     */
    public function protection(Request $request): \support\Response
    {
        $validate = [
            '你家的宠物叫啥？',
            '你的幸运数字是？',
            '你不想上班的理由是？',
        ];

        if (request()->isPost()) {
            $question = input('question');
            $answer = input('answer');

            if (!$question || !$answer) {
                return $this->error('设置失败');
            }

            if (!in_array($question, $validate)) {
                $question = current($validate);
            }

            try {
                $request->userInfo->question = $question;
                $request->userInfo->answer = $answer;
                $request->userInfo->save();
            } catch (\Throwable $th) {
                return $this->error();
            }

            return $this->success();
        }

        return view('/user/protection', [
            'validate' => $validate
        ]);
    }

    /**
     * 安全配置中心
     * @param Request $request
     * @return Response
     */
    public function security(Request $request): Response
    {
        $maxProgress = 5;
        $thisProgress = 1;

        if ($request->userInfo->email) {
            $thisProgress++;
        }

        if ($request->userInfo->mobile) {
            $thisProgress++;
        }

        if ($request->userInfo->answer) {
            $thisProgress++;
        }

        if ($request->userInfo->wechat) {
            $thisProgress++;
        }

        // 计算比例
        $progress = (($thisProgress / $maxProgress) * 100);
        return view('/user/security', [
            'progress' => $progress,
        ]);
    }

    /**
     * 用户头像上传
     * @param Request $request
     * @return Response|void
     * @throws \Exception
     */
    public function avatar(Request $request)
    {
        if (request()->isPost()) {
            $response = Upload::instance()->upload();
            if (!$response) {
                return $this->error(Upload::instance()->getError());
            }
            $request->userInfo->avatar = $response['url'] . '?' . Random::alpha(12);
            if ($request->userInfo->save()) {
                return json($response);
            }
        }

        return json(ResultCode::SUCCESS);
    }

    /**
     * 文件上传函数
     * @return mixed
     * @throws \Exception
     */
    public function upload()
    {
        if (request()->isPost()) {
            $file = Upload::instance()->upload();
            if (!$file) {
                return $this->error(Upload::instance()->getError());
            }
            return json($file);
        }

        return json(ResultCode::SUCCESS);
    }
}
