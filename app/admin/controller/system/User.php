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


use app\admin\service\UserService;
use app\AdminController;
use app\common\exception\OperateException;
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
    public function index(): Response
    {
        if (request()->isAjax()) {
            $post = request()->all();
            list($count, $list) = UserService::dataList($post);
            return $this->success('查询成功', "/", $list, $count);
        }

        return view('/system/user/index', [
            'UserGroup' =>  UserGroupModel::select()->toArray()
        ]);
    }

    /**
     * 添加会员
     * @return Response
     * @throws OperateException
     */
    public function add(): Response
    {
        if (request()->isPost()) {
            $post = request()->post();
            validate(\app\common\validate\system\User::class)->scene('add')->check($post);
            UserService::add($post);
            return $this->success('注册成功！');
        }
        return $this->error('注册失败！');
    }

    /**
     * 编辑会员
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws OperateException
     */
    public function edit(): Response
    {
        if (request()->isPost()) {
            $post = request()->post();
            validate(\app\common\validate\system\User::class)->scene('edit')->check($post);
            UserService::edit($post);
            return $this->success('更新成功！');
        }
        return $this->error('更新失败！');
    }

    /**
     * 删除会员
     */
    public function del(): Response
    {
       return $this->error('不允许删除会员');
    }

}   
