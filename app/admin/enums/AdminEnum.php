<?php

namespace app\admin\enums;

/**
 * 管理员枚举类
 * @package app\admin\enums
 * User：YM
 * Date：2020/2/10
 */
class AdminEnum
{
    /**
     * 管理员状态
     * @var array
     */
    const ADMIN_SESSION = 'AdminLogin';

    /**
     * 管理员登录错误事件
     * @var string
     */
    const ADMIN_LOGIN_ERROR = 'adminLoginError';

    /**
     * 管理员登录成功事件
     * @var string
     */
    const ADMIN_LOGIN_SUCCESS = 'adminLoginSuccess';

    /**
     * 管理员权限规则
     * @var string
     */
    const ADMIN_AUTH_RULES = 'rules';

    /**
     * 管理员栏目规则
     * @var string
     */
    const ADMIN_AUTH_CATES = 'cates';
}