<?php

namespace app\common\model\system;

use think\Model;


/**
 * user_log
 * 登录日志
 * @package app\admin\model\system
 */
class UserLog extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    /**
     * 用户登录日志
     * @param string $error
     * @param string $nickname
     * @param int $login_id
     * @param int $status
     */
    public static function write(string $error, string $nickname,int $login_id = 0, int $status = 0)
    {
        $userAgent = \request()->header('user-agent');
        if (preg_match('/.*?\((.*?)\).*?/', $userAgent, $matches)) {
            $user_os = substr($matches[1], 0, strpos($matches[1], ';'));
        } else {
            $user_os = '未知';
        }

        $user_browser = preg_replace('/[^(]+\((.*?)[^)]+\) .*?/','$1',$userAgent);

        $data = [
            'login_id'      => $login_id,
            'login_ip'      => request()->getRealIp(),
            'login_agent'   => $userAgent,
            'login_os'      => $user_os,
            'login_browser' => $user_browser,
            'nickname'      => $nickname ?? '未知',
            'error'         => $error,
            'status'        => $status,
        ];

        self::create($data);
    }

}