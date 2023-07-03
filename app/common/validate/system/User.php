<?php

declare(strict_types=1);

namespace app\common\validate\system;

use Psr\SimpleCache\InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Validate;

class User extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'nickname' => 'require|min:5|max:32|checkName',
        'pwd|密码' => 'require|min:6|max:64',
        'email'    => 'require',
        'mobile'   => 'require|mobile',
        'captcha'  => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'nickname.require'     => '用户名不能为空',
        'nickname.min'         => '用户名不能少于5个字符',
        'nickname.max'         => '用户名不能超过32个字符',
        'nickname.checkName'   => '用户名包含禁止注册字符',
        'pwd.require'          => '密码不能为空',
        'pwd.min'              => '密码不能少于6个字符',
        'pwd.max'              => '密码不能超过64个字符',
        'email.require'        => '邮箱不能为空',
        'mobile.require'       => '手机号不能为空',
        'mobile.mobile'        => '手机号格式不正确',
        'captcha.require'      => '验证码不能为空',
    ];

    // 测试验证场景
    protected $scene = [
        'nickname' => ['nickname'],
        'mobile'   => ['mobile', 'captcha'],
        'login'    => ['nickname', 'pwd'],
    ];

    /**
     * 自定义验证规则
     * @param $value
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function checkName($value): bool
    {
        $notAllow = saenv('user_reg_notallow');
        $notAllow = explode(',', $notAllow);
        if (in_array($value, $notAllow)) {
            return false;
        }

        return true;
    }

    public function sceneAdd(): User
    {
        return $this->only(['nickname', 'pwd', 'email', 'mobile']);
    }

    public function sceneEdit(): User
    {
        return $this->only(['nickname', 'email']);
    }

    public function sceneRegister(): User
    {
        return $this->only(['nickname', 'pwd']);
    }

    public function scenePwd(): User
    {
        return $this->only(['pwd'])->append('pwd', 'confirm');
    }

    public function sceneMobile(): User
    {
        return $this->only(['mobile', 'captcha']);
    }
}