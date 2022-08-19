<?php

namespace app\common\validate\system;

use think\Validate;

class LoginLog extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
    ];

    
    /**
     * 提示消息
     */
    protected $message = [
    ];


    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];
    
}
