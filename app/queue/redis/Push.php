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
namespace app\queue\redis;

use support\Log;
use Webman\RedisQueue\Redis;
use Webman\RedisQueue\Client;

/**
 * 队列任务
 * @package app\queue\redis
 * @author meystack
 * @date 2022-11-20
 */
class Push
{
    /**
     * api推送
     * @var mixed
     */
    protected mixed $api;

    /**
     * 同步推送
     * @param $name
     * @param $data
     * @param int $delay
     * @return bool
     */
    public static function queue($name, $data, int $delay = 0): bool
    {
        try {
            // 投递消息
            Redis::send($name, $data, $delay);
        } catch (\Throwable $th) {
            Log::info('redis push error:' . $th->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 异步推送
     * @param $name
     * @param $data
     * @param int $delay
     * @return bool
     */
    public static function client($name, $data, int $delay = 0): bool
    {
        try {
            // 投递消息
            Client::send($name, $data, $delay);
        } catch (\Throwable $th) {
            Log::info('redis Client async error:' . $th->getMessage());
            return false;
        }
        return true;
    }
}