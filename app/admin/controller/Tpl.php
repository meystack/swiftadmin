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
use support\Response;

class Tpl extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 读取模板列表
     * @return Response
     */
    public function showTpl(): Response
    {
        // 读取配置文件
        $list = include (base_path().'/extend/conf/tpl/tpl.php');
        foreach ($list as $key => $value) {
            $list[$key]['param'] = str_replace('extend/conf/tpl/','',$value['path']);
        }
        
        return view('/tpl/show_tpl',['list'=>$list]);
    }

    /**
     * 编辑邮件模板
     * @return Response
     */
    public function editTpl(): Response
    {
        if (request()->isPost()) {
            $post = request()->post();
            $tpl = base_path().'/extend/conf/tpl/'.$post['tpl'];
            if (write_file($tpl,$post['content'])) {
                return $this->success('修改邮件模板成功！');
            }
            
            return $this->error('修改邮件模板失败！');
        }

        // 获取模板参数
        $tpl = input('p');
        $content = read_file(base_path().'/extend/conf/tpl/'.$tpl);
        return view('/tpl/edit_tpl',['tpl'=>$tpl,'content'=>$content]);
    }

}
