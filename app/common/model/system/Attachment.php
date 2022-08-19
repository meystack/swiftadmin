<?php
declare (strict_types=1);

namespace app\common\model\system;

use think\Model;

/**
 * @mixin \think\Model
 */
class Attachment extends Model
{

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * 获取文件大小
     * @access      public
     * @param $filesize
     * @return      string
     */
    public function getFilesizeAttr($filesize): string
    {
        if (!empty($filesize)) {
            return format_bytes($filesize);
        }
        return $filesize;
    }

}
