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

use app\admin\enums\AdminNoticeEnum;
use app\common\exception\OperateException;
use app\common\model\system\AdminNotice;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Event\Event;

class AdminNoticeService
{

    /**
     * 获取消息列表
     * @param int $adminId
     * @return array
     * @throws DbException
     */
    public static function dataList(int $adminId): array
    {
        $type = input('type', AdminNoticeEnum::TODO);
        $page = input('page', 1);
        $limit = input('limit', 10);
        $title = input('title', '');
        if ($type == 'send') {
            $where[] = ['type', '=', AdminNoticeEnum::MESSAGE];
            $where[] = ['send_id', '=', $adminId];
        } else {
            $where[] = ['type', '=', $type];
            $where[] = ['admin_id', '=', $adminId];
        }

        $status = input('status', 'all');
        if ($status !== 'all') {
            $where[] = ['status', '=', $status];
        }

        if (!empty($title)) {
            $where[] = ['title', 'like', '%' . $title . '%'];
        }

        $count = AdminNotice::where($where)->count();
        $list = AdminNotice::with(['admin'])->where($where)
            ->order('id', 'desc')
            ->limit((int)$limit)
            ->page((int)$page)
            ->select()->toArray();
        return [$count, $list];
    }

    /**
     * 获取管理员通知列表
     * @param int $adminId
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function bells(int $adminId): array
    {
        $collection = AdminNoticeEnum::COLLECTION;
        foreach ($collection as $item) {
            $where = [
                ['type', '=', $item],
                ['admin_id', '=', $adminId],
            ];
            $count[$item] = AdminNotice::where($where)->where('status', 0)->count();
            $list[$item] = AdminNotice::with(['admin'])->withoutField('content')->where($where)->limit(3)->order('id desc')->select()->toArray();
        }

        return [$count ?? [], $list ?? []];
    }

    /**
     * 获取管理员通知列表
     * @param int $adminId
     * @return array
     * @throws DbException
     */
    public static function getBells(int $adminId): array
    {
        $type = input('type', AdminNoticeEnum::NOTICE);
        $page = input('page', 1);
        $limit = input('limit', 3);
        $where[] = ['type', '=', $type];
        $where[] = ['admin_id', '=', $adminId];
        return AdminNotice::with(['admin'])->where($where)
            ->order('id', 'desc')
            ->paginate(['list_rows' => $limit, 'page' => $page])
            ->toArray();
    }

    /**
     * 添加消息
     * @param array $data
     * @param string $type
     * @return bool
     * @throws OperateException
     */
    public static function add(array $data = [], string $type = ''): bool
    {
        if (!$data) {
            return false;
        }

        try {
            $model = new AdminNotice();
            $type == 'array' ? $model->saveAll($data) : $model->create($data);
        } catch (\Exception $e) {
            throw new OperateException($e->getMessage());
        }

        // 钩子消息推送
        Event::emit('sendAdminNotice', $data);
        return true;
    }

    /**
     * 获取管理员通知详情
     * @param $id
     * @param $adminId
     * @return array
     * @throws OperateException
     */
    public static function getDetail($id, $adminId): array
    {
        $detail = AdminNotice::with(['admin'])->where(['id' => $id])->findOrEmpty()->toArray();
        if (empty($detail)) {
            throw new OperateException('数据不存在');
        }
        if (!in_array($adminId,[$detail['admin_id'],$detail['send_id']])){
            throw new OperateException('非法访问');
        }

        if ($detail['type'] !== AdminNoticeEnum::TODO && $detail['admin_id'] == $adminId) {
            AdminNotice::update(['status' => 1], ['id' => $id]);
        }

        return $detail;
    }

    /**
     * 删除消息
     * @param int $id
     * @param int $adminId
     * @return bool
     * @throws OperateException
     */
    public static function delete(int $id = 0, int $adminId = 0): bool
    {
        $detail = AdminNotice::where('id', $id)->findOrEmpty()->toArray();
        if (empty($detail)) {
            throw new OperateException('数据不存在');
        }

        $receive = $detail['send_id'] == $adminId && $detail['status'] == 1;
        if ($detail['admin_id'] != $adminId || $receive) {
            throw new OperateException('无权删除');
        }

        AdminNotice::destroy($id);
        return true;
    }
}