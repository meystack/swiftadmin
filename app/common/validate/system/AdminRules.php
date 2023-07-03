<?php
declare (strict_types = 1);

namespace app\common\validate\system;

use think\Validate;

class AdminRules extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule =   [
        'pid'    => 'notEqId',
    ];


    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'pid.notEqId'        => '选择上级分类错误！',
    ];

    /**
     * 自定义验证规则
     * @param $value
     * @param $rule
     * @param $data
     * @return bool
     */
    protected function notEqId($value, $rule, $data): bool
    {
        if ($value == $data['id']) {
            return false;
        }

        return true;
    }
}