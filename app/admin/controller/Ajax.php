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
namespace app\admin\controller;

use app\AdminController;
use app\common\library\ResultCode;
use app\common\library\Upload;

/**
 * Ajax类
 * Class Ajax
 * @package app\admin\controller
 */
class Ajax extends AdminController
{
    /**
     * 测试接口
     * @return \support\Response
     */
    public function index(): \support\Response
    {
        return json(ResultCode::SUCCESS);
    }

    /**
     * 文件上传
     * @return \support\Response|void
     * @throws \Exception
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
     * @return mixed
     */
    public function getImage()
    {
        if (request()->isPost()) {
            $file = Upload::instance()->download(input('url'));
            if (!$file) {
                return $this->error(Upload::instance()->getError());
            }
            return json($file);
        }
    }

}
