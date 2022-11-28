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
namespace app\common\library;

use app\common\model\system\UserValidate;
use Psr\SimpleCache\InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Event\Event;

/**
 * 短信息类
 *
 */
class Sms
{
    /**
     * 默认配置
     * @var array
     */
    protected array $config = [];

    /**
     * 错误信息
     * @var string
     */
    protected string $_error = '';

    /**
     * 验证码对象
     * @var mixed
     */
    protected string $smsType = 'alisms';

    /**
     * 验证码过期时间
     * @var int
     */
    private int $expireTime = 5; //验证码过期时间（分钟）

    /**
     * @var object 对象实例
     */
    protected static $instance = null;

    /**
     * 类构造函数
     * class constructor.
     * @throws InvalidArgumentException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function __construct()
    {
        // 此配置项为数组。
        if ($this->smsType = saenv('smstype')) {
            $this->config = array_merge($this->config, saenv($this->smsType));
        }
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return self
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        // 返回实例
        return self::$instance;
    }

    /**
     * 发送短信
     * @param string $mobile
     * @param string $event
     * @return bool
     */
    public function send(string $mobile, string $event = 'register'): bool
    {
        if (!Event::hasListener('smsMsgSend')) {
            $this->setError('短信插件未安装');
            return false;
        }

        $config = include(base_path() . "/extend/conf/sms/sms.php");
        if (!isset($config[$this->smsType][$event]['template'])) {
            $this->setError('短信模板错误');
            return false;
        }

        $response = Event::emit('smsMsgSend', [
            'mobile'   => $mobile,
            'event'    => $event,
            'template' => $config[$this->smsType][$event]['template']
        ],true);

        if ($response['error']) {
            $this->setError($response['msg']);
            return false;
        }

        return true;
    }

    /**
     * 获取最后一条
     * @param string $mobile
     * @return UserValidate|array|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLast(string $mobile)
    {
        $mobile = str_replace(['+86', '-', ' ', '.'], '', $mobile);
        $sms = UserValidate::where('mobile', $mobile)->order('id', 'desc')->find();
        return $sms ?: null;
    }

    /**
     * 检查验证码
     *
     * @param string $mobile
     * @param string $code
     * @param string $event
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function check(string $mobile, string $code, string $event = "default"): bool
    {
        $where = [
            ['event', '=', $event],
            ['mobile', '=', $mobile],
            ['status', '=', 1],
        ];

        $result = UserValidate::where($where)->order("id", "desc")->find();
        if (!empty($result) && $result->code === $code) {

            $result->status = 0;
            $result->save();
            $expires = time() - strtotime($result['create_time']);
            if ($expires <= $this->expireTime * 60) {
                return true;
            }
            $this->setError("当前验证码已过期！");
        } else {
            $this->setError("无效验证码");
        }

        return false;
    }

    /**
     * 获取最后产生的错误
     * @return string
     */
    public function getError(): string
    {
        return $this->_error;
    }

    /**
     * 设置错误
     * @param string $error 信息信息
     */
    protected function setError(string $error): void
    {
        $this->_error = $error;
    }
}