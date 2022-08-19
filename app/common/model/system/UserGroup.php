<?php
declare (strict_types = 1);

namespace app\common\model\system;

use think\db\exception\DataNotFoundException;
use think\Model;
use think\facade\Db;
use think\model\concern\SoftDelete;

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
     *
     * @return int|\think\model\relation\HasMany
     */
    public function userTotal()
    {
        return $this->hasMany(User::class,'group_id','id');
    }

    /**
     * 获取用户组
     *
     * @param integer $id
     * @param boolean $mark
     * @return void|array
     * @throws DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function ToObtain(int $id = 0, bool $mark = true)
    {
        $groupList = system_cache('groupList');
        if (empty($groupList)) {
            $groupList = self::select()->toArray();
            system_cache('groupList',$groupList,86400);
        }

        return $groupList;
    }
}

