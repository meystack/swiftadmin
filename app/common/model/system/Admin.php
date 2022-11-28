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
namespace app\common\model\system;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\Model;
use app\common\library\ParseData;
use think\model\relation\HasOne;

/**
 * @mixin \think\Model
 */
class Admin extends \think\Model
{
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * 关联管理组
     *
     * @return HasOne
     */
    public function group(): HasOne
    {
        return $this->hasOne(AdminGroup::class, 'id', 'group_id');
    }

    /**
     * 根据用户名/密码 进行登录判断
     * @param $user
     * @param $pwd
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function checkLogin($user, $pwd)
    {
        $where[] = ['pwd', '=', encryptPwd(trim($pwd))];
        if (filter_var($user, FILTER_VALIDATE_EMAIL)) {
            $where[] = ['email', '=', htmlspecialchars(trim($user))];
        } else {
            $where[] = ['name', '=', htmlspecialchars(trim($user))];
        }

        return Admin::where($where)->find();
    }

    /**
     * 根据用户名/验证码 进行数据查找
     * @param $user
     * @param $code
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function checkForget($user, $code)
    {
        // 校验格式
        if (filter_var($user, FILTER_VALIDATE_EMAIL)) {
            $where[] = ['email', '=', $user];
        } else {
            $where[] = ['mobile', '=', $user];
        }

        $where[] = ['valicode', '=', $code];
        return Admin::where($where)->find();
    }
}
