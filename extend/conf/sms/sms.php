<?php
return array(
    /*
     * 短信模板配置
    */
    'alisms' => array(
        'register' => array(
            'name'     => '用户注册',
            'auto'     => true,
            'template' => 'SMS_206595526'
        ),
        'forgot'   => array(
            'name'     => '找回密码',
            'auto'     => true,
            'template' => 'SMS_206854581'
        ),
        'change'   => array(
            'name'     => '修改信息',
            'auto'     => true,
            'template' => 'SMS_206595526'
        ),
        'notice'   => array(
            'name'     => '消息通知',
            'auto'     => false,
            'template' => 'SMS_206854581'
        )
    ),
    'tensms' => array(
        'register' => array(
            'name'     => '用户注册',
            'auto'     => true,
            'template' => '1361749'
        ),
        'forgot'   => array(
            'name'     => '找回密码',
            'auto'     => true,
            'template' => '1361742'
        ),
        'change'   => array(
            'name'     => '修改信息',
            'auto'     => true,
            'template' => '1401023'
        ),
        'notice'   => array(
            'name'     => '消息通知',
            'auto'     => false,
            'template' => '901315'
        )
    ),

    // TODO...
);