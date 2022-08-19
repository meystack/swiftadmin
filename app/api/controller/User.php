<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\ApiController;
use app\common\library\ResultCode;
use app\common\library\Sms;
use app\common\library\Upload;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * API用户登录
 */
class User extends ApiController
{
    /**
     * 需要登录
     * @var bool
     */
	public $needLogin = true;

    /**
     * 非鉴权方法
     * @var array
     */
    public $noNeedAuth = ['register', 'login'];

    /**
     * 用户注册
     * @return mixed|void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function register()
    {
        if (request()->isPost()) {

            // 获取参数
            $post = input('post.');

            // 获取注册方式
            $registerType = saenv('user_register');

            if ($registerType == 'mobile') {
                $mobile = input('mobile');
                $captcha = input('captcha');

                // 校验手机验证码
                if (!Sms::instance()->check($mobile, $captcha, 'register')) {
                    return $this->error(Sms::instance()->getError());
                }
            }

            $response = $this->auth->register($post);
            if (!$response) {
                return $this->error($this->auth->getError());
            }

            return $response->withBody(json_encode(ResultCode::REGISTERSUCCESS));
        }
    }

    /**
     * 用户登录
     * @return mixed|void
     */
    public function login() {

        if (request()->isPost()) {
			// 获取参数
			$nickname = input('nickname');
            $password = input('pwd');

            $response = $this->auth->login($nickname, $password);
            if (!$response) {
                return $this->error($this->auth->getError());
            }

            $response->withBody(json_encode(array_merge(ResultCode::LOGINSUCCESS, ['token' => $this->auth->token])));
            return $response;
		}

        return $this->throwError();
    }

    /**
     * 文件上传
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
}
