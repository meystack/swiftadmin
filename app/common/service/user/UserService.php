<?php

namespace app\common\service\user;

use app\common\exception\OperateException;
use app\common\exception\user\UserException;
use app\common\model\system\User as UserModel;
use app\common\model\system\UserLog;
use app\common\model\system\UserNotice;
use app\common\service\notice\EmailService;
use app\common\service\notice\SmsService;
use PHPMailer\PHPMailer\Exception;
use Psr\SimpleCache\InvalidArgumentException;
use system\Random;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use support\Cache;
use Webman\Event\Event;

/**
 * 用户中心服务
 * @package app\common\service\system
 */
class UserService
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
     * 注册服务
     * @param array $post
     * @return array
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws OperateException
     */
    public static function register(array $post): array
    {
        if (!saenv('user_status')) {
            throw new OperateException('暂未开放注册！');
        }

        // 是否手机注册
        $regType = saenv('user_register_style');
        if ($regType == 'mobile') {
            $mobile = $post['mobile'] ?? '';
            $captcha = $post['captcha'] ?? '';
            if (!SmsService::checkCaptcha($mobile, $captcha, 'register')) {
                throw new OperateException('验证码错误');
            }
        }

        // 禁止批量注册
        $where[] = ['create_ip', '=', request()->getRealIp()];
        $where[] = ['create_time', '>', linux_time(1)];
        $totalMax = UserModel::where($where)->count();
        if ($totalMax >= saenv('user_register_second')) {
            throw new OperateException('禁止批量注册');
        }

        try {
            // 加密盐值
            $salt = Random::alpha();
            $userInfo = [
                'nickname'  => $post['nickname'],
                'email'     => $post['email'] ?? '',
                'mobile'    => $post['mobile'] ?? '',
                'salt'      => $salt,
                'pwd'       => encryptPwd($post['pwd'], $salt),
                'invite_id' => input('inviter', request()->cookie('inviter')),
            ];
            $userInfo = self::createUser($userInfo);
        } catch (\Throwable $e) {
            throw new OperateException($e->getMessage());
        }

        return self::createUserCookies($userInfo);
    }

    /**
     * 登录服务
     * @param string $nickname
     * @param string $pwd
     * @return array
     * @throws InvalidArgumentException|OperateException
     */
    public static function accountLogin(string $nickname, string $pwd): array
    {
        // 普通登录支持邮箱和用户名登录
        if (filter_var($nickname, FILTER_VALIDATE_EMAIL)) {
            $where[] = ['email', '=', htmlspecialchars(trim($nickname))];
        } else {
            $where[] = ['nickname', '=', htmlspecialchars(trim($nickname))];
        }

        $userInfo = UserModel::where($where)->findOrEmpty()->toArray();
        if (empty($userInfo)) {
            throw new OperateException('用户名或密码错误');
        }

        $uPwd = encryptPwd($pwd, $userInfo['salt']);
        if ($userInfo['pwd'] != $uPwd) {
            $errorMsg = '用户名或密码错误';
            UserLog::write($errorMsg, $userInfo['nickname'], $userInfo['id']);
            throw new OperateException($errorMsg);
        }

        if (!$userInfo['status']) {
            $errorMsg = '用户禁用或未审核，请联系管理员';
            UserLog::write($errorMsg, $userInfo['nickname'], $userInfo['id']);
            throw new OperateException($errorMsg);
        }

        return self::createUserCookies($userInfo);
    }

    /**
     * 手机登录
     * @param string $mobile
     * @param string $captcha
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException|OperateException
     */
    public static function mobileLogin(string $mobile, string $captcha): array
    {
        if (!SmsService::checkCaptcha($mobile, $captcha, 'login')) {
            throw new OperateException('验证码错误');
        }

        $userInfo = UserModel::where(['mobile' => $mobile])->findOrEmpty()->toArray();
        if (empty($userInfo)) {
            $maxId = UserModel::max('id');
            $userInfo = [
                'nickname' => 'u' . ($maxId + 1),
                'mobile'   => $mobile,
            ];
            return self::createUser($userInfo);
        } else if (!$userInfo['status']) {
            $errorMsg = '用户禁用或未审核，请联系管理员';
            UserLog::write($errorMsg, $userInfo['nickname'], $userInfo['id']);
            throw new OperateException($errorMsg);
        }
        self::updateUser($userInfo);
        return self::createUserCookies($userInfo);
    }

    /**
     * 创建用户
     * @param array $userInfo
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException|OperateException
     */
    public static function createUser(array $userInfo): array
    {
        if (isset($userInfo['nickname']) && UserModel::getByNickname($userInfo['nickname'])) {
            throw new OperateException('当前用户名已被占用！');
        }

        if (isset($userInfo['email']) && UserModel::getByEmail($userInfo['email'])) {
            throw new OperateException('当前邮箱已被占用！');
        }

        if (isset($userInfo['mobile']) && UserModel::getByMobile($userInfo['mobile'])) {
            throw new OperateException('当前手机号已被占用！');
        }

        if (isset($userInfo['pwd']) && $userInfo['pwd']) {
            $userInfo['salt'] = Random::alpha();
            $userInfo['pwd'] = encryptPwd($userInfo['pwd'], $userInfo['salt']);
        }

        $userInfo['login_time'] = time();
        $userInfo['login_ip'] = request()->getRealIp();
        $userInfo['create_ip'] = request()->getRealIp();

        try {
            $userInfo = UserModel::create($userInfo)->toArray();
        } catch (\Throwable $th) {
            throw new OperateException($th->getMessage());
        }

        return $userInfo;
    }

    /**
     *
     * 返回前端令牌
     * @param array $userInfo
     * @return array
     */
    public static function createUserCookies(array $userInfo = []): array
    {
        $userToken = UserTokenService::buildToken($userInfo['id']);
        $response = response()
            ->cookie('uid', $userInfo['id'], self::$keepTime, '/')
            ->cookie('token', $userToken, self::$keepTime, '/')
            ->cookie('nickname', $userInfo['nickname'], self::$keepTime, '/');
        Cache::set($userToken, $userInfo['id'], self::$keepTime);
        Cache::set('user_info_' . $userInfo['id'], $userInfo, self::$keepTime);
        \Webman\Event\Event::emit("userLoginSuccess", $userInfo);
        return ['token' => $userToken, 'id' => $userInfo['id'], 'response' => $response];
    }

    /**
     * 更新用户信息
     * @param array $userInfo
     */
    public static function updateUser(array $userInfo = []): void
    {
        $data['login_time'] = time();
        $data['login_ip'] = request()->getRealIp();
        $data['login_count'] = $userInfo['login_count'] + 1;
        $data['update_time'] = time();
        UserModel::update($data, ['id' => $userInfo['id']]);
        UserLog::write('登录成功', $userInfo['nickname'], $userInfo['id'], 1);
        Event::emit('userLoginSuccess', $userInfo);
    }

    /**
     * 退出登录
     * @return void
     */
    public static function logout(): void
    {
        response()->cookie('uid', null);
        response()->cookie('token', null);
        response()->cookie('nickname', null);
        Cache::delete(UserTokenService::getToken());
    }

    /**
     * @param array $params
     * @param int $userId
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws OperateException
     */
    public static function editProfile(array $params = [], int $userId = 0): bool
    {
        $userInfo = UserModel::where('id', $userId)->findOrEmpty()->toArray();
        if (empty($userInfo)) {
            throw new OperateException('用户不存在');
        }

        $data = [];
        if (isset($params['nickname']) && $params['nickname']) {
            // 验证昵称是否存在
            $data['nickname'] = $params['nickname'];
            if ($data['nickname'] != $userInfo['nickname']
                && UserModel::where('nickname', $data['nickname'])->find()) {
                throw new OperateException('昵称已存在');
            }
        }

        $fields = ['avatar', 'name', 'wechat', 'qq', 'idCard', 'address', 'gender'];
        foreach ($fields as $field) {
            if (isset($params[$field])) {
                $data[$field] = $params[$field];
            }
        }

        try {
            UserModel::update($data, ['id' => $userId]);
        } catch (\Exception $e) {
            throw new OperateException($e->getMessage());
        }

        return true;
    }

    /**
     * 忘记密码
     * @param array $params
     * @return bool
     * @throws DataNotFoundException|DbException|ModelNotFoundException|OperateException
     */
    public static function forgotPwd(array $params = []): bool
    {
        $value = $params['name'] ?? '';
        $pwd = $params['pwd'] ?? '';
        $captcha = $params['captcha'] ?? '';
        $filterVar = filter_var($value, FILTER_VALIDATE_EMAIL);

        // 获取验证服务类
        $checkClass = $filterVar ? EmailService::class : SmsService::class;
        if (!$checkClass::checkCaptcha($value, $captcha, 'forgot')) {
            throw new OperateException('无效的验证码');
        }

        $where = [$filterVar ? 'email' : 'mobile' => $value];
        $userInfo = UserModel::where($where)->findOrEmpty()->toArray();
        if (empty($userInfo)) {
            throw new OperateException('用户不存在');
        }

        try {
            $salt = Random::alpha();
            $pwd = encryptPwd($pwd, $salt);
            UserModel::update(['id' => $userInfo['id'], 'pwd' => $pwd, 'salt' => $salt]);
        } catch (\Exception $e) {
            throw new OperateException($e->getMessage());
        }

        return true;
    }

    /**
     * 修改密码
     * @param array $params
     * @param int $userId
     * @return bool
     * @throws OperateException
     */
    public static function changePwd(array $params = [], int $userId = 0): bool
    {
        $pwd = $params['pwd'] ?? '';
        $oldPwd = $params['oldpwd'] ?? '';
        $userInfo = UserModel::where('id', $userId)->findOrEmpty()->toArray();
        if (empty($userInfo)) {
            throw new OperateException('用户不存在');
        }

        $yPwd = encryptPwd($oldPwd, $userInfo['salt']);
        if (!empty($userInfo['pwd']) && $yPwd != $userInfo['pwd']) {
            throw new OperateException('原密码错误');
        }

        $salt = Random::alpha();
        $pwd = encryptPwd($pwd, $salt);
        try {
            UserModel::update([
                'id'   => $userId,
                'pwd'  => $pwd,
                'salt' => $salt,
            ]);
        } catch (\Exception $e) {
            throw new OperateException($e->getMessage());
        }

        return true;
    }

    /**
     * 修改手机号
     * @param $email
     * @param $captcha
     * @param $event
     * @param $userId
     * @return bool
     * @throws Exception
     * @throws UserException|OperateException
     */
    public static function changeEmail($email, $captcha, $event, $userId): bool
    {
        if (!EmailService::filterEmail($email)) {
            throw new OperateException("邮箱格式不正确");
        }

        if ($email && UserModel::getByEmail($email)) {
            throw new OperateException("您输入的邮箱已被占用");
        }

        try {
            EmailService::checkCaptcha($email, $captcha, $event);
            UserModel::update(['id' => $userId, 'email' => $email]);
        } catch (\Throwable $e) {
            throw new OperateException($e->getMessage());
        }

        return true;
    }

    /**
     * 修改手机号
     * @param $mobile
     * @param $captcha
     * @param $event
     * @param $userId
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws UserException|OperateException
     */
    public static function changeMobile($mobile, $captcha, $event, $userId): bool
    {
        if (!SmsService::filterMobile($mobile)) {
            throw new OperateException("手机号码格式不正确");
        }

        if ($mobile && UserModel::getByMobile($mobile)) {
            throw new OperateException("您输入的手机号已被占用");
        }
        try {
            if (!SmsService::checkCaptcha($mobile, $captcha, $event)) {
                throw new UserException('无效的验证码');
            }
            UserModel::update(['id' => $userId, 'mobile' => (int)$mobile]);
        } catch (\Throwable $e) {
            throw new OperateException($e->getMessage());
        }

        return true;
    }

    /**
     * 获取消息列表
     * @param int $limit
     * @param int $page
     * @param array $where
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function listMessage(mixed $limit = 10, mixed $page = 1, array $where = []): array
    {
        $count = (new UserNotice)->where($where)->count();
        $page = ($count <= $limit) ? 1 : $page;
        $list = (new UserNotice)->where($where)->order('id', 'desc')->limit($limit)->page($page)->select()->toArray();
        return [$list, $count];
    }

    /**
     * 读取消息
     * @param $id
     * @param int $userId
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException|OperateException
     */
    public static function viewMessage($id, int $userId = 0): array
    {
        $where[] = ['id', '=', $id];
        $where[] = ['user_id', '=', $userId];
        $msgInfo = UserNotice::where($where)->findOrEmpty()->toArray();
        if (empty($msgInfo)) {
            throw new OperateException('消息不存在');
        }

        if ($msgInfo['status'] == 0) {
            UserNotice::update(['id' => $id, 'status' => 1]);
        }

        if ($msgInfo['send_id']) {
            $fromInfo = UserModel::where('id', $msgInfo['send_id'])->findOrEmpty()->toArray();
            $msgInfo['nickname'] = $fromInfo['nickname'] ?? 'Unknown';
        }

        $unread = UserNotice::where(['user_id' => $userId, 'status' => 0])->count();
        return ['msgInfo' => $msgInfo, 'unread' => $unread];
    }

    /**
     * @param $ids
     * @param string $type
     * @param int $userId
     * @return bool
     * @throws UserException
     */
    public static function batchMessage($ids, string $type = 'del', int $userId = 0): bool
    {
        $where[] = ['user_id', '=', $userId];
        $where[] = ['id', 'in', implode(',', $ids)];
        try {
            $type == 'del' ? UserNotice::where($where)->delete() : UserNotice::where($where)->update(['status' => 1]);
        } catch (\Exception $e) {
            throw new UserException($e->getMessage());
        }
        return true;
    }
}