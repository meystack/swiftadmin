<?php
declare (strict_types = 1);

namespace app\common\model\system;
use think\Model;
use app\common\library\ParseData;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class User extends Model
{
    use SoftDelete;
    
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 定义第三方关联
    public function third(): \think\model\relation\HasMany
    {
        return $this->hasMany(UserThird::class,'user_id');
    }

    /**
     * 关联用户组
     *
     * @return \think\model\relation\HasOne
     */
    public function group(): \think\model\relation\HasOne
    {
        return $this->hasOne(UserGroup::class,'id','group_id');
    }

    /**
     * 注册会员前
     * @param object $model
     * @return void
     */
    public static function onBeforeInsert(object $model)
    {}

    /**
     * 更新会员前
     * @param object $model
     * @return void
     */
    public static function onBeforeUpdate(object $model)
    {}

    /**
     * 注册会员后
     * @param object $model
     * @return void
     */
    public static function onAfterInsert(object $model)
    {}

    /**
     * 更新会员数据
     * @param object $model
     * @return void
     */
    public static function onAfterUpdate(object $model)
    {}
    
    /**
     * 获取头像
     * @param string $value
     * @param array $data
     * @return string
     */
    public function getAvatarAttr(string $value, array $data): string
    {
        
        if ($value && strpos($value,'://')) {
            return $value;
        }
        
        if (empty($value)) {
            $value = '/static/images/user_default.jpg';
        }

        $prefix = cdn_Prefix();
        if (!empty($prefix) && $value) {
            if (!str_contains($value,'data:image')) { 
                return $prefix.$value;
            }
        }

        return $value;
    }

    /**
     * 设置头像
     * @param string $value
     * @param array $data
     * @return string
     */
    public function setAvatarAttr(string $value, array $data): string
    {
        return ParseData::setImageAttr($value, $data);
    }

    /**
     * 登录时间
     */
    public function getLoginTimeAttr($value)
    {
        if (!empty($value)) {
            $value = date('Y-m-d H:i:s',$value);
        }

        return $value;
    }

    /**
     * 设置创建IP
     * @param $ip
     * @return mixed
     */
    public function setCreateIpAttr($ip)
    {
        return ParseData::setIPAttr($ip);
    }

    /**
     * 获取创建IP
     * @param $ip
     * @return mixed
     */
    public function getCreateIpAttr($ip)
    {
        return ParseData::getIPAttr($ip);
    }

    /**
     * 设置登录IP
     * @param $ip
     * @return mixed
     */
    public function setLoginIpAttr($ip)
    {
        return ParseData::setIPAttr($ip);
    }

    /**
     * 获取登录IP
     */
    public function getLoginIpAttr($ip)
    {
        return ParseData::getIPAttr($ip);
    }

    /**
     * 设置IP转换
     * @access  public
     * @param  $ip
     * @return mixed
     */
    public function setIPAttr($ip)
    {
        return ParseData::setIPAttr($ip);
    }

    /**
     * 获取IP转换
     * @access  public
     * @param  $ip
     * @return mixed
     */
    public function getIPAttr($ip)
    {
        return ParseData::getIPAttr($ip);
    }

    /**
     * 减少会员积分
     *
     * @param integer $id
     * @param integer $score
     * @return void
     */
    public static function reduceScore(int $id = 0, int $score = 0)
    {
        try {
            if ($score) {
                self::where('id', $id)->dec('score', $score)->update();
            }
        } catch (\Throwable $th) {
        }
    }

}

