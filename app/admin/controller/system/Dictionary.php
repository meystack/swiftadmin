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
use app\common\model\system\Dictionary as DictionaryModel;
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 字典管理
 * Class Dictionary
 * @package app\admin\controller\system
 */
class Dictionary extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new DictionaryModel();
    }

    /**
     * 字典首页
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index(): \support\Response
    {
        $post = input();
        $pid = input('pid'); 
        $limit = input('limit') ?? 10;
        $page = input('page') ?? 1;
        if ($pid == null) {
            $pid = (string)$this->model->minId();
        } 

        if (request()->isAjax()) {

            // 生成查询数据
            $pid = !str_contains($pid, ',') ? $pid : explode(',',$pid);
            $where[] = ['pid','in',$pid];
            if (!empty($post['name'])) {
                $where[] = ['name','like','%'.$post['name'].'%'];
            }

            $count = $this->model->where($where)->count();
            $list = $this->model->where($where)->limit((int)$limit)->page((int)$page)->select()
                ->each(function($item,$key) use ($pid){
                if ($key == 0 && $pid == '0') {
                    $item['LAY_CHECKED'] = true;
                }

                return $item;
            });

            return $this->success('查询成功', null, $list, $count);
        }

        return view('/system/dictionary/index',[ 'pid' => $pid]);
    }
}
