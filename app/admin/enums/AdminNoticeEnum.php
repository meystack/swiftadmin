<?php

namespace app\admin\enums;

/**
 * 管理员通知枚举类
 * @package app\admin\enums
 */
class AdminNoticeEnum
{
    // 系统通知
    const NOTICE = 'notice';

    // 站内消息
    const MESSAGE = 'message';

    // 待办事项
    const TODO = 'todo';

    // 未读
    const STATUS_UNREAD = 0;

    // 已读
    const STATUS_READ = 1;

    // 通知类型集合
    const COLLECTION = [self::NOTICE, self::MESSAGE, self::TODO,];

    // 枚举集合
    const ENUM = [
        self::NOTICE  => '系统通知',
        self::MESSAGE => '站内消息',
        self::TODO    => '待办事项',
    ];
}