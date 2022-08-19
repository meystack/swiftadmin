<?php
declare (strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------

namespace app\index\controller;

use app\common\library\Auth;
use app\common\library\ResultCode;
use app\HomeController;
use app\common\model\system\User;
use app\common\model\system\UserThird;
use support\Response;
use system\Random;

/**
 * 社会化登录
 * @ QQ 微信 微博
 */
class Third extends HomeController
{
    /**
     * 类型
     * @var string
     */
    public $type = null;

    /**
     * 类型实例
     * @var Object
     */
    public $oauth = null;

    /**
     * @var array
     */
    public $repeatLogin = [];

    /**
     * 初始化构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取第三方登录配置
     * @return mixed|string
     * @throws \Exception
     */
    private function oType()
    {
        $this->type = input('type');
        $class = "\\system\\third\\" . $this->type;
        if (!class_exists($class)) {
            throw new \Exception('暂时还不支持该方式扩展');
        }

        return new $class;
    }

    /**
     * 用户登录操作
     */
    public function login(): \support\Response
    {
        try {
            $this->oauth = $this->oType();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        $referer = input('ref', request()->server('HTTP_REFERER', '/'));
        request()->cookie('redirectUrl', $referer);
        return $this->oauth->login();
    }

    /**
     * 用户回调函数
     * @return mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function callback()
    {
        try {
            $this->oauth = $this->oType();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        $userInfos = $this->oauth->getUserInfo();
        if (!empty($userInfos) && !$this->auth->isLogin()) {
            return $this->register($userInfos, $this->type);
        } else if ($this->auth->isLogin()) { // 绑定用户
            return $this->doBind($userInfos, $this->type);
        }
    }

    /**
     * 用户注册操作
     * @param array $userInfos
     * @param string|null $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function register(array $userInfos = [], string $type = null)
    {
        $openid = $userInfos['openid'] ?? $userInfos['id'];
        $nickname = $userInfos['userinfo']['name'] ?? $userInfos['userinfo']['nickname'];
        $userInfo = UserThird::alias('th')
                             ->view('user', '*', 'user.id=th.user_id')
                             ->where(['openid' => $openid, 'type' => $type])
                             ->find();

        if (!empty($userInfo)) {
            $array['id'] = $userInfo['id'];
            $array['login_time'] = time();
            $array['login_ip'] = request()->getRemoteIp();
            $array['login_count'] = $userInfo['login_count'] + 1;

            if (User::update($array)) {
                $response = $this->auth->responseToken($userInfo);
                $response->withBody(json_encode(ResultCode::LOGINSUCCESS))->redirect(request()->cookie('redirectUrl', '/'));
            }

        } else {

            // 注册本地用户
            $data['nickname'] = $nickname;
            $data['avatar'] = $userInfos['userinfo']['avatar'];
            if (User::getByNickname($nickname)) {
                $data['nickname'] .= Random::alpha(3);
            }
            $data['group_id'] = 1;
            $data['create_ip'] = request()->getRemoteIp();
            $result = $this->auth->register($data);

            // 封装第三方数据
            if (!empty($result)) {
                $userThird = [
                    'type'          => $this->type,
                    'user_id'       => $result['id'],
                    'openid'        => $openid,
                    'nickname'      => $nickname,
                    'access_token'  => $userInfos['access_token'],
                    'refresh_token' => $userInfos['refresh_token'],
                    'expires_in'    => $userInfos['expires_in'],
                    'login_time'    => time(),
                    'expiretime'    => time() + $userInfos['expires_in'],
                ];
            }

            // 注册第三方数据
            if (isset($userThird) && is_array($userThird)) {
                if (UserThird::create($userThird)) {
                    $response = $this->auth->responseToken($result);
                    $response->withBody(json_encode(ResultCode::LOGINSUCCESS))->redirect(request()->cookie('redirectUrl', '/'));
                }
            }
        }

        return $this->error('登录失败');
    }

    /**
     * 用户绑定操作
     * @return Response
     */
    public function bind(): \support\Response
    {
        if (Auth::instance()->isLogin()) {
            $buildQuery = [
                'bind' => true,
                'type' => input('type'),
                'ref'  => input('ref', request()->server('HTTP_REFERER', '/')),
            ];

           return $this->redirect("/third/login?" . http_build_query($buildQuery));
        }

        return $this->error('请先登录');
    }

    /**
     * 用户解除绑定
     * @return mixed|void
     * @throws \think\db\exception\DbException
     */
    public function unbind(): \support\Response
    {
        try {
            $this->oauth = $this->oType();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        if ($this->auth->isLogin()) {

            $result = $this->auth->userInfo;
            if (!empty($result)) {

                if (empty($result['email']) || empty($result['pwd'])) {
                    return $this->error('解除绑定需要设置邮箱和密码！');
                }

                $where['type'] = $this->type;
                $where['user_id'] = request()->cookie('uid');
                if (UserThird::where($where)->delete()) {
                    return $this->success('解除绑定成功！');
                }
            }
        }

        return $this->error();
    }

    /**
     * 用户绑定操作实例
     * @param array $userInfos
     * @param string|null $type
     * @return \support\Response|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function doBind(array $userInfos = [], string $type = null)
    {

        $openid = $userInfos['openid'] ?? $userInfos['id'];
        $nickname = $userInfos['userinfo']['name'] ?? $userInfos['userinfo']['nickname'];

        // 查询是否被注册
        $where['openid'] = $openid;
        $where['type'] = $this->type;
        if (!UserThird::where($where)->find()) {

            // 拼装数据
            $third = [
                'type'          => $type,
                'user_id'       => request()->cookie('uid'),
                'openid'        => $openid,
                'nickname'      => $nickname,
                'access_token'  => $userInfos['access_token'],
                'refresh_token' => $userInfos['refresh_token'],
                'expires_in'    => $userInfos['expires_in'],
                'login_time'    => time(),
                'expiretime'    => time() + $userInfos['expires_in'],
            ];

            if (UserThird::create($third)) {
                return $this->redirectUrl();
            } else {
                return $this->error('绑定异常');
            }
        }

        return $this->error('当前用户已被其他账户绑定！');
    }

    /**
     * 跳转URL
     * @return void
     */
    protected function redirectUrl(): \support\Response
    {
        $referer = request()->cookie('redirectUrl', '/');

        if (preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $referer = '/';
        }

        request()->cookie('redirectUrl', null,1);
        return $this->redirect($referer);
    }


}
