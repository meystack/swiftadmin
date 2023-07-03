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


use app\AdminController;
use app\common\model\system\Company as CompanyModel;
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Http\Request;

/**
 * 公司信息
 * Class Company
 * @package app\admin\controller\system
 */
class Company extends AdminController
{

    // 初始化函数
    public function __construct()
    {
        parent::__construct();
        $this->model = new CompanyModel();
    }

    /**
     * 获取资源列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index(): \support\Response
    {
        if (request()->isAjax()) {

            // 生成查询条件
            $post = input();
            $where = array();
            if (!empty($post['title'])) {
                $where[] = ['title', 'like', '%' . $post['title'] . '%'];
            }

            // 生成查询数据
            $list = $this->model->where($where)->select()->toArray();
            return $this->success('查询成功', null, $list, count($list));
        }

        return view('/system/company/index');
    }

    /**
     * 添加公司信息
     * @return Response
     */
    public function add(): Response
    {
        if (request()->isPost()) {
            $post = request()->post();
            if ($this->model->create($post)) {
                return $this->success();
            }

            return $this->error();
        }

        return view('/system/company/add', [
            'data' => $this->getTableFields()
        ]);
    }

    /**
     * 编辑公司信息
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function edit(): Response
    {
        $id = input('id');
        if (request()->isPost()) {
            $post = request()->post();
            if ($this->model->update($post)) {
                return $this->success();
            }
            return $this->error();
        }

        $data = $this->model->find($id);
        return view('/system/company/add', [
            'data' => $data
        ]);
    }

}   