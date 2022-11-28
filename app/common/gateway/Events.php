<?php

namespace app\common\gateway;

use GatewayWorker\Lib\Gateway;
use support\Log;
use Webman\Event\Event;

/**
 * IM网关通讯接口
 * Class Events
 * @package app\common\gateway
 * @Author  Meystack <
 */
class Events
{
    /**
     * onWorkerStart 事件回调
     * @param $worker
     * @return void
     */
    public static function onWorkerStart($worker): void
    {
        Event::emit('onWorkerStart', $worker);
    }

    /**
     * 当客户端连接上gateway进程时(TCP三次握手完毕时)触发
     * @param $client_id
     * @return void
     */
    public static function onConnect($client_id): void
    {
        $data = [
            'type'      => 'init',
            'client_id' => $client_id,
        ];
        Gateway::sendToCurrentClient(json_encode($data));
    }

    /**
     * 当客户端连接上gateway完成websocket握手时触发
     * @param $client_id
     * @param $data
     * @return void
     */
    public static function onWebSocketConnect($client_id, $data): void
    {
        Event::emit('onWebSocketConnect', $client_id, $data);
    }

    /**
     * 当客户端发来数据(Gateway进程收到数据)后触发
     * @param $client_id
     * @param $message
     * @return void
     */
    public static function onMessage($client_id, $message): void
    {}

    /**
     * 推送消息
     * @param $client_id
     * @param array $message
     * @return void
     */
    public static function onSendMsg($client_id, array $message = []): void
    {
        Gateway::sendToUid($client_id, json_encode($message, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 绑定用户UID
     * @param $client_id
     * @param $uid
     * @param string $type
     * @return bool
     */
    public static function onBindUid($client_id, $uid, string $type = 'admin'): bool
    {
        if (empty($client_id) || empty($uid)) {
            return false;
        }

        $uid = $type . '_' . $uid;
        try {
            Gateway::bindUid($client_id, $uid);
        } catch (\Exception $e) {
            Log::info('绑定用户UID失败：' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 通过UID获取socketID
     * @param $uid
     * @param string $type
     * @return array
     */
    public static function onGetClientByUid($uid, string $type = 'admin'): array
    {
        if (empty($uid)) {
            return [];
        }

        $uid = $type . '_' . $uid;
        return Gateway::getClientIdByUid($uid);
    }

    /**
     * 当用户断开连接时触发
     * @param $client_id
     * @param $uid
     * @param string $type
     * @return bool
     */
    public static function onUnbindUid($client_id, $uid, string $type = 'admin'): bool
    {
        if (empty($client_id) || empty($uid)) {
            return false;
        }

        $uid = $type . '_' . $uid;
        try {
            Gateway::unbindUid($client_id, $uid);
        } catch (\Exception $e) {
            Log::info('解绑用户UID失败：' . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 用户加入群组
     * @param $client_id
     * @param $group_id
     * @return void
     */
    public static function onJoinGroup($client_id, $group_id): void
    {
        Gateway::joinGroup($client_id, $group_id);
    }

    /**
     * 当客户端断开连接时触发
     * @param $client_id
     * @return void
     */
    public static function onClose($client_id): void
    {
        Event::emit('onWebSocketClose', $client_id);
    }

}
