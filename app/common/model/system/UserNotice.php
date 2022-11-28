<?php

namespace app\common\model\system;

use think\Model;
use Webman\Event\Event;

/**
 * user_notice
 * 用户消息
 * @package app\admin\model\system
 */
class UserNotice extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    /**
     * 消息发送
     * @param array $data
     * @param string $msgType
     * @return false|void
     * @throws \Exception
     */
    public static function sendNotice(array $data = [], string $msgType = 'array')
    {
        if (!$data) {
            return false;
        }

        $model = new self();
        if ($msgType == 'array') {
            $model->saveAll($data);
        } else {
            $model->insert($data);
        }

        // 钩子消息推送
        Event::emit('sendUserNotice', $data);
    }

}