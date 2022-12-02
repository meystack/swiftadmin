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
namespace app\admin\library;

use app\common\model\system\AdminAccess;
use app\common\model\system\Admin as AdminModel;
use app\common\model\system\AdminRules as AdminRulesModel;
use app\common\model\system\AdminGroup as AdminGroupModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use Webman\Event\Event;

/**
 * 后台模块验证类
 */
class Auth
{
    /**
     * 数据库实例
     * @var mixed
     */
    protected mixed $model;

    /**
     * 管理员数据
     * @var mixed
     */
    private mixed $admin;

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
     * @var mixed
     */
    private mixed $groupIDs;

    // 对象实例
    protected static $instance = null;

    /**
     * 类构造函数
     * class constructor.
     */
    public function __construct($config = [])
    {
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return object
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        // 返回实例
        return self::$instance;
    }

    /**
     * 检查权限
     * @param string|array $name 需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param int $admin_id 认证用户的id
     * @param int $type 认证类型
     * @param string $mode 执行check的模式
     * @param string $relation 如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @return bool                        通过验证返回true;失败返回false
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function check($name, int $admin_id = 0, int $type = 1, string $mode = 'url', string $relation = 'or'): bool
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

        foreach ($this->getAuthList($admin_id) as $auth) {

            // 非鉴权接口
            $router = strtolower($auth['router']);
            if (in_array($router, $name) && $auth['auth'] == 0) {
                $authList[] = $router;
                continue;
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
     * 获取权限节点
     * @param mixed $admin_id
     * @param string $type
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getRulesNode(mixed $admin_id = 0, string $type = AUTH_RULES): array
    {
        // 私有节点
        $authGroup = $authPrivate = [];
        $admin_id = $admin_id > 1 ? $admin_id:  session('AdminLogin.id');
        $authNodes = AdminAccess::where('admin_id', $admin_id)->find();

        if (!empty($authNodes[$type])) {
            $authPrivate = explode(',', $authNodes[$type]);
        }

        // 用户组节点
        if (!empty($authNodes['group_id'])) {
            $groupNodes = AdminGroupModel::whereIn('id', $authNodes['group_id'])->select()->toArray();
            foreach ($groupNodes as $value) {
                $nodes = !empty($value[$type]) ? explode(',', $value[$type]) : [];
                $authGroup = array_unique(array_merge($authGroup, $nodes));
                $authPrivate = array_unique(array_merge($authPrivate, $nodes));
            }
        }

        // 返回数据集
        $array[$this->authGroup] = $authGroup;
        $array[$this->authPrivate] = $authPrivate;

        return $array;
    }

    /**
     * 获取权限菜单
     * @access  public
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getRulesMenu()
    {
        $authNodes = $this->getRulesNode();
        $list = $this->getAuthList(session('AdminLogin.id'), $authNodes);

        foreach ($list as $key => $value) {
            $list[$key]['title'] = __($value['title']);
            $list[$key]['router'] = url($value['router']);
        }

        if ($this->superAdmin()) {
            $authNodes['supersAdmin'] = true;
        }

        $authNodes['authorities'] = list_to_tree($list);
        return json_encode($authNodes, JSON_UNESCAPED_UNICODE);

    }

    /**
     * 查询权限列表
     * @param  $admin_id
     * @param array $nodes
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAuthList($admin_id, array $nodes = []): array
    {
        // 查找节点
        $where[] = ['status', '=', 1];
        if (!$this->superAdmin()) {
            $auth_nodes = !empty($nodes) ? $nodes : $this->getRulesNode($admin_id);
            return AdminRulesModel::where(function ($query) use ($where, $auth_nodes) {
                if (empty($auth_nodes[$this->authPrivate])) {
                    $where[] = ['auth', '=', '0'];
                    $query->where($where);
                } else {
                    $where[] = ['id', 'in', $auth_nodes[$this->authPrivate]];
                    $query->where($where)->whereOr('auth', '0');
                }
            })->order('sort asc')->select()->toArray();
        }

        return AdminRulesModel::where($where)->order('sort asc')->select()->toArray();
    }

    /**
     * 查询权限节点
     * @access public
     * @param mixed|null $type
     * @param mixed|null $class
     * @param bool $tree
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getRuleCatesTree(mixed $type = null, mixed $class = null, bool $tree = true)
    {
        $list = [];
        if (is_array($type) && $type) {
            $class = $type['class'] ?? $this->authGroup;
            $type = $type['type'] ?? AUTH_RULES;
        }

        $class = $class != $this->authGroup ? $this->authPrivate : $class;
        $auth_nodes = $this->getRulesNode(session('AdminLogin.id'), $type);
        if ($type && $type == AUTH_RULES) {
            $where[] = ['status', '=', 1];
            if (!$this->superAdmin()) {
                $list = AdminRulesModel::where(function ($query) use ($where, $auth_nodes, $class) {
                    if (empty($auth_nodes[$class])) {
                        $where[] = ['auth', '=', '0'];
                        $query->where($where);
                    } else {
                        $where[] = ['id', 'in', $auth_nodes[$class]];
                        $query->where($where)->whereOr('auth', '0');
                    }
                })->order('sort asc')->select()->toArray();
            } else {
                $list = AdminRulesModel::where($where)->order('sort asc')->select()->toArray();
            }
        } else {
            /**
             * 栏目二次开发接口
             * @param $list
             */
            if (!$this->superAdmin()) {
                if (!empty($auth_nodes[$class])) {
                    $list = Event::emit('cmsCategoryPermissions', [
                        'field' => $this->authFields,
                        'nodes' => $auth_nodes[$class]
                    ], true);
                }
            } else {
                $list = Event::emit('cmsCategoryPermissions', [
                    'field' => $this->authFields
                ], true);
            }
        }

        return $tree ? ($list ? json_encode(list_to_tree($list)) : json_encode([])) : $list;
    }

    /**
     * 校验节点 避免越权
     * @access public
     * @param null $rules
     * @param string|null $type
     * @param string $class
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function checkRuleOrCateNodes($rules = null, string $type = null, string $class = 'pri'): bool
    {
        if (!$this->superAdmin() && !empty($rules)) {
            $type = !empty($type) ? $type : AUTH_RULES;
            $class = !empty($class) ? $class : $this->authGroup;
            $class = $class != $this->authGroup ? $this->authPrivate : $class;
            $auth_nodes = $this->getRulesNode(session('AdminLogin.id'), $type);
            $differ = array_unique(array_merge($rules, $auth_nodes[$class]));
            if (count($differ) > count($auth_nodes[$class])) {
                return false;
            }
        }

        return true;
    }

    /**
     * 超级管理员
     * @access public
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function superAdmin(): bool
    {
        $groupIDs = AdminModel::field('group_id')->find(session('AdminLogin.id'));
        $groupIDs = explode(',', $groupIDs['group_id']);
        $this->groupIDs = $groupIDs;
        if (session('AdminLogin.id') == 1 || in_array(1, $groupIDs)) {
            return true;
        }
        return false;
    }

    /**
     * 管理组分级鉴权
     * @param array $groupIDs
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function checkRulesForGroup(array $groupIDs = []): bool
    {
        if ($this->superAdmin()) {
            return true;
        }

        // 查询数据
        $list = AdminGroupModel::select()->toArray();
        foreach ($list as $value) {
            // 循环处理组PID
            if (in_array($value['id'], $groupIDs)) {
                foreach ($this->groupIDs as $id) {
                    $self = list_search($list, ['id' => $id]);
                    if (!empty($self) &&
                        ($value['pid'] < $self['id'] || $value['pid'] == $self['pid'])) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * 获取用户信息
     * @param $admin_id
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAdminInfo($admin_id): array
    {
        $admin_id = $admin_id ?? get_admin_id();
        static $AdminArray = [];
        $user = Db::name('admin');
        // 获取用户表主键
        $_pk = is_string($user->getPk()) ? $user->getPk() : 'id';
        if (!isset($AdminArray[$admin_id])) {
            $AdminArray[$admin_id] = $user->where($_pk, $admin_id)->find();
        }

        return $AdminArray[$admin_id];
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
     * @param string $error 信息信息
     * @return void
     */
    protected function setError(string $error): void
    {
        $this->_error = $error;
    }

}
