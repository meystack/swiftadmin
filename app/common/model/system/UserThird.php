<?php
declare (strict_types = 1);

namespace app\common\model\system;

use think\Model;

/**
 * @mixin \think\Model
 */
class UserThird extends Model
{

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updatetime = 'update_time';
}
