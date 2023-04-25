<?php

// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------

namespace app\index\controller;

use app\common\library\Email;
use app\common\library\Sms;
use app\common\model\system\User;
use app\HomeController;
use PHPMailer\PHPMailer\Exception;
use Psr\SimpleCache\InvalidArgumentException;
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\response\Json;

/**
 * Ajax控制器
 * @ 异步调用
 */
class Ajax extends HomeController
{
    /**
     * 首页
     */
    public function index(): Response
    {
        return \response('Hello SWIFT!', 200);
    }

    /**
     * 发送短信验证码
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public function smsSend()
    {
        if (request()->isPost()) {

            $mobile = input('mobile');
            $event = input('event', 'register');

            if (!is_mobile($mobile)) {
                return $this->error('手机号码不正确');
            }

            $sms = Sms::instance();
            $data = $sms->getLast($mobile);
            if ($data && (time() - strtotime($data['create_time'])) < 60) {
                return $this->error(__('发送频繁'));
            }

            $user = User::getByMobile($mobile);
            if (in_array($event, ['register', 'changer']) && $user) {
                return $this->error('当前手机号已被占用');
            } else if ($event == 'forgot' && !$user) {
                return $this->error('当前手机号未注册');
            }

            if ($sms->send($mobile, $event)) {
                return $this->success("验证码发送成功！");
            } else {
                return $this->error($sms->getError());
            }
        }
    }

    /**
     * 发送邮件验证码
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function emailSend()
    {
        if (request()->isPost()) {

            $email = input('email');
            $event = input('event', 'register');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->error('邮件格式不正确');
            }

            $Ems = Email::instance();
            $data = $Ems->getLast($email);

            if ($data && (time() - strtotime($data['create_time'])) < 60) {
                return $this->error(__('发送频繁'));
            }

            $user = User::getByEmail($email);
            if (in_array($event, ['register', 'changer']) && $user) {
                return $this->error('当前邮箱已被注册');
            } else if ($event == 'forgot' && !$user) {
                return $this->error('当前邮箱不存在');
            }

            if ($Ems->captcha($email, $event)->send()) {
                return $this->success("验证码发送成功！");
            } else {
                return $this->error($Ems->getError());
            }
        }
    }
}
