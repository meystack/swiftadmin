<?php

declare(strict_types=1);

namespace app\common\model\system;

use think\Model;

/**
 * @mixin \think\Model
 */
class AdminGroup extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * 获取无限极分类
     * @access public static
     * @param string $tips 名称格式
     * @param int $pid 栏目父ID
     * @param array $array 引用数组
     * @param int $blank 替换字符
     * @param int $level 栏目等级
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getListGroup(string $tips = '', int $pid = 0, array &$array = [], int $blank = 0, int $level = 0): array
    {
        // 获取所有分类
        $result = self::where('pid', $pid)->select()->toArray();
        foreach ($result as $key => $value) {
            if (!empty($tips)) {
                $cateName = $tips . $value['title'];
                $value['title'] = str_repeat('', $blank) . $cateName;
            }
            $value['_level'] = $level;
            $array[] = $value;
            unset($result[$key]);
            self::getListGroup($tips, $value['id'], $array, $blank + 1, $level + 1);
        }

        return $array;
    }
}
