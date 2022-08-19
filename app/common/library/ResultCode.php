<?php
declare (strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------
namespace app\common\library;


/**
 * RESULT代码文件
 */
class ResultCode
{
    const SUCCESS = [
        'code'   => 200,
        'status' => 'SUCCESS',
        'msg'    => '请求成功',
    ];

    const REGISTERSUCCESS = [
        'code'   => 200,
        'status' => 'REGISTERSUCCESS',
        'msg'    => '注册成功',
    ];

    const LOGINSUCCESS = [
        'code'   => 200,
        'status' => 'LOGINSUCCESS',
        'msg'    => '登录成功',
    ];

    const LOGINOUTSUCCESS = [
        'code'   => 200,
        'status' => 'LOGINOUTSUCCESS',
        'msg'    => '退出成功',
    ];

    const AUTH_ERROR = [
        'code'   => -100,
        'status' => 'AUTH_ERROR',
        'msg'    => '没有权限',
    ];

    const INVALID = [
        'code'   => -101,
        'status' => 'INVALID',
        'msg'    => '操作失败',
    ];

    const PARAMERROR = [
        'code'   => -102,
        'status' => 'PARAMERROR',
        'msg'    => '请求参数错误',
    ];

    const TOKEN_INVALID = [
        'code'   => -103,
        'status' => 'TOKEN_INVALID',
        'msg'    => 'token校验失败',
    ];

    const API_DISABLE = [
        'code'   => -104,
        'status' => 'API_DISABLE',
        'msg'    => '当前接口已禁用',
    ];

    const METHOD_INVALID = [
        'code'   => -105,
        'status' => 'METHOD_INVALID',
        'msg'    => '访问方式错误',
    ];

    const DAY_INVALID = [
        'code'   => -106,
        'status' => 'DAY_INVALID',
        'msg'    => '接口已达每日上限',
    ];

    const API_SPEED_INVALID = [
        'code'   => -107,
        'status' => 'API_SPEED_INVALID',
        'msg'    => '调用API接口速度过快',
    ];

    const CEILING_INVALID = [
        'code'   => -108,
        'status' => 'CEILING_INVALID',
        'msg'    => '调用总额已消费完',
    ];

    const USPWDERROR = [
        'code'   => -109,
        'status' => 'USPWDERROR',
        'msg'    => '用户名或密码错误',
    ];

    const STATUSEXCEPTION = [
        'code'   => -110,
        'status' => 'STATUSEXCEPTION',
        'msg'    => '当前用户已被禁用',
    ];

    const ACCESS_TOKEN_TIMEOUT = [
        'code'   => -300,
        'status' => 'ACCESS_TOKEN_TIMEOUT',
        'msg'    => '身份令牌过期',
    ];

    const ACCESS_TOKEN_INVALID = [
        'code'   => -301,
        'status' => 'ACCESS_TOKEN_INVALID',
        'msg'    => '获取token失败',
    ];

    const SESSION_TIMEOUT = [
        'code'   => -302,
        'status' => 'SESSION_TIMEOUT',
        'msg'    => 'SESSION过期',
    ];

    const UNKNOWN = [
        'code'   => -990,
        'status' => 'UNKNOWN',
        'msg'    => '未知错误',
    ];

    const EXCEPTION = [
        'code'   => -991,
        'status' => 'EXCEPTION',
        'msg'    => '系统异常',
    ];

    const VERSION_ERROR = [
        'code'   => -992,
        'status' => 'VERSION_ERROR',
        'msg'    => '版本错误',
    ];

    const SYSTEM_DISABLE = [
        'code'   => -993,
        'status' => 'VERSION_ERROR',
        'msg'    => '禁止修改系统属性',
    ];

    const LACKPARAME = [
        'code'   => -994,
        'status' => 'LACKPARAME',
        'msg'    => '缺少请求参数',
    ];

}
