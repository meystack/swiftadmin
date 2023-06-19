<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------
namespace app\common\service\notice;

use app\common\driver\notice\EmailDriver;
use app\common\exception\OperateException;
use app\common\model\system\User;
use app\common\model\system\UserValidate;
use PHPMailer\PHPMailer\Exception;
use Psr\SimpleCache\InvalidArgumentException;
use support\App;
use system\Random;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class EmailService
{
    /**
     * 发送间隔时间
     * @var int
     */
    const EMAIL_SEND_INTERVAL = 60;

    /**
     * 验证码过期时间
     * @var int
     */
    const EXPIRE_TIME = 5; //验证码过期时间（分钟）

    /**
     * 发送邮件
     * @param $email
     * @param $title
     * @param $content
     * @return bool
     * @throws \Exception
     */
    public static function send($email, $title, $content): bool
    {
        $eDriver = new EmailDriver();
        try {
            $eDriver->address($email)->Subject($title)->MsgHTML($content)->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return true;
    }

    /**
     * 发送验证码
     * @param $email
     * @param $event
     * @return bool
     * @throws OperateException
     * @throws InvalidArgumentException
     */
    public static function captcha($email, $event): bool
    {
        $result = self::getLastMsg($email, $event);
        if (!empty($result) && time() - strtotime($result['create_time']) < self::EMAIL_SEND_INTERVAL) {
            throw new OperateException(__('发送频繁'));
        }

        $userinfo = (new User())->where('email', $email)->findOrEmpty()->toArray();
        if (in_array($event, ['register', 'change']) && $userinfo) {
            throw new OperateException(__('当前邮箱已被注册'));
        } else if ($event == 'forgot' && !$userinfo) {
            throw new OperateException(__('当前邮箱不存在'));
        }

        $captcha = Random::number();
        $filePath = root_path() . 'extend/conf/tpl/captcha.tpl';
        if (!is_file($filePath)) {
            throw new OperateException(__('验证码模板不存在'));
        }

        $eDriver = new EmailDriver();
        $data = [$captcha, saenv('site_name'), date('Y-m-d H:i:s')];
        $content = str_replace(['{code}', '{site_name}', '{time}'], $data, read_file($filePath));
        try {

            // 发送邮件
            $eDriver->address($email)->Subject("验证码")->MsgHTML($content)->send();
            // 保存验证码
            (new UserValidate())->create([
                'code'   => $captcha,
                'event'  => $event,
                'email'  => $email,
                'status' => 1,
            ]);
        } catch (\Throwable $th) {
            throw new OperateException(__('验证码发送失败'));
        }

        return true;
    }

    /**
     * 校验验证码
     * @param $email
     * @param $code
     * @param $event
     * @return bool
     * @throws \Exception
     */
    public static function checkCaptcha($email, $code, $event): bool
    {
        $model = new UserValidate();
        $result = $model->where([
            ['event', '=', $event],
            ['email', '=', $email],
            ['code', '=', $code],
            ['status', '=', 1],
        ])->order("id", "desc")->findOrEmpty()->toArray();

        if (!empty($result)) {
            $model->where('id', $result['id'])->update(['status' => 0]);
            $expires = time() - strtotime($result['create_time']);
            if ($expires <= self::EXPIRE_TIME * 60) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取最后一条
     * @param string $email
     * @param string $event
     * @return array
     */
    public static function getLastMsg(string $email, string $event): array
    {
        return (new UserValidate())->where([
            ['email', '=', $email],
            ['event', '=', $event],
        ])->order('id', 'desc')->findOrEmpty()->toArray();
    }

    /**
     * 过滤邮箱格式
     * @param string $email
     * @return string
     */
    public static function filterEmail(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public static function testEmail(array $params = []): bool
    {
        $eDriver = new EmailDriver();
        return $eDriver->testEmail($params);
    }
}