<?php

// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------
namespace app\admin\controller\system;

use app\admin\enums\AdminEnum;
use app\admin\service\AdminGroupService;
use app\AdminController;
use app\common\exception\OperateException;
use app\common\model\system\AdminGroup as AdminGroupModel;
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Http\Request;

/**
 * 管理员组管理
 * Class AdminGroup
 * @package app\admin\controller\system
 */
class AdminGroup extends AdminController
{
    // 初始化函数
    public function __construct()
    {
        parent::__construct();
        $this->model = new AdminGroupModel();
    }

    /**
     * 获取资源列表
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index(): Response
    {
        if (request()->isAjax()) {
            $params = \request()->all();
            list($count, $list) = AdminGroupService::dataList($params);
            return $this->success('查询成功', '/', $list, $count);
        }

        return view('/system/admin/group', [
            'group' => $this->model->getListGroup()
        ]);
    }

    /**
     * 添加角色
     */
    public function add()
    {
        if (request()->isPost()) {
            $post = request()->post();
            validate(\app\common\validate\system\AdminGroup::class)->scene('add')->check($post);
            AdminGroupService::add($post);
            return $this->success('添加角色成功！');
        }

        return $this->error('添加角色失败！');
    }

    /**
     * 编辑角色
     */
    public function edit()
    {
        if (request()->isPost()) {
            $post = request()->post();
            validate(\app\common\validate\system\AdminGroup::class)->scene('edit')->check($post);
            AdminGroupService::edit($post);
            return $this->success('更新角色成功！');
        }

        return $this->error('更新角色失败！');
    }

    /**
     * 权限函数接口
     * @access      public
     */
    public function getRuleCateTree()
    {
        $type = input('type', AdminEnum::ADMIN_AUTH_RULES);
        return $this->authService->getRuleCatesTree($type, $this->authService->authGroup);
    }

    /**
     * 更新权限
     * @return Response
     * @throws OperateException
     */
    public function editRules(): Response
    {
        $id = input('id', 0);
        $post = request()->post();
        $rules = input(AdminEnum::ADMIN_AUTH_RULES, []);
        validate(\app\common\validate\system\AdminGroup::class)->scene('edit')->check($post);
        AdminGroupService::editRules((int)$id, $rules);
        return $this->success('更新权限成功！');
    }

    /**
     * 更新栏目
     * @return Response
     * @throws OperateException
     */
    public function editCates(): Response
    {
        $id = input('id', 0);
        $cates = input(AdminEnum::ADMIN_AUTH_CATES, []);
        $post = request()->post();
        validate(\app\common\validate\system\AdminGroup::class)->scene('edit')->check($post);
        AdminGroupService::editCates($id, $cates);
        return $this->success('更新权限成功！');
    }

    /**
     * 删除角色/用户组
     */
    public function del(): Response
    {
        $id = input('id', 0);
        validate(\app\common\validate\system\AdminGroup::class)->scene('edit')->check(request()->all());
        if ($id == 1) {
            return $this->error('系统内置禁止删除！');
        } else if ($this->model::destroy($id)) {
            return $this->success('删除角色成功！');
        }

        return $this->error('删除角色失败，请检查您的参数！');
    }

}
