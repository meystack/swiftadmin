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
namespace app\admin\controller\system;


use app\AdminController;
use app\common\library\Ip2Region;
use app\common\model\system\User as UserModel;
use app\common\model\system\UserGroup as UserGroupModel;
use support\Response;
use system\Random;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 用户管理
 * Class User
 * @package app\admin\controller\system
 */
class User extends AdminController
{
    // 初始化函数
    public function __construct()
    {
        parent::__construct();
        $this->model = new UserModel();
    }

    /**
     * 获取资源
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index(): \support\Response
    {
        $userGroup = UserGroupModel::select()->toArray();
        if (request()->isAjax()) {

            // 获取数据
            $post = \request()->all();
            $page = (int)input('page') ?? 1;
            $limit = (int)input('limit') ?? 10;
            $status = !empty($post['status']) ? (int)$post['status'] - 1 : 1;
            // 生成查询条件
            $where = array();
            if (!empty($post['nickname'])) {
                $where[] = ['nickname', 'like', '%' . $post['nickname'] . '%'];
            }

            if (!empty($post['group_id'])) {
                $where[] = ['group_id', 'find in set', $post['group_id']];
            }

            // 生成查询数据
            $where[] = ['status', '=', $status];
            $count = $this->model->where($where)->count();
            $page = ($count <= $limit) ? 1 : $page;
            $list = $this->model->where($where)->order("id asc")->limit((int)$limit)->page((int)$page)->select();

            // 循环处理数据
            foreach ($list as $key => $value) {

                $value->hidden(['pwd', 'salt']);
                $region = Ip2Region::instance()->memorySearch($value['login_ip']);
                $region = explode('|', $region['region']);
                $list[$key]['region'] = $region;
                $result = list_search($userGroup, ['id' => $value['group_id']]);
                if (!empty($result)) {
                    $list[$key]['group'] = $result['title'];
                }
            }

            // TODO..
            return $this->success('查询成功', "", $list, $count);
        }

        return view('/system/user/index', [
            'UserGroup' => $userGroup,
        ]);
    }

    /**
     * 添加会员
     */
    public function add()
    {
        if (request()->isPost()) {
            $post = request()->post();
            $post = request_validate_rules($post, get_class($this->model));
            if (empty($post) || !is_array($post)) {
                return $this->error($post);
            }

            // 禁止重复注册
            $whereName[] = ['nickname', '=', $post['nickname']];
            $whereEmail[] = ['email', '=', $post['email']];
            if ($this->model->whereOr([$whereName, $whereEmail])->find()) {
                return $this->error('该用户ID或邮箱已经存在！');
            }

            // 生成密码
            $salt = Random::alpha();
            $post['salt'] = $salt;
            $post['pwd'] = encryptPwd($post['pwd'], $post['salt']);
            if ($this->model->create($post)) {
                return $this->success('注册成功！');
            }

            return $this->error('注册失败！');
        }
    }

    /**
     * 编辑会员
     */
    public function edit()
    {

        if (request()->isPost()) {

            $post = \request()->post();

            // 查询数据
            $data = $this->model->find($post['id']);
            if ($data['nickname'] != $post['nickname']) {
                $whereName[] = ['nickname', '=', $post['nickname']];
                if ($this->model->where($whereName)->find()) {
                    return $this->error('该用户ID已经存在！');
                }
            }

            if ($data['email'] != $post['email']) {
                $whereEmail[] = ['email', '=', $post['email']];
                if ($this->model->where($whereEmail)->find()) {
                    return $this->error('该用户邮箱已经存在！');
                }
            }

            // 为空则去掉密码
            if (empty($post['pwd'])) {
                unset($post['pwd']);
            } else {
                $salt = Random::alpha();
                $post['salt'] = $salt;
                $post['pwd'] = encryptPwd($post['pwd'], $post['salt']);
            }

            if ($this->model->update($post)) {
                return $this->success('更新成功！');
            }

            return $this->error('更新失败！');
        }
    }

    /**
     * 删除会员
     */
    public function del(): Response
    {
       return $this->error('不允许删除会员');
    }

}   
