<?php

namespace app\admin\enums;

use app\common\enums\EnumDesc;

/**
 * 管理员枚举类
 * @package app\admin\enums
 * User：YM
 * Date：2020/2/10
 */
class AdminEnum
{
    #[EnumDesc('管理员SESSION名称')]
    const ADMIN_SESSION = 'AdminLogin';

    #[EnumDesc('管理员登录错误事件')]
    const ADMIN_LOGIN_ERROR = 'adminLoginError';

    #[EnumDesc('管理员登录成功事件')]
    const ADMIN_LOGIN_SUCCESS = 'adminLoginSuccess';

    #[EnumDesc('管理员权限规则')]
    const ADMIN_AUTH_RULES = 'rules';

    #[EnumDesc('管理员栏目规则')]
    const ADMIN_AUTH_CATES = 'cates';
}