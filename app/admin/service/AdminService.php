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
use app\common\model\system\Admin;
use app\common\model\system\AdminAccess;
use app\common\model\system\AdminAccess as AdminAccessModel;
use app\common\model\system\AdminGroup as AdminGroupModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;

/**
 * 管理员服务
 * Class AdminService
 */
class AdminService
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
        $status = !empty($params['status']) ? $params['status'] - 1 : 1;
        $where[] = ['status', '=', $status];
        if (!empty($params['name'])) {
            $where[] = ['name', 'like', '%' . $params['name'] . '%'];
        }

        if (!empty($params['dep'])) {
            $where[] = ['branch_id', 'find in set', $params['dep']];
        }

        if (!empty($params['group_id'])) {
            $where[] = ['group_id', 'find in set', $params['group_id']];
        }

        $model = new Admin();
        $count = $model->where($where)->count();
        $page = ($count <= $limit) ? 1 : $page;
        $adminList = $model->where($where)->order("id asc")->withoutField('pwd')->limit($limit)->page($page)->select()->toArray();

        $authService = AuthService::instance();
        foreach ($adminList as $key => $value) {
            $groupId = trim($value['group_id']);
            $itemGroup = (new AdminGroupModel)->where('id', 'in', $groupId)->select()->toArray();
            $adminList[$key]['group'] = $itemGroup;
            // 排序
            if (!empty($adminList[$key]['group'])) {
                $adminList[$key]['group'] = list_sort_by($adminList[$key]['group'], 'id');
            }

            $authNodes = $authService->getRulesNode($value['id']);
            $adminList[$key][AdminEnum::ADMIN_AUTH_RULES] = $authNodes[$authService->authPrivate];

            $authNodes = $authService->getRulesNode($value['id'], AdminEnum::ADMIN_AUTH_RULES);
            $adminList[$key][AdminEnum::ADMIN_AUTH_CATES] = $authNodes[$authService->authPrivate];
        }

        return [
            'count' => $count,
            'list'  => $adminList
        ];
    }

    /**
     * @param array $params
     * @return bool
     * @throws OperateException
     */
    public static function add(array $params = []): bool
    {
        $model = new Admin();
        $where[] = ['name', '=', $params['name']];
        $where[] = ['email', '=', $params['email']];
        $result = $model->whereOr($where)->findOrEmpty()->toArray();
        if (!empty($result)) {
            throw new OperateException('该用户名或邮箱已被注册！');
        }

        // 管理员加密
        $params['pwd'] = encryptPwd($params['pwd']);
        $params['create_ip'] = request()->getRealIp();

        Db::startTrans();
        try {

            $data = $model->create($params);
            $access['admin_id'] = $data['id'];
            $access['group_id'] = $data['group_id'];
            AdminAccessModel::insert($access);
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
        if (!empty($params['pwd'])) {
            $params['pwd'] = encryptPwd($params['pwd']);
        }

        foreach ($params as $key => $value) {
            if (empty($value)) {
                unset($params[$key]);
            }
        }

        Db::startTrans();
        try {
            $model = new Admin();
            $model->update($params);
            $access['group_id'] = $params['group_id'];
            AdminAccessModel::update($access, ['admin_id' => $params['id']]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new OperateException($e->getMessage());
        }

        return true;
    }

    /**
     * 更新权限节点
     * @param $adminId
     * @param string $ruleType
     * @return bool
     * @throws OperateException
     */
    public static function updateRulesNodes($adminId, string $ruleType): bool
    {
        if (!$adminId) {
            throw new OperateException('参数错误！');
        }

        $authService = AuthService::instance();
        $params = request()->post($ruleType, []);
        $access = $authService->getRulesNode($adminId, $ruleType);
        $rules = array_diff($params, $access[$authService->authGroup]);
        if (!$authService->checkRuleOrCateNodes($rules, $ruleType, $authService->authPrivate)) {
            throw new OperateException('没有权限!');
        }

        $differ = array_diff($access[$authService->authPrivate], $access[$authService->authGroup]);
        $curNodes = [];
        if (!$authService->superAdmin()) {
            $curNodes = $authService->getRulesNode();
            $curNodes = array_diff($differ, $curNodes[$authService->authPrivate]);
        }

        Db::startTrans();
        try {
            $value = array_unique(array_merge($rules, $curNodes));
            $data[$ruleType] = implode(',', $value);
            AdminAccessModel::update($data, ['admin_id' => $adminId]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new OperateException($e->getMessage());
        }

        return true;
    }
}