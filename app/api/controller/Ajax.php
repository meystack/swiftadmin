<?php
declare (strict_types=1);

namespace app\api\controller;

use app\ApiController;
use app\common\exception\OperateException;
use app\common\service\notice\EmailService;
use app\common\service\notice\SmsService;
use Psr\SimpleCache\InvalidArgumentException;
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 异步调用
 */
class Ajax extends ApiController
{
    /**
     * 首页
     */
    public function index(): Response
    {
        return response('Hello swiftadmin!');
    }

    /**
     * 发送短信验证码
     * @return Response
     * @throws InvalidArgumentException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function smsSend(): Response
    {
        $mobile = input('mobile', '');
        $event = input('event', 'register');
        if (!SmsService::filterMobile($mobile)) {
            return $this->error('手机号码不正确');
        }
        SmsService::send($mobile, $event);
        return $this->success("验证码发送成功！");
    }

    /**
     * 发送邮件验证码
     * @return Response
     * @throws InvalidArgumentException
     * @throws OperateException
     */
    public function emailSend(): Response
    {
        $email = input('email');
        $event = input('event', 'register');
        if (!EmailService::filterEmail($email)) {
            return $this->error('邮件格式不正确');
        }
        EmailService::captcha($email, $event);
        return $this->success("验证码发送成功！");
    }
}
