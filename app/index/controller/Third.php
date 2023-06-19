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

use app\common\library\ResultCode;
use app\common\service\user\UserService;
use app\common\service\user\UserTokenService;
use app\HomeController;
use app\common\model\system\User;
use app\common\model\system\UserThird;
use Psr\SimpleCache\InvalidArgumentException;
use support\Response;
use system\Random;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 社会化登录
 * @ QQ 微信 微博
 */
class Third extends HomeController
{
    /**
     * 类型
     * @var mixed
     */
    public mixed $type;

    /**
     * 类型实例
     * @var mixed
     */
    public mixed $oauth;

    /**
     * @var array
     */
    public array $repeatLogin = [];

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
    public function login(): Response
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
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException
     */
    public function callback()
    {
        try {
            $this->oauth = $this->oType();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        $user = $this->oauth->getUserInfo();
        if (!empty($user) && !UserTokenService::isLogin()) {
            return $this->register($user, $this->type);
        } else if (UserTokenService::isLogin()) { // 绑定用户
            return $this->doBind($user, $this->type);
        }
    }

    /**
     * 用户注册操作
     * @param array $info
     * @param string|null $type
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function register(array $info = [], string $type = null)
    {
        $openid = $info['openid'] ?? $info['id'];
        $nickname = $info['userData']['name'] ?? $info['userData']['nickname'];
        $userInfo = UserThird::alias('th')->view('user', '*', 'user.id=th.user_id')->where(['openid' => $openid, 'type' => $type])->find();

        if (!empty($userInfo)) {
            $array['login_time'] = time();
            $array['login_ip'] = request()->getRealIp();
            $array['login_count'] = $userInfo['login_count'] + 1;
            if (User::update($array, ['id' => $userInfo['user_id']])) {
                UserService::createUserCookies($userInfo);
                return redirect(request()->cookie('redirectUrl', '/'));
            }
        } else {

            // 注册本地用户
            $post['nickname'] = $nickname;
            $post['avatar'] = $info['userData']['avatar'];
            if (User::getByNickname($nickname)) {
                $post['nickname'] .= Random::alpha(3);
            }
            $post['group_id'] = 1;
            $post['create_ip'] = request()->getRealIp();
            $result = UserService::register($post);

            // 封装第三方数据
            if (!empty($result)) {
                $third = [
                    'type'          => $this->type,
                    'user_id'       => $result['id'],
                    'openid'        => $openid,
                    'nickname'      => $nickname,
                    'access_token'  => $info['access_token'],
                    'refresh_token' => $info['refresh_token'],
                    'expires_in'    => $info['expires_in'],
                    'login_time'    => time(),
                    'expiretime'    => time() + $info['expires_in'],
                ];

                if (UserThird::create($third)) {
                    UserService::createUserCookies($result);
                    return redirect(request()->cookie('redirectUrl', '/'));
                }
            }
        }

        return $this->error('登录失败');
    }

    /**
     * 用户绑定操作
     * @return Response
     * @throws InvalidArgumentException
     */
    public function bind(): Response
    {
        if (UserTokenService::isLogin()) {
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
     * @return Response
     * @throws DbException
     * @throws InvalidArgumentException
     */
    public function unbind(): Response
    {
        try {
            $this->oauth = $this->oType();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

        $result = UserTokenService::isLogin();
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


        return $this->error();
    }

    /**
     * 用户绑定操作实例
     * @param array $info
     * @param string|null $type
     * @return Response|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function doBind(array $info = [], string $type = null)
    {
        $openid = $info['openid'] ?? $info['id'];
        $nickname = $info['userData']['name'] ?? $info['userData']['nickname'];
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
                'access_token'  => $info['access_token'],
                'refresh_token' => $info['refresh_token'],
                'expires_in'    => $info['expires_in'],
                'login_time'    => time(),
                'expiretime'    => time() + $info['expires_in'],
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
     * @return Response
     */
    protected function redirectUrl(): Response
    {
        $referer = request()->cookie('redirectUrl', '/');

        if (preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $referer = '/';
        }

        request()->cookie('redirectUrl', null, 1);
        return $this->redirect($referer);
    }
}
