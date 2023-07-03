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

use app\admin\service\AdminRuleService;
use app\AdminController;
use app\common\model\system\AdminRules as AdminRuleModel;
use support\Response;
use think\db\exception\DbException;
use Webman\Http\Request;

/**
 * 管理员规则
 * Class AdminRules
 * @package app\admin\controller\system
 */
class AdminRules extends AdminController
{
	// 初始化函数
    public function __construct()
    {
        parent::__construct();
        $this->model = new AdminRuleModel();
	}
	
    /**
     * 获取资源列表
     * return Response
     */
    public function index(): Response
    {
		if (request()->isAjax()) {
            list($count, $list) = AdminRuleService::dataList(request()->all());
            $rules = list_to_tree($list,'id','pid','children',0);
            return $this->success('获取成功', '/',$rules, $count);
		}

		return view('/system/admin/rules');
	}

	/**
	 * 添加节点数据
     * @return Response
	 */
	public function add(): Response
    {
        if (request()->isPost()) {
            $post = \request()->post();
            validate(\app\common\validate\system\AdminRules::class . '.add')->check($post);
            if ($this->model->create($post)) {
                return $this->success('添加菜单成功！');
            }
        }
        return $this->error('添加菜单失败！');
	}

	/**
	 * 编辑节点数据
     * @return Response
	 */
	public function edit(): Response
    {
        if (request()->isPost()) {
            $post = \request()->post();
            validate(\app\common\validate\system\AdminRules::class . '.edit')->check($post);
            if ($this->model->update($post)) {
                return $this->success('更新菜单成功！');
            }
        }
        return $this->error('更新菜单失败');
	}

    /**
     * 删除节点数据
     * @return Response
     * @throws DbException
     */
	public function del(): Response
    {
		$id = input('id');
		if (!empty($id)) {
			// 查询子节点
			if ($this->model->where('pid',$id)->count()) {
				return $this->error('当前菜单存在子菜单！');
			}

			// 删除单个
			if ($this->model::destroy($id)) {
				return $this->success('删除菜单成功！');
			}
		}
		
		return $this->error('删除失败，请检查您的参数！');
	}

}
