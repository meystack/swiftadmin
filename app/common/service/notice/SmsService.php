<?php
declare (strict_types=1);
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

use app\common\exception\OperateException;
use app\common\model\system\User;
use app\common\model\system\UserValidate;
use Psr\SimpleCache\InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Event\Event;

class SmsService
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
     * 类构造函数
     * class constructor
     * @access public
     */
    public function __construct()
    {}

    /**
     * 发送短信
     * @param $mobile
     * @param $event
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public static function send($mobile, $event): bool
    {
        $result = self::getLastMsg($mobile, $event);
        if (!empty($result)
            && time() - strtotime($result['create_time']) < self::EMAIL_SEND_INTERVAL) {
            throw new OperateException(__('发送频繁'));
        }

        $userinfo = (new User())->where('mobile', $mobile)->findOrEmpty()->toArray();
        if (in_array($event, ['register', 'change']) && $userinfo) {
            throw new OperateException(__('当前手机号已注册'));
        } else if ($event == 'forgot' && !$userinfo) {
            throw new OperateException(__('当前手机号未注册'));
        }

        if (!Event::hasListener('smsMsgSend')) {
            throw new OperateException(__('短信插件未安装'));
        }

        list('type' => $smsType, 'config' => $config) = self::getSmsConfig();
        $smsConf = include(root_path() . "extend/conf/sms/sms.php");
        if (!isset($smsConf[$smsType][$event]['template'])) {
            throw new OperateException(__('短信模板错误'));
        }

        $response = Event::emit('smsMsgSend', [
            'mobile'   => $mobile,
            'event'    => $event,
            'template' => $smsConf[$smsType][$event]['template'],
        ],true);

        if ($response['error'] == 1) {
            throw new \Exception($response['msg']);
        }

        return true;
    }

    /**
     * 校验验证码
     * @param $mobile
     * @param $captcha
     * @param $event
     * @return bool
     * @throws DbException
     */
    public static function checkCaptcha($mobile, $captcha, $event): bool
    {
        $model = new UserValidate();
        $result = $model->where([
            ['event', '=', $event],
            ['mobile', '=', $mobile],
            ['code', '=', $captcha],
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
     * @param string $mobile
     * @param string $event
     * @return array
     */
    public static function getLastMsg(string $mobile, string $event): array
    {
        $mobile = str_replace(['+86', '-', ' ', '.'], '', $mobile);
        return (new UserValidate())->where([
            ['mobile', '=', $mobile],
            ['event', '=', $event],
        ])->order('id', 'desc')->findOrEmpty()->toArray();
    }


    /**
     * 校验手机号
     * @param $mobile
     * @return bool
     */
    public static function filterMobile($mobile): bool
    {
        $pattern = '/^((13[0-9])|(14[5,7,9])|(15[^4])|(18[0-9])|(17[0,1,3,5,6,7,8]))\d{8}$/';
        return (bool)preg_match($pattern, $mobile);
    }

    /**
     * 获取配置信息
     * @return array
     * @throws InvalidArgumentException
     */
    protected static function getSmsConfig(): array
    {
        $smsType = saenv('smstype');
        var_dump($smsType);
        $config = saenv($smsType) ?? [];
        var_dump($config);
        return ['type' => $smsType, 'config' => $config];
    }
}