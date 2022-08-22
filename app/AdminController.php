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
namespace app;

use app\admin\library\Auth;
use app\common\model\system\SystemLog;
use Psr\SimpleCache\InvalidArgumentException;
use support\Response;
use think\helper\Str;
use Webman\Http\Request;

class AdminController extends BaseController
{

    /**
     * 数据库实例
     * @var object
     */
    public $model = null;

    /**
     * 是否验证
     * @var bool
     */
    public $isValidate = true;

    /**
     * 验证场景
     * @var string
     */
    public $scene = '';

    /**
     * 数据表名称
     * @var string
     */
    public $tableName = null;

    /**
     * 控制器/类名
     * @var string
     */
    public $controller = null;

    /**
     * 控制器方法
     * @var string
     */
    public $action = null;

    /**
     * 控制器/方法名
     * @var string
     */
    public $method = null;

    /**
     * 操作状态
     * @var int
     */
    public $status = false;

    /**
     * 管理员信息
     * @var array
     */
    public $admin = [];

    /**
     * 管理员会话标识
     * @var string
     */
    public $sename = 'AdminLogin';

    /**
     * 获取模板
     * @access   protected
     * @var      string
     */
    public $template = null;

    /**
     * 权限验证类
     * @var object
     */
    public $auth = null;

    /**
     * 当前表字段
     *
     * @var array
     */
    protected $tableFields = [];

    /**
     * 默认开关
     *
     * @var string
     */
    protected $keepField = 'status';

    /**
     * 开启数据限制
     * 默认关闭
     * @var boolean
     */
    protected $dataLimit = false;

    /**
     * 数据限制字段
     *
     * @var string
     */
    protected $dataLimitField = 'admin_id';

    /**
     * 需要排除的字段
     *
     * @var string
     */
    protected $ruleOutFields = "";

    /**
     * 查询过滤字段
     *
     * @var array
     */
    protected $filterWhere = ['page', 'limit'];

    /**
     * 查询转换字段
     *
     * @var array
     */
    protected $converTime = ['create_time', 'update_time', 'delete_time'];

