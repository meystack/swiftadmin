<?php

namespace app\queue\redis;
use Webman\RedisQueue\Consumer;

/**
 * 消费任务
 * @package app\queue\redis
 * @author meystack
 * @date 2022-11-20
 */
class Worker implements Consumer
{
    /**
     * 消费队列名称
     * @var string
     */
    public string $queue = 'send-mail';

    /**
     * REDIS连接名称
     * @param $data
     * @return bool
     */
    public string $connection = 'default';

    /**
     * 默认消费函数
     * @param $data
     * @return bool
     */
    public function consume($data): bool
    {
        /**
         * 无需反序列化
         * 请在此编写您的消费逻辑
         */
        var_dump($data);
        return true;
    }
}