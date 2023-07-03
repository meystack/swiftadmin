<?php
declare (strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------
namespace app\admin\service;
use app\common\model\system\AdminRules;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class AdminRuleService
{
    /**
     * 获取资源列表
     * @param array $params
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function dataList(array $params): array
    {
        $where = array();
        if (!empty($params['title'])) {
            $where[] = ['title','like','%'.$params['title'].'%'];
        }
        if (!empty($params['router'])) {
            $where[] = ['router','like','%'.$params['router'].'%'];
        }
        $model = new AdminRules();
        $count = $model->where($where)->count();
        $list = $model->where($where)->order('sort asc')->select()->toArray();

        foreach ($list as $key => $value) {
            $list[$key]['title'] = __($value['title']);
        }

        return [$count, $list];
    }
}