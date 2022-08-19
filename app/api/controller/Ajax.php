<?php
declare (strict_types=1);

namespace app\api\controller;

use app\ApiController;

use app\common\library\Email;
use app\common\library\Sms;
use app\common\model\system\User;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 异步调用
 */
class Ajax extends ApiController
{

    public $needLogin = true;

    /**
     * 发送短信
     * @return mixed|void
     * @throws DataNotFoundException
     * @throws DbException
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

            $userinfo = User::getByMobile($mobile);
            if (in_array($event, ['register', 'changer']) && $userinfo) {
                return $this->error('当前手机号已被占用');
            } else if ($event == 'forgot' && !$userinfo) {
                return $this->error('当前手机号未注册');
            }

            if ($sms->send($mobile, $event)) {
                return $this->success("验证码发送成功！");
            } else {
                return $this->error($sms->getError());
            }
        }

        return json(['msg' => 'success', 'data' => 'Hello']);
    }

    /**
     * 发送邮件
     * @return mixed|void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
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

            $userinfo = User::getByEmail($email);
            if (in_array($event, ['register', 'changer']) && $userinfo) {
                return $this->error('当前邮箱已被注册');
            } else if ($event == 'forgot' && !$userinfo) {
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
