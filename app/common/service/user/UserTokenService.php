<?php

namespace app\common\service\user;

use app\common\model\system\User as UserModel;
use Psr\SimpleCache\InvalidArgumentException;
use system\Random;
use support\Cache;

/**
 * 用户token服务
 * Class UserTokenService
 */
class UserTokenService
{
    /**
     * 保活时间
     * @var int
     */
    protected static int $keepTime = 604800;

    /**
     * 类构造函数
     * class constructor.
     */
    public function __construct()
    {
        self::$keepTime = config('cookie.expire');
    }

    /**
     * 校验登录
     * @return array
     * @throws InvalidArgumentException
     */
    public static function isLogin(): array
    {
        $token = self::getToken();
        $userId = self::checkToken($token);
        if (empty($userId)) {
            return [];
        }

        $userInfo = UserModel::with('group')->where(['id' => $userId])->findOrEmpty()->toArray();
        $userInfo['has_password'] = empty($userInfo['password']) ? 0 : 1;
        unset($userInfo['password'], $userInfo['salt']);
        return $userInfo;
    }

    /**
     * 生成token
     * @access protected
     * @param int $id
     * @return string
     */
    public static function buildToken(int $id = 0): string
    {
        return md5(Random::alpha(16) . $id . time());
    }

    /**
     * 获取token
     * return string
     */
    public static function getToken(): string
    {
        $token = request()->header('Authorization') ?: request()->header('token');
        return $token ?? input('token', request()->cookie('token')) ?: 'undefined';
    }

    /**
     * 校验token
     * @access protected
     * @param string $token
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function checkToken(string $token): mixed
    {
        return Cache::get($token) ?? 0;
    }
}