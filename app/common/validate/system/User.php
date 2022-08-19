<?php

declare(strict_types=1);

namespace app\common\validate\system;

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
        'test_filed' => 'max:255',
        'nickname'   => 'require|min:2|max:12|filters|chsAlphaNum',
        'pwd|密码'     => 'require|min:6|max:64',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'nickname.require'     => '用户名不能为空',
        'nickname.min'         => '用户名不能少于2个字符',
        'nickname.max'         => '用户名不能超过12个字符',
        'nickname.filters'     => '用户名包含禁止注册字符',
        'nickname.chsAlphaNum' => '用户名只能是汉字、字母和数字',
        'test_filed.max'       => '测试场景用',
    ];

    // 测试验证场景
    protected $scene = [
        'test' => ['test_filed'],
    ];

    /**
     * 自定义验证规则
     * @param $value
     * @return bool
     */
    protected function filters($value): bool
    {
        $notAllow = saenv('user_reg_notallow');
        $notAllow = explode(',', $notAllow);
        foreach ($notAllow as $values) {
            if ($value == $values) {
                return false;
            }
        }
        return true;
    }
}
