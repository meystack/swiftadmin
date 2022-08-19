<?php
declare (strict_types = 1);

namespace app\common\model\system;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class Jobs extends Model
{
    use SoftDelete;
    
    // 定义时间戳字段名
    protected $createTime = 'create_time';

	/**
	 * 树形分类
	 */
	public static function getListTree() 
    {
        $array = self::select()->toArray();
		if (is_array($array) && !empty($array)) {
			return list_to_tree($array);
		}
	}

    // 字段修改器
    public function setSortAttr($value) 
    {
        if (is_empty($value)) {
            return self::max('id') + 1;
        }
        return $value;
    }
}
