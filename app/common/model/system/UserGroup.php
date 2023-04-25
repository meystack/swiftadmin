<?php


namespace app\common\model\system;

use think\Model;
use think\model\concern\SoftDelete;
use think\model\relation\HasMany;

/**
 * @mixin \think\Model
 */
class UserGroup extends Model
{
    use SoftDelete;
    
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * 关联用户组
     * @return HasMany
     */
    public function userTotal(): HasMany
    {
        return $this->hasMany(User::class,'group_id','id');
    }
}