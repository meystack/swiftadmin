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
namespace app\admin\controller;

use app\AdminController;
use app\common\library\ResultCode;
use app\common\library\Upload;
use Psr\SimpleCache\InvalidArgumentException;
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * Ajax类
 * Class Ajax
 * @package app\admin\controller
 */
class Ajax extends AdminController
{
    /**
     * 初始化方法
     * @return void
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 测试接口
     * @return Response
     */
    public function index(): Response
    {
        return json(ResultCode::SUCCESS);
    }

    /**
     * 文件上传
     * @return Response|void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public function upload()
    {
        if (request()->isPost()) {
            $file = Upload::instance()->upload();
            if (!$file) {
                return $this->error(Upload::instance()->getError());
            }
            return json($file);
        }
    }

    /**
     * 远程下载图片
     * @return Response
     * @throws InvalidArgumentException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getImage(): Response
    {
        if (request()->isPost()) {
            $url = request()->post('url');
            if (empty($url)) {
                return $this->error('图片地址不能为空');
            }
            $file = Upload::instance()->download($url);
            if (!$file) {
                return $this->error(Upload::instance()->getError());
            }
            return json($file);
        }

        return json(ResultCode::EXCEPTION);
    }

}
