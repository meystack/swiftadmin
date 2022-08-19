<?php
declare (strict_types = 1);

namespace app\common\validate\system;

use think\Validate;

class Admin extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule =   [
        'name'  => 'require|min:2|max:12|chsAlphaNum',
        'pwd|密码'   => 'require|min:6|max:64',
    ];


    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'name.require'		=> '用户名不能为空',
        'name.min'     	    => '用户名不能少于2个字符',
        'name.max'     	    => '用户名不能超过12个字符',
        'name.filters'      => '用户名包含禁止注册字符',
        'name.chsAlphaNum'  => '用户名只能是汉字、字母和数字',
    ];

    // 测试验证场景
    protected $scene = [
        'edit'  =>  ['name']
    ];
}
