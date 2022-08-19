<?php

namespace app\mobile\controller;

use app\HomeController;

class Index extends HomeController
{
    /**
     * 手机端控制器
     * @return \support\Response
     */
    public function index(): \support\Response
    {
        return response('Hello swift Mobile!');
    }
}