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

namespace app\admin\controller\system;

use app\admin\enums\AdminEnum;
use app\admin\service\AdminService;
use app\AdminController;
use app\common\exception\OperateException;
use app\common\model\system\AdminNotice;
use app\common\model\system\Jobs;
use app\common\model\system\Department;
use app\common\model\system\Admin as AdminModel;
use app\common\model\system\AdminGroup as AdminGroupModel;
use app\common\model\system\AdminAccess as AdminAccessModel;
use support\Log;
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use support\Cache;
use Webman\Http\Request;

/**
 * 管理员管理
 * Class Admin
 * @package app\admin\controller\system
 */
class Admin extends AdminController
{
    /**
     * 用户管理组
     * @var mixed
     */
    protected mixed $group;

    /**
     * 用户岗位
     * @var mixed
     */
    public mixed $jobs;

    /**
     * 用户部门
     * @var mixed
     */
    public mixed $department;

    // 初始化函数
    public function __construct()
    {
        parent::__construct();
        $this->model = new AdminModel();
    }

    /**
     * 获取资源列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index(): Response
    {
        $this->jobs = Jobs::select()->toArray();
        $this->group = AdminGroupModel::select()->toArray();
        $this->department = Department::getListTree();

        if (request()->isAjax()) {
            $params = request()->all();
            list('count' => $count, 'list' => $list) = AdminService::dataList($params);
            return $this->success('查询成功', null, $list, $count);
        }

        return view('/system/admin/index', [
            'jobs'       => $this->jobs,
            'group'      => $this->group,
            'department' => json_encode($this->department),
        ]);
    }

    /**
     * 添加管理员
     * @return Response
     * @throws OperateException
     */
    public function add(): Response
    {
        if (request()->isPost()) {
            $post = request()->post();
            validate(\app\common\validate\system\Admin::class)->scene('add')->check($post);
            AdminService::add($post);
            return $this->success('添加管理员成功');
        }

        // 获取用户组
        return view('', ['group' => $this->group]);
    }

    /**
     * 更新管理员
     * @return Response
     * @throws OperateException
     */
    public function edit(): Response
    {
        if (request()->isPost()) {
            $post = request()->all();
            validate(\app\common\validate\system\Admin::class)->scene('edit')->check($post);
            AdminService::edit($post);
            return $this->success('更新管理员成功');
        }

        return $this->error('更新管理员失败');
    }

    /**
     * 获取用户权限树
     * @access      public
     * getAdminRules
     */
    public function getPermissions()
    {
        return $this->authService->getPermissionsMenu();
    }

    /**
     * 获取节点数据
     * @access   public
     */
    public function getRuleCateTree()
    {
        $type = input('type', AdminEnum::ADMIN_AUTH_RULES);
        return $this->authService->getRuleCatesTree($type, $this->authService->authPrivate);
    }

    /**
     * 编辑权限
     * @access      public
     * @return      Response
     * @throws OperateException
     */
    public function editRules(): Response
    {
        $adminId = input('admin_id', 0);
        AdminService::updateRulesNodes($adminId, AdminEnum::ADMIN_AUTH_RULES);
        return $this->success('更新权限成功！');
    }

    /**
     * 编辑栏目权限
     * @access      public
     * @return      Response
     * @throws OperateException
     */
    public function editCates(): Response
    {
        $adminId = input('admin_id', 0);
        AdminService::updateRulesNodes($adminId, AdminEnum::ADMIN_AUTH_CATES);
        return $this->success('更新权限成功！');
    }

    /**
     * 模版页面
     * @return Response
     */
    public function theme(): Response
    {
        return view('/system/admin/theme');
    }

