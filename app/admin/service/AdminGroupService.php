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

use app\admin\enums\AdminEnum;
use app\common\exception\OperateException;
use app\common\model\system\AdminGroup as AdminGroupModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;

/**
 * 管理员角色服务
 * Class AdminGroupService
 */
class AdminGroupService
{

    /**
     * 获取管理员列表
     * @param array $params
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function dataList(array $params = []): array
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        $where = [];
        if (!empty($param['title'])) {
            $where[] = ['title', 'like', '%' . $param['title'] . '%'];
        }
        if (!empty($param['alias'])) {
            $where[] = ['alias', 'like', '%' . $param['alias'] . '%'];
        }
        if (!empty($param['content'])) {
            $where[] = ['content', 'like', '%' . $param['content'] . '%'];
        }

        $model = new AdminGroupModel();
        // 查询数据
        $count = $model->where($where)->count();
        $page = ($count <= $limit) ? 1 : $page;
        $list = $model->where($where)->order("id asc")->limit($limit)->page($page)->select()->toArray();
        return [$count, $list];
    }

    /**
     * @param array $params
     * @return bool
     * @throws OperateException
     */
    public static function add(array $params = []): bool
    {
        $model = new AdminGroupModel();
        $where[] = ['title', '=', $params['title']];
        $where[] = ['alias', '=', $params['alias']];
        $result = $model->whereOr($where)->findOrEmpty()->toArray();
        if (!empty($result)) {
            throw new OperateException('该角色名称或角色别名已被注册');
        }

        Db::startTrans();
        try {
            $model->create($params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new OperateException($e->getMessage());
        }

        return true;
    }

    /**
     * 编辑管理员
     * @param array $params
     * @return bool
     * @throws OperateException
     */
    public static function edit(array $params): bool
    {
        $model = new AdminGroupModel();
        $where[] = ['title', '=', $params['title']];
        $where[] = ['alias', '=', $params['alias']];
        $result = $model->whereOr($where)->findOrEmpty()->toArray();
        if (!empty($result) && $result['id'] != $params['id']) {
            throw new OperateException('该角色名称或角色别名已被注册');
        }

        Db::startTrans();
        try {
            $model->update($params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new OperateException($e->getMessage());
        }

        return true;
    }


    /**
     * 编辑角色权限
     * @param int $id
     * @param array $rules
     * @return bool
     * @throws OperateException
     */
    public static function editRules(int $id, array $rules = []): bool
    {
        $authService = AuthService::instance();
        if (!$authService->checkRuleOrCateNodes($rules, AdminEnum::ADMIN_AUTH_RULES)) {
            throw new OperateException('没有权限！');
        }

        Db::startTrans();
        try {
            $rules = implode(',', $rules);
            AdminGroupModel::update(['rules' => $rules], ['id' => $id]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new OperateException($e->getMessage());
        }

        return true;
    }

    /**
     * 编辑角色权限
     * @param int $id
     * @param array $cates
     * @return bool
     * @throws OperateException
     */
    public static function editCates(int $id, array $cates = []): bool
    {
        $authService = AuthService::instance();
        if (!$authService->checkRuleOrCateNodes($cates, AdminEnum::ADMIN_AUTH_CATES)) {
            throw new OperateException('没有权限！');
        }

        Db::startTrans();
        try {
            $cates = implode(',', $cates);
            AdminGroupModel::update(['cates' => $cates], ['id' => $id]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new OperateException($e->getMessage());
        }

        return true;
    }
}