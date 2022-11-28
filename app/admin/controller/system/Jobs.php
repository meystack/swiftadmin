<?php
declare (strict_types = 1);
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
use app\common\model\system\Jobs as JobsModel;
use Webman\Http\Request;

/**
 * 岗位管理
 * Class Jobs
 * @package app\admin\controller\system
 */
class Jobs extends AdminController
{
	// 初始化函数
    public function __construct()
    {
        parent::__construct();
        $this->model = new JobsModel();
	}
	
    /**
     * 获取资源列表
     */
    public function index()
    {
		if (request()->isAjax()) {
			
			$param = request()->all();

			$param['page'] = input('page');
			$param['limit'] = input('limit');

			// 查询条件
			$where = array();
			if (!empty($param['title'])) {
				$where[] = ['title','like','%'.$param['title'].'%'];
			}
			if (!empty($param['alias'])) {
				$where[] = ['alias','like','%'.$param['alias'].'%'];
			}
			if (!empty($param['content'])) {
				$where[] = ['content','like','%'.$param['content'].'%'];
			}

			// 查询数据
            $count = $this->model->where($where)->count();
            $limit = empty($param['limit']) ? 10 : (int)$param['limit'];
            $page = ($count <= $limit) ? 1 : $param['page'];
			$list = $this->model->where($where)->order("id asc")->limit((int)$limit)->page((int)$page)->select()->toArray();
			foreach ($list as $key => $value) {
				$list[$key]['title'] = __($value['title']);
			}

			return $this->success('查询成功', null, $list, $count);
		}

		return view('/system/jobs/index');
	}

	/**
	 * 添加岗位数据
	 */
	public function add() 
	{
		if (request()->isPost()) {
			$post = request()->post();
			$post = request_validate_rules($post, get_class($this->model));
			if (empty($post) || !is_array($post)) {
				return $this->error($post);
			}
			if ($this->model->create($post)) {
				return $this->success('添加岗位成功！');
			}else {
				return $this->error('添加岗位失败！');
			}
		}
	}

	/**
	 * 编辑岗位数据
	 */
	public function edit() 
	{
		if (request()->isPost()) {
			$post = request()->post();
			$post = request_validate_rules($post, get_class($this->model));
			if (empty($post) || !is_array($post)) {
				return $this->error($post);
			}
			if ($this->model->update($post)) {				
				return $this->success('更新岗位成功！');
			}else {
				return $this->error('更新岗位失败');
			}
		}	
	}

	/**
	 * 删除岗位数据
	 */
	public function del() 
	{
		$id = input('id');
		if (!empty($id) && is_numeric($id)) {
			if ($this->model::destroy($id)) {
				return $this->success('删除岗位成功！');
			}
		}
		
		return $this->error('删除失败，请检查您的参数！');
	}
}