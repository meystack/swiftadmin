<?php

namespace app\common\model\system;

use think\Model;
use think\model\relation\HasOne;
use Webman\Event\Event;

/**
 * admin_notice
 * 管理员通知
 * @package app\admin\model\system
 */
class AdminNotice extends Model
{
    // 定义时间戳字段名
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    /**
     * 获取管理员
     * @access  public
     * @return  HasOne
     */
    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class,'id','send_id')->bind(['nickname','face']);
    }

    /**
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
        Event::emit('sendAdminNotice', $data);
    }

    /*
     * 获取时间戳
     * @return false|int
     */
    public function getCreateTimeAttr($time): string
    {
        if (!empty($time) && strlen($time) >= 10) {
            $time = published_date($time);
        }

        return $time;
    }

}