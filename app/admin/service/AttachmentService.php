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

use app\common\model\system\Attachment;

class AttachmentService
{
    /**
     * 获取资源列表
     * @param array $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function dataList(array $params = []): array
    {

        $page = (int)$params['page'] ?: 1;
        $limit = (int)$params['limit'] ?: 10;
        $type = $params['type'] ?? '';
        $where = [];
        if (!empty($type)) {
            $where[] = ['type', '=', $type];
        }
        if (!empty($params['filename'])) {
            $where[] = ['filename', 'like', '%' . $params['filename'] . '%'];
        }

        $model = new Attachment();
        $count = $model->where($where)->count();
        $page = ($count <= $limit) ? 1 : $page;
        $list = $model->where($where)->order("id desc")->limit((int)$limit)->page((int)$page)->select()->toArray();

        $prefix = cdn_Prefix();
        foreach ($list as $index => $item) {
            if (!empty($prefix)) {
                $list[$index]['url'] = $prefix . $item['url'];
            }
        }

        return [$count, $list];
    }
}