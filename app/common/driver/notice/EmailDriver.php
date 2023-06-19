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
namespace app\common\driver\notice;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\SimpleCache\InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class EmailDriver
{
    /**
     * @PHPMailer 对象实例
     */
    public object $mail;

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
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        // 此配置项为数组
        $email = saenv('email');
        $this->config = array_merge($this->config, $email);

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
    }

    /**
     * 设置邮件主题
     * @param string $subject 邮件主题
     * @return $this
     */
    public function Subject(string $subject): EmailDriver
    {
        $this->mail->Subject = $subject;
        return $this;
    }

    /**
     * 设置发件人
     * @param string $email 发件人邮箱
     * @param string $name 发件人名称
     * @return $this
     * @throws Exception
     */
    public function from(string $email, string $name = ''): EmailDriver
    {
        $this->mail->setFrom($email, $name);
        return $this;
    }

    /**
     * 设置邮件内容
     * @param $MsgHtml
     * @param boolean $isHtml 是否HTML格式
     * @return $this
     * @throws Exception
     */
    public function MsgHTML($MsgHtml, bool $isHtml = true): EmailDriver
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
     * @return $this
     * @throws Exception
     */
    public function address($email): EmailDriver
    {
        $list = $this->buildAddress($email);
        foreach ($list as $address => $name) {
            $this->mail->addAddress($address, $name);
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
        if (!is_array($emails)) {
            $emails = explode(',', str_replace(";", ",", $emails));
        }

        foreach ($emails as $key => $value) {
            $list[is_numeric($key) ? $value : $key] = is_numeric($key) ? "" : $value;
        }
        return $list ?? [];
    }

    /**
     * 设置抄送
     * @param string $address
     * @param string $name
     * @return EmailDriver
     * @throws @\PHPMailer\PHPMailer\Exception
     */
    public function addCC(string $address, string $name = ''): EmailDriver
    {
        $this->mail->addCC($address, $name);
        return $this;
    }

    /**
     * 设置密送
     * @param string $address
     * @param string $name
     * @return EmailDriver
     * @throws @\PHPMailer\PHPMailer\Exception
     */
    public function addBCC(string $address, string $name = ''): EmailDriver
    {
        $this->mail->addBCC($address, $name);
        return $this;
    }

    /**
     * 添加附件
     * @param string $path 附件路径
     * @param string $name 附件名称
     * @throws Exception
     */
    public function attachment(string $path, string $name = ''): EmailDriver
    {
        if (is_file($path)) {
            $this->mail->addAttachment($path, $name);
        }

        return $this;
    }

    /**
     * 发送邮件
     * @return boolean
     * @throws Exception
     */
    public function send(): bool
    {
        if (!$this->mail->send()) {
            throw new Exception($this->mail->ErrorInfo);
        }
        return true;
    }

    /**
     * 测试发送邮件
     * @param array $config
     * @return bool
     * @throws Exception
     */
    public function testEmail(array $config = []): bool
    {
        $this->config = array_merge($this->config, $config);
        $this->mail->Host = $this->config['smtp_host'];
        $this->mail->Port = $this->config['smtp_port'];
        $this->mail->Username = $this->config['smtp_user'];
        $this->mail->Password = trim($this->config['smtp_pass']);
        $this->mail->SetFrom($this->config['smtp_user'], $this->config['smtp_name']);
        if (!$this->address($config['smtp_test'])->Subject("测试邮件")->MsgHTML("如果您看到这封邮件，说明测试成功了！")->send()) {
            throw new Exception($this->mail->ErrorInfo);
        }

        return true;
    }
}