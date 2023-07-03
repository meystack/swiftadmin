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
namespace app\admin\service;

use app\admin\enums\AdminEnum;
use app\common\exception\OperateException;
use app\common\library\ResultCode;
use app\common\model\system\Admin;
use app\common\model\system\AdminLog;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Event\Event;

class LoginService
{

    /**
     * 管理员登录
     * @param string $name
     * @param string $pwd
     * @param string $captcha
     * @param array $adminInfo
     * @return bool
     * @throws OperateException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function accountLogin(string $name, string $pwd, string $captcha = '', array $adminInfo = []): bool
    {
        $countLimit = isset($adminInfo['count']) && $adminInfo['count'] >= 5;
        $minuteLimit = isset($adminInfo['time']) && $adminInfo['time'] >= strtotime('- 5 minutes');
        if ($countLimit && $minuteLimit) {
            throw new OperateException('错误次数过多，请稍后再试！');
        }

        // 验证码
        if (isset($adminInfo['isCaptcha']) && !self::captchaCheck($captcha)) {
            throw new OperateException('验证码错误！');
        }

        $result = Admin::checkLogin($name, $pwd);
        if (empty($result)) {
            $adminInfo['time'] = time();
            $adminInfo['isCaptcha'] = true;
            $adminInfo['count'] = isset($adminInfo['count']) ? $adminInfo['count'] + 1 : 1;
            request()->session()->set(AdminEnum::ADMIN_SESSION, $adminInfo);
            Event::emit(AdminEnum::ADMIN_LOGIN_ERROR, request()->all());
            self::writeAdminLogs($name, ResultCode::USPWDERROR['msg']);
            throw new OperateException(ResultCode::USPWDERROR['msg'], ResultCode::USPWDERROR['code']);
        }

        if ($result['status'] !== 1) {
            throw new OperateException(ResultCode::STATUSEXCEPTION['msg'], ResultCode::STATUSEXCEPTION['code']);
        }

        try {
            $data['login_ip'] = request()->getRealIp();
            $data['login_time'] = time();
            $data['count'] = $result['count'] + 1;
            Admin::update($data, ['id' => $result['id']]);
            $adminInfo = array_merge($adminInfo, $result->toArray());
            request()->session()->set(AdminEnum::ADMIN_SESSION, $adminInfo);
            self::writeAdminLogs($name, ResultCode::LOGINSUCCESS['msg'], 1);
            Event::emit(AdminEnum::ADMIN_LOGIN_SUCCESS, $adminInfo);
        } catch (\Throwable $th) {
            throw new OperateException($th->getMessage());
        }

        return true;
    }

    /**
     * 检查验证码
     * @param string $text
     * @return bool
     */
    protected static function captchaCheck(string $text): bool
    {
        $captcha = $text ?? \request()->post('captcha');
        if (strtolower($captcha) !== request()->session()->get('captcha')) {
            return false;
        }

        return true;
    }

    /**
     * 记录登录日志
     * @param string $name
     * @param string $error
     * @param int $status
     * @return void
     */
    public static function writeAdminLogs(string $name, string $error, int $status = 0): void
    {
        $userAgent = request()->header('user-agent');
        $nickname = (new Admin)->where('name', $name)->value('nickname');
        preg_match('/.*?\((.*?)\).*?/', $userAgent, $matches);
        $user_os = isset($matches[1]) ? substr($matches[1], 0, strpos($matches[1], ';')) : 'unknown';
        $user_browser = preg_replace('/[^(]+\((.*?)[^)]+\) .*?/', '$1', $userAgent);
        $data['name'] = $name;
        $data['nickname'] = $nickname ?? 'unknown';
        $data['user_ip'] = request()->getRealIp();
        $data['user_agent'] = $userAgent;
        $data['user_os'] = $user_os;
        $data['user_browser'] = $user_browser;
        $data['error'] = $error;
        $data['status'] = $status;
        AdminLog::create($data);
    }
}