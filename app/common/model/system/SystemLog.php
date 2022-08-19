<?php
declare (strict_types = 1);

namespace app\common\model\system;

use think\Model;
use app\common\library\ParseData;

/**
 * @mixin \think\Model
 */
class SystemLog extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 写入日志
    public static function write($logs = null) 
    {
        if (!empty($logs) && is_array($logs)) {
            try {
                self::create($logs);
            } 
            catch (\Throwable $th) {
                if (preg_match('/\'(.*?)\'/',$th->getMessage(),$matches)) {
                    $logs[$matches[1]] = '0'; // 字节太长
                    self::write($logs);
                }
            }
        }
    }

    /**
     * 设置IP转换
     * @access  public
     * @param   $ip
     * @return  mixed
     */
    public function setIPAttr($ip)
    {
        return ParseData::setIPAttr($ip);
    }

    /**
     * 获取IP转换
     * @access  public
     * @param   $ip
     * @return  mixed
     */
    public function getIPAttr($ip)
    {
        return ParseData::getIPAttr($ip);
    }
}
