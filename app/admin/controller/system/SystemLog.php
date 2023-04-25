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
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use app\common\model\system\SystemLog as SystemLogModel;

/**
 * 系统日志
 * Class SystemLog
 * @package app\admin\controller\system
 */
class SystemLog extends AdminController
{
	// 初始化函数
    public function __construct()
    {
        parent::__construct();
        $this->model = new SystemLogModel();
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
        
        if (request()->isAjax()) {
            // 获取数据
            $post = \request()->all();
            $page = (int)input('page') ?? 1;
            $limit = (int)input('limit') ?? 18;
            
            // 生成查询数据
            $where = array();
            if (!empty($post['name'])) {
                $where[] = ['url','like','%'.$post['name'].'%'];
            }

            if (!empty($post['type']) && $post['type'] == 'user') {
                $where[] = ['name','<>','system'];
            }else if (!empty($post['type']) && $post['type'] == 'system') {
                $where[] = ['name','=','system'];
            }

            if (!empty($post['status']) && $post['status'] == 'normal') {
                $where[] = ['error','=',null];
            }else if (!empty($post['status']) && $post['status'] == 'error') {
                $where[] = ['error','<>',''];
            }

            $where[] = ['status','=','1'];
            $count = $this->model->where($where)->count();
            $page = ($count <= $limit) ? 1 : $page;
            $list = $this->model->where($where)->order('id', 'desc')->limit((int)$limit)->page((int)$page)->select()->toArray();
            return $this->success('查询成功', "", $list, $count);
        }

        return view('/system/system_log/index');
    }
}