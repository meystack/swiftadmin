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
use app\common\model\system\Admin;
use app\common\model\system\AdminAccess;
use app\common\model\system\AdminGroup as AdminGroupModel;
use app\common\model\system\AdminRules as AdminRulesModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Event\Event;

/**
 * 后台权限验证
 * @package app\admin\service
 * Class AuthService
 */
class AuthService
{
    /**
     * 数据库实例
     * @var object
     */
    protected object $model;

    /**
     * 分组标记
     * @var string
     */
    public string $authGroup = 'authGroup';

    /**
     * 用户私有标记
     * @var string
     */
    public string $authPrivate = 'authPrivate';

    /**
     * 默认权限字段
     *
     * @var string
     */
    public string $authFields = 'id,cid,pid,title,auth';

    /**
     * 错误信息
     * @var string
     */
    protected string $_error = '';

    /**
     * @var ?object 对象实例
     */
    protected static ?object $instance = null;

    /**
     * 类构造函数
     * class constructor.
     */
    public function __construct()
    {
        $this->model = new Admin();
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return object|null
     */
    public static function instance(array $options = []): ?object
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        // 返回实例
        return self::$instance;
    }

    /**
     * 检查权限
     * @param mixed $name 需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param int $adminId 认证用户的id
     * @param int $type 认证类型
     * @param string $mode 执行check的模式
     * @param string $relation 如果为 'or' 表示满足任一条规则即通过验证;如果为 and则表示需满足所有规则才能通过验证
     * @return bool                        通过验证返回true;失败返回false
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function permissions(mixed $name, int $adminId = 0, int $type = 1, string $mode = 'url', string $relation = 'or'): bool
    {
        // 转换格式
        if (is_string($name)) {
            $name = strtolower($name);
            if (str_contains($name, ',')) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }

        $authList = [];
        if ('url' == $mode) { // 解析URL参数
            $REQUEST = unserialize(strtolower(serialize(request()->all())));
        }

        foreach ($this->getAuthList($adminId) as $auth) {

            // 非鉴权接口
            $router = strtolower($auth['router']);
            if (in_array($router, $name) && $auth['auth'] == 0) {
                $authList[] = $router;
                continue;
            }

            // 校验正则模式
            if (!empty($auth['condition'])) {
                $rule = $condition = '';
                $user = $this->getUserInfo();
                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule);
                @(eval('$condition=(' . $command . ');'));
                if ($condition) {
                    $authList[] = $router;
                }
            }

            // URL参数模式
            $query = preg_replace('/^.+\?/U', '', $router);
            if ('url' == $mode && $query != $router) {
                parse_str($query, $param);
                $intersect = array_intersect_assoc($REQUEST, $param);
                $router = preg_replace('/\?.*$/U', '', $router);
                if (in_array($router, $name) && $intersect == $param) {
                    $authList[] = $router;
                }
            } else {
                if (in_array($router, $name)) {
                    $authList[] = $router;
                }
            }
        }

        $authList = array_unique($authList);
        if ('or' == $relation && !empty($authList)) {
            return true;
        }

        $authDiff = array_diff($name, $authList);
        if ('and' == $relation && empty($authDiff)) {
            return true;
        }

        return false;
    }

    /**
     * 查询权限列表
     * @param mixed $adminId 用户id
     * @param array $nodes 已获取节点
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAuthList(mixed $adminId = 0, array $nodes = []): array
    {
        // 查找节点
        $where[] = ['status', '=', 1];
        if (!$this->superAdmin()) {
            $authNodes = !empty($nodes) ? $nodes : $this->getRulesNode($adminId);
            return AdminRulesModel::where(function ($query) use ($where, $authNodes) {
                if (empty($authNodes[$this->authPrivate])) {
                    $where[] = ['auth', '=', '0'];
                    $query->where($where);
                } else {
                    $where[] = ['id', 'in', $authNodes[$this->authPrivate]];
                    $query->where($where)->whereOr('auth', '0');
                }
            })->order('sort asc')->select()->toArray();
        }

        return AdminRulesModel::where($where)->order('sort asc')->select()->toArray();
    }

    /**
     * 获取权限菜单
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getPermissionsMenu(): string
    {
        $authNodes = $this->getRulesNode();
        $nodeLists = $this->getAuthList(get_admin_id(), $authNodes);
        foreach ($nodeLists as $key => $value) {
            $nodeLists[$key]['title'] = __($value['title']);
            if ($value['router'] != '#') {
                $nodeLists[$key]['router'] = (string)url($value['router']);
            }
        }

        $this->superAdmin() && $authNodes['supersAdmin'] = true;
        $authNodes['authorities'] = list_to_tree($nodeLists);
        return json_encode($authNodes, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 管理组分级鉴权
     * @param array $operationIds
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function checkRulesForGroup(array $operationIds = []): bool
    {
        if ($this->superAdmin()) {
            return true;
        }

        $group_id = $this->getUserInfo()['group_id'];
        $adminGroupIds = explode(',', $group_id);
        $adminGroupList = AdminGroupModel::where('id', 'in', $adminGroupIds)->select()->toArray();
        // 查询操作组
        $operationList = AdminGroupModel::where('id', 'in', $operationIds)->select()->toArray();
        foreach ($operationList as $item) {
            foreach ($adminGroupList as $child) {
                if ($item['pid'] < $child['id']
                    || $item['pid'] == $child['pid']) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 查询权限节点
     * @access public
     * @param $type
     * @param $class
     * @param bool $tree
     * @return array|false|string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getRuleCatesTree($type, $class, bool $tree = true)
    {
        if (is_array($type) && $type) {
            $type = $type['type'] ?? AdminEnum::ADMIN_AUTH_RULES;
            $class = $type['class'] ?? $this->authGroup;
        }
        $class = $class != $this->authGroup ? $this->authPrivate : $class;
        $authNodes = $this->getRulesNode(get_admin_id(), $type);
        $where[] = ['status', '=', 1];
        if ($type && $type == AdminEnum::ADMIN_AUTH_RULES) {
            if (!$this->superAdmin()) {
                $menuList = AdminRulesModel::where(function ($query) use ($where, $authNodes, $class) {
                    if (empty($authNodes[$class])) {
                        $where[] = ['auth', '=', '0'];
                        $query->where($where);
                    } else {
                        $where[] = ['id', 'in', $authNodes[$class]];
                        $query->where($where)->whereOr('auth', '0');
                    }
                })->order('sort asc')->select()->toArray();
            } else {
                $menuList = AdminRulesModel::where($where)->order('sort asc')->select()->toArray();
            }

        } else {
            /**
             * 栏目二次开发接口
             * @param $menuList
             */
            if (!$this->superAdmin() && !empty($authNodes[$class])) {
                $menuList = Event::emit('cmsCategoryPermissions', [
                    'field' => $this->authFields,
                    'nodes' => $authNodes[$class]
                ], true);
            } else {
                $menuList = Event::emit('cmsCategoryPermissions', [
                    'field' => $this->authFields
                ], true);
            }
        }

        return $tree ? ($menuList ? json_encode(list_to_tree($menuList)) : json_encode([])) : $menuList;
    }


    /**
     * 校验节点避免越权
     * @access public
     * @param $rules
     * @param string $type
     * @param string $class
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function checkRuleOrCateNodes($rules, string $type, string $class = 'pri'): bool
    {
        if ($this->superAdmin()) {
            return true;
        }

        $type = !empty($type) ? $type : AdminEnum::ADMIN_AUTH_RULES;
        $class = !empty($class) ? $class : $this->authGroup;
        $class = $class != $this->authGroup ? $this->authPrivate : $class;
        $authNodes = $this->getRulesNode(get_admin_id(), $type);
        $differ = array_unique(array_merge($rules, $authNodes[$class]));
        if (count($differ) > count($authNodes[$class])) {
            return false;
        }

        return true;
    }

    /**
     * 获取权限节点
     * @param mixed $adminId 管理员id
     * @param string $type 节点类型
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getRulesNode(mixed $adminId = 0, string $type = AdminEnum::ADMIN_AUTH_RULES): array
    {
        $authGroup = $authPrivate = [];
        $adminId = $adminId > 0 ? $adminId : get_admin_id();
        $authNodes = AdminAccess::where('admin_id', $adminId)->findOrEmpty()->toArray();

        // 私有节点
        if (!empty($authNodes[$type])) {
            $authPrivate = explode(',', $authNodes[$type]);
        }

        // 用户组节点
        if (!empty($authNodes['group_id'])) {
            $groupNodes = (new AdminGroupModel)->whereIn('id', $authNodes['group_id'])->select()->toArray();
            foreach ($groupNodes as $value) {
                $nodes = !empty($value[$type]) ? explode(',', $value[$type]) : [];
                $authGroup = array_merge($authGroup, $nodes);
                $authPrivate = array_merge($authPrivate, $nodes);
            }
            $authGroup = array_unique($authGroup);
            $authPrivate = array_unique($authPrivate);
        }

        return [
            $this->authGroup   => $authGroup,
            $this->authPrivate => $authPrivate,
        ];
    }

    /**
     * 超级管理员
     * @param int $adminId
     * @param int $type
     * @return bool
     */
    public function superAdmin(int $adminId = 0, int $type = 1): bool
    {
        $adminId = $adminId > 1 ? $adminId : get_admin_id();
        $adminInfo = $this->getUserInfo($adminId);
        $adminGroup = explode(',', $adminInfo['group_id']);
        if ($adminInfo['id'] == $type || array_search($type, $adminGroup)) {
            return true;
        }

        return false;
    }

    /**
     * 获取用户信息
     * @param int $adminId
     * @return array
     */
    public function getUserInfo(int $adminId = 0): array
    {
        $_pk = is_string($this->model->getPk()) ? $this->model->getPk() : 'id';
        return $this->model->where($_pk, $adminId)->findOrEmpty()->toArray();
    }

    /**
     * 获取最后产生的错误
     * @return string
     */
    public function getError(): string
    {
        return $this->_error;
    }

    /**
     * 设置错误
     * @param string $error
     */
    protected function setError(string $error): void
    {
        $this->_error = $error;
    }
}