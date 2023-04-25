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

use app\common\model\system\Attachment as AttachmentModel;
use Webman\Http\Request;

/**
 * 附件管理
 * Class Attachment
 * @package app\admin\controller\system
 */
class Attachment extends AdminController
{
    /**
     * 上传文件夹地址
     * @var mixed
     */
    protected mixed $upload;

    /**
     * 初始化函数
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new AttachmentModel();
        $this->upload = saenv('upload_path');
    }

    /**
     * 获取资源列表
     */
    public function index()
    {
        if (request()->isAjax()) {

            // 生成查询条件
            $post  = request()->post();
            $page = (int)input('page') ?: 1;
            $limit = (int)input('limit') ?: 10;
            $type = input('type','');

            $where = [];
            if (!empty($post['filename'])) {
                $where[] = ['filename','like','%'.$post['filename'].'%'];
            }

            if (!empty($type)) {
                $where[] = ['type','=',$type];
            }

            $count = $this->model->where($where)->count();
            $page = ($count <= $limit) ? 1 : $page;            

            // 生成查询数据
            $list = $this->model->where($where)->order("id desc")->limit((int)$limit)->page((int)$page)->select()->toArray();
            return $this->success('查询成功', "", $list, $count);
        }

		return view('/system/attachment/index',[
            'choose' => input('choose') ?: '',
        ]);

    }
}
