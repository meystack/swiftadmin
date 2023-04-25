<?php


namespace app\common\model\system;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class Department extends Model
{
    use SoftDelete;
    
    // 定义时间戳字段名
    protected $createTime = 'create_time';
	protected $updateTime = 'update_time';

    /**
     * 树形分类
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
	public static function getListTree(): array
    {
        $array = self::select()->toArray();
		if (!empty($array)) {
			return list_to_tree($array);
		}

        return [];
	}

    /**
     * 字段修改器
     */
    public function setSortAttr($value) 
    {
        if (is_empty($value)) {
            return self::max('id') + 1;
        }
        return $value;
	}
	
}