    /**
     * 跳转URL地址
     * @var string
     */
    protected $JumpUrl = '/';

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->auth = Auth::instance();
    }

    /**
     * 获取资源
     * @return mixed
     */
    public function index()
    {
        if (request()->isAjax()) {
            $page = (int)input('page') ?? 1;
            $limit = (int)input('limit') ?? 18;
            $where = $this->buildSelectParams();
            $count = $this->model->where($where)->count();
            $limit = is_empty($limit) ? 10 : $limit;
            $page = $count <= $limit ? 1 : $page;
            $order = !array_key_exists('sort', $this->model->getFields()) ? 'id' : 'sort';
            $subQuery = $this->model->field('id')->where($where)->order($order, 'desc')->limit($limit)->page($page)->buildSql();
            $subQuery = '( SELECT object.id FROM ' . $subQuery . ' AS object )';
            $list = $this->model->where('id in' . $subQuery)->order($order, 'desc')->select()->toArray();
            return $this->success('查询成功', null, $list, $count);
        }

        return $this->view();
    }

    /**
     * 添加资源
     * @return Response|void
     */
    public function add()
    {
        if (request()->isPost()) {

            $post = $this->preRuleOutFields(\request()->post());
            if ($this->dataLimit) {
                $post[$this->dataLimitField] = $this->admin['id'];
            }

            $validate = $this->isValidate ? get_class($this->model) : $this->isValidate;
            $post = request_validate_rules($post, $validate, $this->scene);
            if (empty($post) || !is_array($post)) {
                return $this->error($post);
            }

            $this->status = $this->model->create($post);
            return $this->status ? $this->success() : $this->error();
        }

        return $this->view('', ['data' => $this->getTableFields()]);
    }

    /**
     * 编辑资源
     * @return Response|void
     */
    public function edit()
    {
        $id = input('id');
        $data = $this->model->find($id);

        // 限制数据调用
        if (!$this->auth->SuperAdmin() && $this->dataLimit
            && in_array($this->dataLimitField, $this->model->getFields())) {
            if ($data[$this->dataLimitField] != $this->admin['id']) {
                return $this->error('没有权限');
            }
        }

        if (request()->isPost()) {
            $post = $this->preRuleOutFields(\request()->post());
            $validate = $this->isValidate ? get_class($this->model) : $this->isValidate;
            $post = request_validate_rules($post, $validate, $this->scene);
            if (empty($post) || !is_array($post)) {
                return $this->error($post);
            }

            $this->status = $this->model->update($post);
            return $this->status ? $this->success() : $this->error();
        }

        /**
         * 默认共享模板
         */
        return $this->view(Str::lower(request()->getController()) . '/add', [
            'data' => $data
        ]);
    }

    /**
     * 删除资源
     * @return Response|void
     */
    public function del()
    {
        $id = input('id');
        if (!is_array($id)) {
            $id = [$id];
        }

        try {
            $list = $this->model->whereIn('id', $id)->select();
            foreach ($list as $item) {
                if (!$this->auth->SuperAdmin() && $this->dataLimit
                    && in_array($this->dataLimitField, $this->model->getFields())) {
                    if ($item[$this->dataLimitField] != $this->admin['id']) {
                        continue;
                    }
                }
                if (isset($item->isSystem) && $item->isSystem) {
                    throw new \Exception('禁止删除系统级数据');
                }

                $item->delete();
                $this->status = true;
            }
        } catch (\Throwable $th) {
            $this->status = false;
            return $this->error($th->getMessage());
        }

        return $this->status ? $this->success() : $this->error();
    }

    /**
     * 修改资源状态
     * @return Response|void
     */
    public function status()
    {
        if (request()->isAjax()) {

            $where[] = ['id', '=', input('id')];
            if (!$this->auth->SuperAdmin() && $this->dataLimit
                && in_array($this->dataLimitField, $this->model->getFields())) {
                $where[] = [$this->dataLimitField, '=', $this->admin['id']];
            }

            try {
                $this->status = $this->model->where($where)->update(['status' => input('status')]);
            } catch (\Throwable $th) {
                return $this->error($th->getMessage());
            }

            if ($this->status) {
                return $this->success();
            }
        }

        return $this->error();
    }

    /**
     * 数据表排序
     * @return Response|void
     */
    public function sort()
    {
        if (request()->isPost()) {

            if (array_search('sort', $this->model->getTableFields())) {
                try {

                    $ids = request()->post('ids');
                    $list = $this->model->where('id', 'in', $ids)->orderRaw('field(id,' . implode(',', $ids) . ')')->select()->toArray();
                    $newSort = array_column($list, 'sort');
                    rsort($newSort);
                    $array = [];

                    // 循环处理排序字段
                    foreach ($list as $key => $value) {
                        $array[] = [
                            'id'   => $value['id'],
                            'sort' => $newSort[$key],
                        ];
                    }

                    $this->model->saveAll($array);
                } catch (\Throwable $th) {
                    return $this->error($th->getMessage());
                }

            } else {
                return $this->error('数据表未包含排序字段');
            }
        }

        return $this->success();
    }

    /**
     * 自动获取view模板
     * @param string $template
     * @param array $vars
     * @param null $app
     * @return Response
     */
    public function view(string $template = '', array $vars = [], $app = null): Response
    {
        $request = explode('/', \request()->getController());
        if (empty($template)) {
            $parseArr = array_map(function ($item) {
                return Str::snake($item);
            }, $request);
            $template = implode('/', $parseArr) . '/' . Str::snake(\request()->getAction());
        }

        return view($template, $vars, $app);
    }

    /**
     * 排除特定字段
     *
     * @param [type] $params
     * @return array
     */
    protected function preRuleOutFields($params): array
    {
        if (is_array($this->ruleOutFields)) {
            foreach ($this->ruleOutFields as $field) {
                if (key_exists($field, $params)) {
                    unset($params[$field]);
                }
            }
        } else {
            if (key_exists($this->ruleOutFields, $params)) {
                unset($params[$this->ruleOutFields]);
            }
        }

        return $params;
    }

    /**
     * 获取查询参数
     *
     * @return array|false
     */
    protected function buildSelectParams()
    {
        $params = request()->all();

        if (!empty($params) && is_array($params)) {

            $where = [];
            $this->tableFields = $this->model->getFields();
            foreach ($params as $field => $value) {

                // 过滤字段
                if (in_array($field, $this->filterWhere)) {
                    continue;
                }

                // 非表内字段
                if (!array_key_exists($field, $this->tableFields)) {
                    continue;
                }

                // 默认状态字段
                if ($field == $this->keepField && $value) {
                    $where[] = [$field, '=', intval($value - 1)];
                    continue;
                }

                // 获取类型
                $type = $this->tableFields[$field]['type'];
                $type = explode('(', $type)[0];
                $value = str_replace('/\s+/', '', $value);
                switch ($type) {
                    case 'char':
                    case 'text':
                    case 'varchar':
                    case 'tinytext':
                    case 'longtext':
                        $where[] = [$field, 'like', '%' . $value . '%'];
                        break;
                    case 'int':
                    case 'bigint':
                    case 'integer':
                    case 'tinyint':
                    case 'smallint':
                    case 'mediumint':
                    case 'float':
                    case 'double':
                    case 'timestamp':
                    case 'year':
                        $value = str_replace(',', '-', $value);
                        if (strpos($value, '-')) {

                            $arr = explode(' - ', $value);
                            if (empty($arr)) {
                                continue 2;
                            }

                            if (in_array($field, $this->converTime)) {

                                if (isset($arr[0])) {
                                    $arr[0] = strtotime($arr[0]);
                                }

                                if (isset($arr[1])) {
                                    $arr[1] = strtotime($arr[1]);
                                }
                            }

                            $exp = 'between';
                            if ($arr[0] === '') {
                                $exp = '<=';
                                $arr = $arr[1];
                            } elseif ($arr[1] === '') {
                                $exp = '>=';
                                $arr = $arr[0];
                            }

                            $where[] = [$field, $exp, $arr];
                        } else {
                            $where[] = [$field, '=', $value];
                        }
                        break;
                    case 'set';
                        $where[] = [$field, 'find in set', $value];
                        break;
                    case 'enum';
                        $where[] = [$field, '=', $value];
                        break;
                    case 'date';
                    case 'time';
                    case 'datetime';
                        $value = str_replace(',', '-', $value);

                        if (strpos($value, '-')) {
                            $arr = explode(' - ', $value);
                            if (!array_filter($arr)) {
                                continue 2;
                            }

                            $exp = '=';
                            if ($arr[0] === '') {
                                $exp = '<= TIME';
                                $arr = $arr[1];
                            } elseif ($arr[1] === '') {
                                $exp = '>= TIME';
                                $arr = $arr[0];
                            }

                            $where[] = [$field, $exp, $arr];
                        } else {
                            $where[] = [$field, '=', $value];
                        }

                        break;
                    case 'blob';
                        break;
                    default:
                        // 默认值
                        break;
                }
            }

            // 限制数据字段
            if (!$this->auth->SuperAdmin() && $this->dataLimit) {
                if (in_array($this->dataLimitField, $this->tableFields)) {
                    $where[] = [$this->dataLimitField, '=', $this->admin['id']];
                }
            }

            return $where;
        }

        return false;
    }

    /**
     * 递归查询父节点
     * @access public
     * @param int $pid 查询条件
     * @param array $array 返回数组
     * @return array
     */
    public function parentNode(int $pid, array &$array = []): array
    {
        $result = $this->model->where('id', $pid)->find()->toArray();
        if (!empty($result)) {
            /**
             * 多语言字段
             */
            if (isset($result['title'])) {
                $result['title'] = __($result['title']);
            }

            $array[] = $result;
            if ($result['pid'] !== 0) {
                $this->parentNode($result['pid'], $array);
            }
        }

        return $array;
    }


    /**
     * 管理员退出
     * @return Response
     */
    public function logout(): Response
    {
        request()->session()->set($this->sename, null);
        return $this->success('退出成功！', $this->JumpUrl);
    }


    /**
     * 错误页面
     * @param int $code
     * @param string $msg
     * @return Response
     */
    public function abortPage(string $msg = '', int $code = 404): Response
    {
        $exception = config('app.exception_template');
        if (isset($exception[$code])) {
            $template = @file_get_contents($exception[$code]);
        } else {
            $template = $msg;
        }

        return \response($template, $code);
    }
}