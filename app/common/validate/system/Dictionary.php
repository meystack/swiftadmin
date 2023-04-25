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
}