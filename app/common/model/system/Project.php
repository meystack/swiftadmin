<?php
declare (strict_types = 1);

namespace app\common\model\system;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class Project extends Model
{
    use SoftDelete;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}

