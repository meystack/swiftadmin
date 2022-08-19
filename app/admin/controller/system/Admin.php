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

use app\AdminController;
use app\common\model\system\Jobs;
use app\common\model\system\Department;
use app\common\model\system\Admin as AdminModel;
use app\common\model\system\AdminGroup as AdminGroupModel;
use app\common\model\system\AdminAccess as AdminAccessModel;

/**
 * 管理员管理
 * Class Admin
 * @package app\admin\controller\system
 */
class Admin extends AdminController
{

    /**
     * 用户管理组
     * @var null
     */
    protected $group = null;

    /**
     * 用户岗位
     * @var null
     */
    public $jobs = null;

    /**
     * 用户部门
     * @var null
     */
    public $department = null;

    // 初始化函数
    public function __construct()
    {
        parent::__construct();
        $this->model = new AdminModel();
        $this->jobs = Jobs::select()->toArray();
        $this->group = AdminGroupModel::select()->toArray();
        $this->department = Department::getListTree();

        foreach ($this->group as $k => $v) {
            $this->group[$k]['title'] = __($v['title']);
        }
    }

    /**
     * 获取资源列表
     */
    public function index()
    {
        // 判断isAjax
        if (request()->isAjax()) {

            // 获取数据
            $post = \request()->all();
            $page = (int)request()->input('page') ?? 1;
            $limit = (int)request()->input('limit') ?? 10;
            $status = !empty($post['status']) ? $post['status'] - 1 : 1;

            // 生成查询条件
            $where = array();
            if (!empty($post['name'])) {
                $where[] = ['name', 'like', '%' . $post['name'] . '%'];
            }

            if (!empty($post['dep'])) {
                $where[] = ['department_id', 'find in set', $post['dep']];
            }

            if (!empty($post['group_id'])) {
                $where[] = ['group_id', 'find in set', $post['group_id']];
            }

            // 生成查询数据
            $where[] = ['status', '=', $status];
            $count = $this->model->where($where)->count();
            $page = ($count <= $limit) ? 1 : $page;
            $list = $this->model->where($where)->order("id asc")->withoutField('pwd')->limit($limit)->page($page)->select()->toArray();

            // 循环处理数据
            foreach ($list as $key => $value) {
                $groupIDs = explode(',', $value['group_id']);
                foreach ($groupIDs as $field => $id) {
                    // 查找组
                    $result = list_search($this->group, ['id' => $id]);
                    if (!empty($result)) {
                        $list[$key]['group'][$field] = $result;
                    }
                }

                if (!empty($list[$key]['group'])) {
                    $list[$key]['group'] = list_sort_by($list[$key]['group'], 'id');
                }

                $authNodes = $this->auth->getRulesNode($value['id']);
                $list[$key][AUTH_RULES] = $authNodes[$this->auth->authPrivate];

                $authNodes = $this->auth->getRulesNode($value['id'], AUTH_CATE);
                $list[$key][AUTH_CATE] = $authNodes[$this->auth->authPrivate];
            }

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
     * @return \support\Response
     */
    public function add(): \support\Response
    {
        if (request()->isPost()) {

            // 验证数据
            $post = request()->post();
            $post = request_validate_rules($post, get_class($this->model));
            if (!is_array($post)) {
                return $this->error($post);
            }

            $where[] = ['name', '=', $post['name']];
            $where[] = ['email', '=', $post['email']];
            if ($this->model->whereOr($where)->find()) {
                return $this->error('该用户名或邮箱已被注册！');
            }


            // 管理员加密
            $post['pwd'] = encryptPwd($post['pwd']);
            $post['create_ip'] = request()->getRemoteIp();
            $data = $this->model->create($post);
            if (!is_empty($data->id)) {
                $access['admin_id'] = $data->id;
                $access['group_id'] = $data->group_id;
                AdminAccessModel::insert($access);
                return $this->success('添加管理员成功！');
            } else {
                return $this->error('添加管理员失败！');
            }
        }

        // 获取用户组
        return view('', ['group' => $this->group]);
    }

    /**
     * 更新管理员
     */
    public function edit()
    {
        if (request()->isPost()) {

            $id = request()->input('id');

            if (!empty($id) && is_numeric($id)) {

                // 验证数据
                $post = request()->all();
                $post = request_validate_rules($post, get_class($this->model), 'edit');
                if (!is_array($post)) {
                    return $this->error($post);
                }

                if (!empty($post['pwd'])) {
                    $post['pwd'] = encryptPwd($post['pwd']);
                } else {
                    unset($post['pwd']);
                }

                if ($this->model->update($post)) {
                    $access['group_id'] = $post['group_id'];
                    AdminAccessModel::where('admin_id', $id)->update($access);
                    return $this->success('更新管理员成功！');
                } else {
                    return $this->error('更新管理员失败');
                }
            }
        }
    }

    /**
     * 编辑权限
     */
    public function editRules()
    {
        if (request()->isPost()) {
            return $this->_update_RuleCates();
        }
    }

    /**
     * 编辑栏目权限
     */
    public function editCates()
    {
        return $this->_update_RuleCates(AUTH_CATE);
    }

    /**
     * 更新权限函数
     * @access      protected
     * @param   string  $type
     * @return      \support\Response|void
     */
    protected function _update_RuleCates(string $type = AUTH_RULES)
    {
        if (request()->isPost()) {

            $admin_id = input('admin_id');
            $rules = request()->post($type) ?? [];

            if (!empty($admin_id) && $admin_id > 0) {

                $access = $this->auth->getRulesNode($admin_id, $type);
                $rules = array_diff($rules, $access[$this->auth->authGroup]);

                // 权限验证
                if (!$this->auth->checkRuleOrCateNodes($rules, $type, $this->auth->authPrivate)) {
                    return $this->error('没有权限!');
                }

                // 获取个人节点
                $differ = array_diff($access[$this->auth->authPrivate], $access[$this->auth->authGroup]);
                $current = [];
                if (!$this->auth->superAdmin()) {
                    $current = $this->auth->getRulesNode();
                    $current = array_diff($differ, $current[$this->auth->authPrivate]);
                }

                $rules = array_unique(array_merge($rules, $current));
                $this->model = new AdminAccessModel();
                $data = [
                    "$type" => implode(',', $rules)
                ];

                if ($this->model->where('admin_id', $admin_id)->save($data)) {
                    return $this->success('更新权限成功！');
                }

                return $this->error('更新权限失败！');
            }
        }
    }

    /**
     * 获取用户菜单
     * getAdminRules
     * @return void
     */
    public function getUserMenu()
    {
        if (\request()->isAjax()) {
            return $this->auth->getRulesMenu();
        }
    }

    /**
     * 权限函数接口
     * @access      public
     * @return      mixed|array
     */
    public function getRuleCateTree()
    {
        if (request()->isAjax()) {
            $type = input('type') ?? 'rules';
            try {
                $list = $this->auth->getRuleCatesTree($type, $this->auth->authPrivate);
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }
            return $list;
        }

        return [];
    }

    /**
     * 模版页面
     */
    public function theme()
    {
        return view('/system/admin/theme');
    }

    /**
     * 消息模板
     */
    public function message()
    {
        // 配置消息
        $msg = [
            'msg'     => [
                '0' => [
                    'title'       => '你收到了几份周报！',
                    'type'        => '周报类型',
                    'create_time' => '1周前',
                ],
                '1' => [
                    'title'       => '你收到了来自女下属的周报',
                    'type'        => '周报类型',
                    'create_time' => '2周前',
                ]
            ],
            'comment' => [
                '0' => [
                    'title'       => '一个领导评论了你',
                    'content'     => '小伙子不错，继续努力！',
                    'create_time' => '1周前',
                ]
            ],
            'things'  => [
                '0' => [
                    'title'       => '客户说尽快修复瞟了么APP闪退的问题...',
                    'type'        => '0',
                    'create_time' => '1周前',
                ],
                '1' => [
                    'title'       => '秦老板和经销商的下季度合同尽快签订！',
                    'type'        => '1',
                    'create_time' => '2周前',
                ]
            ],
        ];


        return view('/system/admin/message', [
            'list' => $msg
        ]);
    }

    /**
     * 个人中心
     * @return mixed
     */
    public function center(): \support\Response
    {

        if (request()->isPost()) {
            $post = request()->post();
            $post['id'] = $this->admin['id'];
            if ($this->model->update($post)) {
                return $this->success();
            }

            return $this->error();
        }

        $title = [];
        $data = $this->model->find($this->admin['id']);
        if (!empty($data['group_id'])) {
            $group = AdminGroupModel::field('title')
                                    ->whereIn('id', $data['group_id'])
                                    ->select()
                                    ->toArray();
            foreach ($group as $key => $value) {
                $title[$key] = $value['title'];
            }
        }

        $data['group'] = implode('－', $title);
        $data['tags'] = empty($data['tags']) ? $data['tags'] : unserialize($data['tags']);
        return view('/system/admin/center', [
            'data' => $data
        ]);
    }

    /**
     * 修改个人资料
     */
    public function modify()
    {
        if (request()->isAjax()) {
            $post = request()->post();
            $id = $this->admin['id'];
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
     */
    public function pwd(): \support\Response
    {
        if (request()->isPost()) {

            $pwd = input('pwd');
            $post = request()->except(['pwd']);
            if ($post['pass'] !== $post['repass']) {
                return $this->error('两次输入的密码不一样！');
            }

            // 查找数据
            $where[] = ['id', '=', $this->admin['id']];
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
     * @return mixed
     * @throws \think\Exception
     */
    public function language()
    {
        $language = input('l');
        $env = base_path() . '/.env';
        $array = parse_ini_file($env, true);
        $array['LANG_DEFAULT_LANG'] = $language;
        $content = parse_array_ini($array);
        if (write_file($env, $content)) {
            return json(['success']);
        }
    }

    /**
     * 更改状态
     * @return \support\Response
     */
    public function status()
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
     * @return mixed
     * @throws \think\db\exception\DbException
     */
    public function del()
    {
        $id = input('id');
        !is_array($id) && ($id = array($id));
        if (!empty($id) && is_array($id)) {

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
     * @return \support\Response
     * @throws \think\Exception
     */
    public function clear(): \support\Response
    {
        if (request()->isAjax()) {

            $type = input('type');

            try {

                // 清理内容
                if ($type == 'all' || $type == 'content') {
                    \think\facade\Cache::clear();
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
