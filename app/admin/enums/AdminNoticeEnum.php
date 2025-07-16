<?php

namespace app\admin\enums;

use app\common\enums\EnumDesc;

/**
 * 管理员通知枚举类
 * @package app\admin\enums
 */
class AdminNoticeEnum
{
    #[EnumDesc('系统通知')]
    const NOTICE = 'notice';

    #[EnumDesc('站内消息')]
    const MESSAGE = 'message';

    #[EnumDesc('待办事项')]
    const TODO = 'todo';

    #[EnumDesc('未读数量')]
    const STATUS_UNREAD = 0;

    #[EnumDesc('已读数量')]
    const STATUS_READ = 1;

    #[EnumDesc('通知类型集合')]
    const COLLECTION = [self::NOTICE, self::MESSAGE, self::TODO,];

    #[EnumDesc('枚举集合')]
    const ENUM = [
        self::NOTICE  => '系统通知',
        self::MESSAGE => '站内消息',
        self::TODO    => '待办事项',
    ];
}