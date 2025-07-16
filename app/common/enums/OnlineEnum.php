<?php

namespace app\common\enums;

/**
 * 运行环境枚举
 * Class OnlineEnum
 * @package app\common\enums
 */
enum OnlineEnum
{
    #[EnumDesc('iOS')]
    const IOS = 1;

    #[EnumDesc('Android')]
    const ANDROID = 2;

    #[EnumDesc('Windows')]
    const WINDOWS = 3;

    #[EnumDesc('MacOSX')]
    const MAC_OSX = 4;

    #[EnumDesc('Web')]
    const WEB = 5;

    #[EnumDesc('MiniProgram')]
    const MINI_PROGRAM = 6;

    #[EnumDesc('Linux')]
    const LINUX = 7;

    #[EnumDesc('AndroidPad')]
    const ANDROID_PAD = 8;

    #[EnumDesc('iPad')]
    const IPAD = 9;

    #[EnumDesc('iPadPro')]
    const IPAD_PRO = 10;
}
