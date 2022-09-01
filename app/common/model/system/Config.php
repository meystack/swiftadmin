<?php

declare(strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------
namespace app\common\model\system;

use think\Model;

/**
 * @mixin \think\Model
 */
class Config extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    /**
     * 获取系统配置
     *
     * @param string $name
     * @param bool $group
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function all(string $name = '', bool $group = false): array
    {
        $where = [];
        $config = [];

        if (!empty($name) && $group) {
            $where[] = ['group', '=', $name];
        } else {
            if (!empty($name)) {
                $where[] = ['name', '=', $name];
            }
        }

        $list = self::where($where)->select()->toArray();
        foreach ($list as $option) {
            if (!is_empty($option['type']) && 'array' == trim($option['type'])) {
                $config[$option['name']] = json_decode($option['value'], true);
            } else {
                $config[$option['name']] = $option['value'];
            }
        }

        return $config;
    }
}
