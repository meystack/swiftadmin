<?php

namespace app\mobile\controller;

use app\HomeController;
use support\Response;

class Index extends HomeController
{
    /**
     * 手机端控制器
     * @return Response
     */
    public function index(): Response
    {
        return response('Hello swift Mobile!');
    }
}