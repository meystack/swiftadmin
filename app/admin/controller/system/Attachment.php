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

use app\admin\service\AttachmentService;
use app\AdminController;

use app\common\model\system\Attachment as AttachmentModel;
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
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
     * Attachment constructor.
     * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new AttachmentModel();
        $this->upload = saenv('upload_path');
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

            $params = request()->post();
            list($count, $list) = AttachmentService::dataList($params);
            return $this->success('查询成功', "/", $list, $count);
        }

		return view('/system/attachment/index',[
            'choose' => input('choose') ?: '',
        ]);

    }
}
