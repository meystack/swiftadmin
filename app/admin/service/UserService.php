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

use app\common\exception\OperateException;
use app\common\model\system\User;
use app\common\model\system\UserGroup;
use system\IpLocation;
use system\Random;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;

class UserService
{
    /**
     *
     * @param array $params
     * @param array $conditions
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function dataList(array $params = [], array $conditions = []): array
    {
        $page = (int)$params['page'] ?: 1;
        $limit = (int)$params['limit'] ?: 10;
        if (!empty($params['status'])) {
            $where[] = ['status', '=', $params['status'] == 1 ? 0 : 1];
        }
        if (!empty($params['nickname'])) {
            $where[] = ['nickname', 'like', '%' . $params['nickname'] . '%'];
        }

        if (!empty($params['group_id'])) {
            $where[] = ['group_id', 'find in set', $params['group_id']];
        }
        $conditions = array_merge($conditions, $where ?? []);

        $model = new User();
        $count = $model->where($conditions)->count();
        $page = ($count <= $limit) ? 1 : $page;
        $list = $model->where($conditions)->order("id asc")->limit($limit)->page($page)->select();
        // 循环处理数据
        $userGroup = (new UserGroup)->select()->toArray();
        $qqWry = new IpLocation();
        foreach ($list as $key => $value) {
            $value->hidden(['pwd', 'salt']);
            $loginIp = $value['login_ip'];
            if (!empty($loginIp)) {
                $region = $qqWry->getLocation($loginIp);
                $list[$key]['region'] = $region['country'] . ' ' . $region['area'];
            } else {
                $list[$key]['region'] = 'unKnown';
            }
            $result = list_search($userGroup,['id'=> $value['group_id']]);
            if (!empty($result)) {
                $list[$key]['group'] = $result['title'];
            }
        }

        return [$count, $list];
    }

    /**
     * @param array $params
     * @return bool
     * @throws OperateException
     */
    public static function add(array $params): bool
    {
        $model = new User();
        $whereName[] = ['nickname','=',$params['nickname']];
        $whereEmail[] = ['email','=',$params['email']];
        $data = $model->whereOr([$whereName,$whereEmail])->findOrEmpty()->toArray();
        if(!empty($data)) {
            throw new OperateException('该用户ID或邮箱已经存在！');
        }
        // 密码加密
        $params['salt'] = Random::alpha();
        $params['pwd'] = encryptPwd($params['pwd'],$params['salt']);

        Db::startTrans();
        try {
            $model->create($params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new OperateException('添加失败！');
        }

        return true;
    }

    /**
     * @param array $params
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws OperateException
     */
    public static function edit(array $params): bool
    {
        $model = new User();
        $data = $model->where('id',  $params['id'])->findOrEmpty()->toArray();
        if ($data['nickname'] != $params['nickname']) {
            $whereName[] = ['nickname','=',$params['nickname']];
            if($model->where($whereName)->find()) {
                throw new OperateException('用户ID已经存在！');
            }
        }

        if ($data['email'] != $params['email']) {
            $whereEmail[] = ['email','=',$params['email']];
            if($model->where($whereEmail)->find()) {
                throw new OperateException('用户邮箱已经存在！');
            }
        }

        if (!empty($params['pwd'])) {
            $salt = Random::alpha();
            $params['salt'] = $salt;
            $params['pwd'] = encryptPwd($params['pwd'],$params['salt']);
        } else {
            unset($params['pwd']);
        }

        Db::startTrans();
        try {
            $model->update($params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new OperateException('添加失败！');
        }

        return true;
    }

}