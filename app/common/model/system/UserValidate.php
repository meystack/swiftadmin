<?php
declare (strict_types = 1);

namespace app\common\model\system;

use think\Model;
use app\common\library\ParseData;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class UserValidate extends Model
{
    use SoftDelete;
    
    // 定义时间戳字段名
    protected $createTime = 'create_time';

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
