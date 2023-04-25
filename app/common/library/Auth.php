<?php

declare(strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------
namespace app\common\library;

use app\common\model\system\UserLog;
use Psr\SimpleCache\InvalidArgumentException;
use system\Random;
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use app\common\model\system\User as UserModel;
use Webman\Event\Event;


class Auth
{
    /**
     * token令牌
     * @var string
     */
    public string $token;

    /**
     * 用户ID
     */
    public mixed $user_id = 0;

    /**
     * 用户数据
     * @var object|array
     */
    public mixed $userInfo;

    /**
     * 保活时间
     * @var int
     */
    protected int $keepTime = 604800;

    /**
     * 错误信息
     * @var string
     */
    protected string $_error = '';

    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * 类构造函数
     * class constructor.
     */
    public function __construct($config = [])
    {
        $this->keepTime = config('session.cookie_lifetime');
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return object
     */
    public static function instance(array $options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        // 返回实例
        return self::$instance;
    }

    /**
     * 用户注册
     * @param array $post
     * @return false|Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public function register(array $post)
    {
        if (!saenv('user_status')) {
            $this->setError('暂未开放注册！');
            return false;
        }

        // 禁止批量注册
        $where[] = ['create_ip', '=', request()->getRealIp()];
        $where[] = ['create_time', '>', linux_time(1)];
        $totalMax = UserModel::where($where)->count();

        if ($totalMax >= saenv('user_register_second')) {
            $this->setError('当日注册量已达到上限');
            return false;
        }

        // 过滤用户信息
        if (isset($post['nickname']) && UserModel::getByNickname($post['nickname'])) {
            $this->setError('当前用户名已被占用！');
            return false;
        }

        if (isset($post['email']) && UserModel::getByEmail($post['email'])) {
            $this->setError('当前邮箱已被占用！');
            return false;
        }

        if (isset($post['mobile']) && UserModel::getByMobile($post['mobile'])) {
            $this->setError('当前手机号已被占用！');
            return false;
        }

        try {
            /**
             * 是否存在邀请注册
             */
            $post['invite_id'] = $this->getToken('inviter');
            if (isset($post['pwd']) && $post['pwd']) {
                $post['salt'] = Random::alpha();
                $post['pwd'] = encryptPwd($post['pwd'], $post['salt']);
            }

            $user = UserModel::create($post);
        } catch (\Throwable $th) {
            $this->setError($th->getMessage());
            return false;
        }

        return $this->responseToken($user);
    }

    /**
     * 用户检测登录
     * @param string $nickname
     * @param string $pwd
     * @return false|Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public function login(string $nickname = '', string $pwd = '')
    {
        // 支持邮箱或手机登录
        if (filter_var($nickname, FILTER_VALIDATE_EMAIL)) {
            $where[] = ['email', '=', htmlspecialchars(trim($nickname))];
        } else {
            $where[] = ['mobile', '=', htmlspecialchars(trim($nickname))];
        }

        $user = UserModel::where($where)->find();

        if (!empty($user)) {

            $uPwd = encryptPwd($pwd, $user['salt']);
            if ($user['pwd'] !== $uPwd) {

                $this->setError('用户名或密码错误');
                UserLog::write($this->getError(), $user['nickname'], $user['id']);
                return false;
            }

            if (!$user['status']) {
                $this->setError('用户异常或未审核，请联系管理员');
                UserLog::write($this->getError(), $user['nickname'], $user['id']);
                return false;
            }

            // 更新登录数据
            $update = [
                'id'          => $user['id'],
                'login_time'  => time(),
                'login_ip'    => request()->getRealIp(),
                'login_count' => $user['login_count'] + 1,
            ];

            if (UserModel::update($update)) {
                Event::emit('userLoginSuccess', $user);
                UserLog::write('登录成功', $user['nickname'], $user['id'], 1);
                return $this->responseToken($user);
            }
        }

        $this->setError('您登录的用户不存在');
        return false;
    }

    /**
     * 验证是否登录
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException|InvalidArgumentException
     */
    public function isLogin(): bool
    {
        $token = $this->getToken();
        if (!$token) {
            return false;
        }

        // 验证token
        $user = $this->checkToken($token);
        if (isset($user['id'])) {
            $this->userInfo = UserModel::with('group')->find($user['id']);
            if (!empty($this->userInfo)) {
                $this->token = $token;
                $this->user_id = $user['id'];
                $this->refreshUserInfo($token, $this->userInfo);
                return true;
            }
        }

        return false;
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo()
    {
        $token = $this->getToken();
        if (!$token) {
            return false;
        }

        // 获取用户信息
        return $this->checkToken($token);
    }

    /**
     *
     * 返回前端令牌
     * @param $user
     * @param bool $token
     * @return Response
     * @throws InvalidArgumentException
     */
    public function responseToken($user, bool $token = false): Response
    {
        $this->token = $token ? $this->getToken() : $this->buildToken($user['id']);
        $response = response();
        $response->cookie('uid', $user['id'], $this->keepTime, '/');
        $response->cookie('token', $this->token, $this->keepTime, '/');
        $response->cookie('nickname', $user['nickname'], $this->keepTime, '/');
        $this->refreshUserInfo($this->token, $user);
        // 执行登录成功事件
        Event::emit("userLoginSuccess", $user);
        return $response;
    }

    /**
     * 刷新用户信息
     * @param $token
     * @param $user
     * @return void
     * @throws InvalidArgumentException
     */
    private function refreshUserInfo($token, $user): void
    {
        Cache::set($token, $user, $this->keepTime);
    }

    /**
     * 生成token
     * @access protected
     * @param $id
     * @return string
     */
    protected function buildToken($id): string
    {
        return md5(Random::alpha(16) . $id);
    }

    /**
     * 获取token
     */
    public function getToken($token = 'token')
    {
        return request()->header($token, input($token, request()->cookie($token)));
    }

    /**
     * 校验token
     */
    public function checkToken($token)
    {
        return Cache::get($token);
    }

    /**
     * 退出登录
     * @return void
     * @throws InvalidArgumentException
     */
    public function logout()
    {
        Cache::delete($this->token);
    }

    /**
     * 获取最后产生的错误
     * @return string
     */
    public function getError(): string
    {
        return $this->_error;
    }

    /**
     * 设置错误
     * @param string $error 信息信息
     * @return void
     */
    protected function setError(string $error): void
    {
        $this->_error = $error;
    }
}