    /**
     * 个人中心
     * @param Request $request
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function center(Request $request): Response
    {
        if (request()->isPost()) {
            $post = request()->post();
            $post['id'] = get_admin_id();
            if ($this->model->update($post)) {
                return $this->success();
            }

            return $this->error();
        }

        $title = [];
        $data = $this->model->find(get_admin_id());
        if (!empty($data['group_id'])) {
            $group = AdminGroupModel::field('title')
                ->whereIn('id', $data['group_id'])
                ->select()
                ->toArray();
            foreach ($group as $key => $value) {
                $title[$key] = $value['title'];
            }
        }

        $data['jobs'] = Jobs::where('id', $data['jobs_id'])->value('title');
        $data['group'] = implode('－', $title);
        $data['tags'] = empty($data['tags']) ? $data['tags'] : unserialize($data['tags']);
        return view('/system/admin/center', [
            'data' => $data
        ]);
    }

    /**
     * 修改个人资料
     */
    public function modify(Request $request)
    {
        if (request()->isAjax()) {
            $post = request()->post();
            $id = get_admin_id();
            try {
                //code...
                switch ($post['field']) {
                    case 'face':
                        $id = $this->model->update(['id' => $id, 'face' => $post['face']]);
                        break;
                    case 'mood':
                        $id = $this->model->update(['id' => $id, 'mood' => $post['mood']]);
                        break;
                    case 'tags':
                        if (\is_empty($post['tags'])) {
                            break;
                        }
                        $data = $this->model->field('tags')->find($id);
                        if (!empty($data['tags'])) {
                            $tags = unserialize($data['tags']);
                            if (!empty($post['del'])) {
                                foreach ($tags as $key => $value) {
                                    if ($value == $post['tags']) {
                                        unset($tags[$key]);
                                    }
                                }
                            } else {
                                $merge = array($post['tags']);
                                $tags = array_unique(array_merge($merge, $tags));
                                if (count($tags) > 10) {
                                    throw new \Exception('最多拥有10个标签！');
                                }
                            }
                            $tags = serialize($tags);
                        } else {
                            $tags = serialize(array($post['tags']));
                        }
                        $id = $this->model->update(['id' => $id, 'tags' => $tags]);
                        break;
                    default:
                        # code...
                        break;
                }
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }

            return $id ? $this->success() : $this->error();
        }
    }

    /**
     * 修改密码
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function pwd(): Response
    {
        if (request()->isPost()) {

            $pwd = input('pwd');
            $post = request()->except(['pwd']);
            if ($post['pass'] !== $post['repass']) {
                return $this->error('两次输入的密码不一样！');
            }

            // 查找数据
            $where[] = ['id', '=', get_admin_id()];
            $where[] = ['pwd', '=', encryptPwd($pwd)];
            $result = $this->model->where($where)->find();

            if (!empty($result)) {
                $this->model->where($where)->update(['pwd' => encryptPwd($post['pass'])]);
                return $this->success('更改密码成功！');
            } else {
                return $this->error('原始密码输入错误');
            }
        }

        return view('/system/admin/pwd');
    }

    /**
     * 语言配置
     * @return Response
     */
    public function language(): Response
    {
        $language = input('l');
        $env = base_path() . '/.env';
        $array = parse_ini_file($env, true);
        $array['LANG_DEFAULT_LANG'] = $language;
        $content = parse_array_ini($array);
        request()->session()->set('lang', $language);
        if (write_file($env, $content)) {
            return json(['success']);
        }
        return json(['error']);
    }

    /**
     * 更改状态
     * @return Response
     */
    public function status(): Response
    {
        $id = input('id');
        if ($id == 1) {
            return $this->error('超级管理员不能更改状态！');
        }
        $array['id'] = $id;
        $array['status'] = input('status');
        if ($this->model->update($array)) {
            return $this->success('修改成功！');
        }

        return $this->error('修改失败,请检查您的数据！');
    }

    /**
     * 删除管理员
     * @return Response
     * @throws DbException
     */
    public function del(): Response
    {
        $id = input('id');
        !is_array($id) && ($id = array($id));
        if (!empty($id)) {

            // 过滤权限
            if (in_array("1", $id)) {
                return $this->error('禁止删除超管帐号！');
            }

            // 删除用户
            if ($this->model->destroy($id)) {
                $arr = implode(',', $id);
                $where[] = ['admin_id', 'in', $arr];
                AdminAccessModel::where($where)->delete();
                return $this->success('删除管理员成功！');
            }
        }

        return $this->error('删除管理员失败，请检查您的参数！');
    }

    /**
     * 清理系统缓存
     * @return Response
     */
    public function clear(): Response
    {
        if (request()->isAjax()) {

            $type = input('type');
            try {

                // 清理内容
                if ($type == 'all' || $type == 'content') {
                    $session = session(AdminEnum::ADMIN_SESSION);
                    Cache::clear();
                    request()->session()->set(AdminEnum::ADMIN_SESSION, $session);
                }

                // 清理模板
                if ($type == 'all' || $type == 'template') {
                    recursive_delete(root_path('runtime/views'));
                }

                // 清理插件缓存
                if ($type == 'all' || $type == 'plugin') {
                    plugin_refresh_hooks();
                }

            } catch (\Throwable $th) {
                return $this->error($th->getMessage());
            }
        }

        return $this->success('清理缓存成功，请刷新页面！');
    }
}
