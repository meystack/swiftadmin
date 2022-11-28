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

namespace app\common\library;

use app\common\model\system\UserValidate;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\SimpleCache\InvalidArgumentException;
use system\Random;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\Model;

/**
 * 邮件发送类
 *
 */
class Email
{

    /**
     * @var object 对象实例
     */
    protected static $instance = null;

    /**
     * @PHPMailer 对象实例
     */
    protected mixed $mail;

    /**
     * 验证码对象
     * @var mixed
     */
    private mixed $userVModel;

    /**
     * 验证码过期时间
     * @var int
     */
    private int $expireTime = 5; //验证码过期时间（分钟）

    /**
     * 错误信息
     * @var string
     */
    protected string $_error = '';

    //默认配置
    protected array $config = [
        'smtp_debug' => false,                           // 是否调试
        'smtp_host'  => 'smtp.163.com',                  // 服务器地址
        'smtp_port'  => 587,                             // 服务器端口
        'smtp_user'  => 'yourname@163.com',              // 邮件用户名
        'smtp_pass'  => '****',                          // 邮件密码
        'smtp_name'  => '管理员',                         // 发送邮件显示
    ];

    /**
     * 类构造函数
     * class constructor.
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function __construct()
    {
        // 此配置项为数组
        if ($email = saenv('email')) {
            $this->config = array_merge($this->config, $email);
        }

        // 创建PHPMailer对象实例
        $this->mail = new PHPMailer();
        $this->mail->CharSet = 'UTF-8';
        $this->mail->IsSMTP();

        /**
         * 是否开启调试模式
         */
        $this->mail->SMTPDebug = $this->config['smtp_debug'];
        $this->mail->SMTPAuth = true;
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );

        $this->mail->Host = $this->config['smtp_host'];
        $this->mail->Port = $this->config['smtp_port'];
        $this->mail->Username = $this->config['smtp_user'];
        $this->mail->Password = trim($this->config['smtp_pass']);
        $this->mail->SetFrom($this->config['smtp_user'], $this->config['smtp_name']);
        $this->userVModel = new UserValidate();
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return EMAIL
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
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
     * 设置邮件主题
     * @param string $subject 邮件主题
     * @return $this
     */
    public function Subject(string $subject): Email
    {
        $this->mail->Subject = $subject;
        return $this;
    }

    /**
     * 设置发件人
     * @param string $email 发件人邮箱
     * @param string $name 发件人名称
     * @return $this
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function from(string $email, string $name = ''): Email
    {
        $this->mail->setFrom($email, $name);
        return $this;
    }

    /**
     * 设置邮件内容
     * @param $MsgHtml
     * @param boolean $isHtml 是否HTML格式
     * @return $this
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function MsgHTML($MsgHtml, bool $isHtml = true): Email
    {
        if ($isHtml) {
            $this->mail->msgHTML($MsgHtml);
        } else {
            $this->mail->Body = $MsgHtml;
        }
        return $this;
    }

    /**
     * 设置收件人
     * @param $email
     * @param string $name
     * @return $this
     * @throws Exception
     */
    public function to($email, string $name = ''): Email
    {
        $emailArr = $this->buildAddress($email);
        foreach ($emailArr as $address => $name) {
            $this->mail->addAddress($address, $name);
        }

        return $this;
    }

    /**
     * 添加附件
     * @param string $path 附件路径
     * @param string $name 附件名称
     * @return Email
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function attachment(string $path, string $name = ''): Email
    {
        if (is_file($path)) {
            $this->mail->addAttachment($path, $name);
        }

        return $this;
    }

    /**
     * 构建Email地址
     * @param $emails
     * @return array
     */
    protected function buildAddress($emails): array
    {
        $emails = is_array($emails) ? $emails : explode(',', str_replace(";", ",", $emails));
        $result = [];
        foreach ($emails as $key => $value) {
            $email = is_numeric($key) ? $value : $key;
            $result[$email] = is_numeric($key) ? "" : $value;
        }

        return $result;
    }

    /**
     * 获取最后一条
     * @param string $email
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getLast(string $email)
    {
        $sms = UserValidate::where('email', $email)->order('id', 'desc')->find();
        return $sms ?: null;
    }

    /**
     * 发送验证码
     * @param string $email 收件人邮箱
     * @param string $event
     * @return Email
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function captcha(string $email = '', string $event = "default"): Email
    {
        $code = Random::number();
        $array = [
            'code'   => $code,
            'event'  => $event,
            'email'  => $email,
            'status' => 1,
        ];

        $this->userVModel->create($array);
        $content = read_file(base_path() . '/extend/conf/tpl/captcha.tpl');
        $content = str_replace(['{code}', '{site_name}', '{time}'], [$code, saenv('site_name'), date('Y-m-d H:i:s')], $content);
        $this->to($email)->Subject("验证码")->MsgHTML($content);

        return $this;
    }

    /**
     * 检查验证码
     * @param string $email
     * @param string $code
     * @param string $event
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function check(string $email, string $code = '', string $event = "default"): bool
    {
        $result = $this->userVModel->where([
                                               ['event', '=', $event],
                                               ['email', '=', $email],
                                               ['status', '=', 1],
                                           ])->order("id", "desc")->find();

        if (!empty($result) && $result->code == $code) {

            // 设置已使用
            $result->status = 0;
            $result->save();

            // 是否过期
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

    /**
     * 发送邮件
     * @return boolean
     */
    public function send(): bool
    {
        $result = false;

        try {
            $result = $this->mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $this->setError($e->getMessage());
        }

        $this->setError($result ? '' : $this->mail->ErrorInfo);
        return $result;
    }

    /**
     * 测试发送邮件
     */
    public function testEmail($config)
    {
        if (empty($config) || !is_array($config)) {
            return '缺少必要的信息';
        }

        $this->config = array_merge($this->config, $config);
        $this->mail->Host = $this->config['smtp_host'];
        $this->mail->Port = $this->config['smtp_port'];
        $this->mail->Username = $this->config['smtp_user'];
        $this->mail->Password = trim($this->config['smtp_pass']);
        $this->mail->SetFrom($this->config['smtp_user'], $this->config['smtp_name']);
        return $this->to($config['smtp_test'])->Subject("测试邮件")->MsgHTML("如果您看到这封邮件，说明测试成功了！")->send();
    }

}
