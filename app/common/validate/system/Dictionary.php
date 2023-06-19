<?php


namespace app\common\validate\system;

use app\common\model\system\Dictionary as SystemDictionary;
use think\Validate;

class Dictionary extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
    protected $rule =   [
        'name'       => 'require',
        'value'      => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'name.require'       => '字典名称不能为空',
        'value.require'      => '字典值不能为空',
    ];
}