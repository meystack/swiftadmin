<?php

declare(strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com>  MIT License Code
// +----------------------------------------------------------------------
namespace app\index\controller;

use app\common\library\ResultCode;
use app\common\model\system\UserLog;
use app\common\model\system\UserNotice;
use app\HomeController;
use app\common\library\Sms;
use app\common\library\Email;
use app\common\library\Upload;
use app\common\model\system\User as UserModel;
use PHPMailer\PHPMailer\Exception;
use Psr\SimpleCache\InvalidArgumentException;
use support\Request;
use support\Response;
use system\Http;
use system\Random;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Event\Event;

class User extends HomeController
{
    /**
     * 鉴权控制器
     */
    public bool $needLogin = true;

    /**
     * 超时时间
     */
    public int $expire = 604800;

    /**
     * 非登录鉴权方法
     */
    public array $noNeedAuth = ['login', 'register', 'forgot', 'check'];

    public function __construct()
    {
        parent::__construct();
        $this->model = new UserModel();
    }

    /**
     * 用户中心
     * @return mixed
     * @throws DbException
     */
    public function index(): Response
    {
        // 未读短消息
        $unread = UserNotice::where('user_id', get_user_id())->where('status', 0)->count();
        return view('/user/index', [
            'unread' => $unread,
        ]);
    }

    /**
     * 用户注册
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException
     */
    public function register(): Response
    {
        if (request()->isPost()) {

            // 获取参数
            $post = \request()->post();
            $post = request_validate_rules($post, get_class($this->model));

            if (!is_array($post)) {
                return $this->error($post);
            }

            // 手机号注册
            if (saenv('user_register') == 'mobile') {
                $mobile = input('mobile');
                $captcha = input('captcha');
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

        return view('/user/register', [
            'style' => saenv('user_register'),
        ]);
    }

    /**
     * 用户登录
     * @return Response
     */
    public function login(): Response
    {

        if (request()->isPost()) {
            $nickname = \request()->post('nickname');
            $password = \request()->post('pwd');

            $response = $this->auth->login($nickname, $password);
            if (!$response) {
                return $this->error($this->auth->getError());
            }

            $response->withBody(json_encode(ResultCode::LOGINSUCCESS));
            return $response;
        }

        return view('/user/login', [
            'referer' => request()->server('HTTP_REFERER', '/'),
        ]);
    }

    /**
     * 找回密码
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function forgot(): Response
    {
        if (request()->isPost()) {

            $email = input('email');
            $mobile = input('mobile');
            $event = input('event');
            $captcha = input('captcha');
            $pwd = input('pwd');

            if (!empty($email)) {
                if (!Email::instance()->check($email, $captcha, $event)) {
                    return $this->error(Email::instance()->getError());
                }
            } else {
                if (!Sms::instance()->check($mobile, $captcha, $event)) {
                    return $this->error(Sms::instance()->getError());
                }
            }

            $where = $email ? ['email' => $email] : ['mobile' => $mobile];
            $user = $this->model->where($where)->find();
            if (!$user) {
                return $this->error('用户不存在');
            }

            try {
                $salt = Random::alpha();
                $pwd = encryptPwd($pwd, $salt);
                $this->model->update(['id' => $user['id'], 'pwd' => $pwd, 'salt' => $salt]);
            } catch (\Exception $e) {
                return $this->error('修改密码失败，请联系管理员');
            }

            return $this->success('修改密码成功！');
        }

        return view('/user/forgot');
    }

    /**
     * 用户资料
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function center(Request $request): Response
    {
        if (request()->isPost()) {

            $nickname = input('nickname');
            $post = request_validate_rules(request()->Post(), get_class($this->model), 'nickname');
            if (!is_array($post)) {
                return $this->error($post);
            }

            if ($this->model->where('nickname', $nickname)->find()) {
                return $this->error('当前昵称已被占用，请更换！');
            }

            if ($this->model->update(['id' => get_user_id(), 'nickname' => $nickname])) {
                return $this->success('修改昵称成功！', (string)url('/user/index'));
            }

            return $this->error();
        }

        /**
         * 初始化请求新闻
         */
        $files = public_path('upload/upgrade') . DIRECTORY_SEPARATOR . 'news.html';
        if (!is_file($files)) {
            $result = Http::get(config('app.api_url') . '/news/index');
            write_file($files, $result);
        } else {
            $result = read_file($files);
            if (filemtime($files) + $this->expire <= time()) {
                @unlink($files);
            }
        }

        return view('/user/center', [
            'newsHtml'     => $result ?? '服务器错误',
            'userList'     => $this->model->order('login_count', 'desc')->limit(12)->select()->toArray(),
            'invite_count' => $this->model->where('invite_id', get_user_id())->count(),
        ]);
    }

    /**
     * 消息列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function message(): Response
    {
        if (request()->isAjax()) {

            $page = input('page', 1);
            $limit = input('limit', 1);

            $status = input('status', 'all');
            if ($status !== 'all') {
                $where[] = ['status', '=', $status];
            }

            $where[] = ['user_id', '=', get_user_id()];
            $count = UserNotice::where($where)->count();
            $page = ($count <= $limit) ? 1 : $page;
            $list = UserNotice::where($where)->order('id', 'desc')->limit((int)$limit)->page((int)$page)->select()->toArray();
            return $this->success('查询成功', "", $list, $count);
        }

        return view('/user/message');
    }

    /**
     * 查看消息
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function viewMessage(): Response
    {
        $id = input('id');
        $info = UserNotice::where('id', $id)->find();
        if (!$info) {
            return $this->error('消息不存在');
        }

        if ($info['user_id'] != get_user_id()) {
            return $this->error('非法操作');
        }

        if ($info['status'] == 0) {
            UserNotice::update(['id' => $id, 'status' => 1]);
        }

        if ($info['send_id'] > 0) {
            $fromInfo = $this->model->where('id', $info['send_id'])->find();
            $info['nickname'] = $fromInfo['nickname'];
        }

        // 更新未读
        $unread = UserNotice::where(['user_id' => get_user_id(), 'status' => 0])->count();
        return view('/user/viewMessage', [
            'info'   => $info,
            'unread' => $unread,
        ]);
    }

    /**
     * 全部删除消息
     * @return Response
     * @throws DbException
     */
    public function derMessage(): Response
    {
        if (\request()->isPost()) {
            $ids = input('id');
            $type = input('type', 'del');
            $where[] = ['id', 'in', implode(',', $ids)];
            $where[] = ['user_id', '=', get_user_id()];
            if ($type === 'del') {
                if (UserNotice::where($where)->delete()) {
                    return $this->success('删除成功');
                }
            } else {

                if (UserNotice::where($where)->update(['status' =>1])) {
                    return $this->success('操作成功');
                }
            }

            return $this->error('操作失败');
        }

        return $this->error('非法操作');
    }

    /**
     * 用户资料
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function profile(Request $request): Response
    {
        if (request()->isPost()) {
            $nickname = input('nickname');
            $post = request_validate_rules(request()->Post(), get_class($this->model), 'nickname');
            if (!is_array($post)) {
                return $this->error($post);
            }

            if ($nickname != get_user_info()['nickname']
                &&$this->model->where('nickname', $nickname)->find()) {
                return $this->error('当前昵称已被占用，请更换！');
            }

            unset($post['money']);
            unset($post['score']);
            $user = $this->model->find(get_user_id());
            if ($user->save($post)) {
                return $this->success('更新资料成功');
            }

            return $this->error();
        }

        return view('/user/profile');
    }

    /**
     * 实名认证
     * @return Response
     */
    public function certification(): Response
    {
        $userInfo = get_user_info();
        if (request()->isPost()) {
            $name = input('name');
            $mobile = input('mobile');
            $idCard = input('idCard');
            $captcha = input('captcha');

            if (!empty($userInfo['prove'])) {
                return $this->error('您已经实名认证过了！');
            }

            // 判断是否安装实名认证插件
            if (!Event::hasListener('certification')) {
                return $this->error('实名认证插件未安装');
            }

            // 判断验证码
            if (!$captcha || !$this->captchaCheck($captcha)) {
                return $this->error('验证码错误');
            }

            try {
                $result = Event::emit('certification', [
                    'name'   => $name,
                    'mobile' => $mobile,
                    'idCard' => $idCard,
                ]);

                if ($result['code'] != 1) {
                    throw new \Exception($result['msg']);
                }

                // 更新系统认证信息
                $this->model->where('id', get_user_id())->update([
                    'prove'      => 1,
                    'name'       => $name,
                    'idCard'     => $idCard,
                    'mobile'     => $mobile,
                    'prove_time' => date('Y-m-d H:i:s', time())
                ]);

            } catch (\Exception $e) {
                return $this->error('实名认证失败，请联系管理员');
            }

            return $this->success('实名认证成功！');
        }

        return view('/user/certification',['prove' => $userInfo['prove']]);
    }

    /**
     * 用户登录日志
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function login_log(): Response
    {
        if (request()->isAjax()) {

            // 获取数据
            $page = input('page', 1);
            $limit = input('limit', 1);
            $where[] = ['login_id', '=', get_user_id()];
            $count = UserLog::where($where)->count();
            $page = ($count <= $limit) ? 1 : $page;
            $list = UserLog::where($where)->order('id', 'desc')->limit((int)$limit)->page((int)$page)->select()->toArray();
            return $this->success('查询成功', "", $list, $count);

        }

        return view('/user/login_log');
    }

    /**
     * 修改密码
     * @param Request $request
     * @return Response
     */
    public function changePwd(Request $request): Response
    {
        if (request()->isPost()) {

            // 获取参数
            $pwd = input('pwd');
            $oldPwd = input('oldpwd');
            $userInfo = get_user_info();
            $yPwd = encryptPwd($oldPwd, $userInfo['salt']);

            if ($yPwd != $userInfo['pwd']) {
                return $this->error('原密码输入错误！');
            }

            $salt = Random::alpha();
            $pwd = encryptPwd($pwd, $salt);
            $result = $this->model->update(['id' => get_user_id(), 'pwd' => $pwd, 'salt' => $salt]);
            if (!empty($result)) {
                return $this->success('修改密码成功！');
            }

            return $this->error();
        }

        return view('/user/changepwd');
    }

    /**
     * 申请appKey
     * @return Response|void
     */
    public function appid(Request $request)
    {
        if (request()->isPost()) {
            $data = array();
            $data['id'] = get_user_id();
            $data['app_id'] = 10000 + get_user_id();
            $data['app_secret'] = Random::alpha(22);
            if ($this->model->update($data)) {
                return $this->success();
            }
            return $this->error();
        }
    }

    /**
     * 修改邮箱
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public function email(Request $request): Response
    {
        if (request()->isPost()) {

            $email = input('email');
            $event = input('event');
            $captcha = input('captcha');

            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->error("您输入的邮箱格式不正确！");
            }

            if (UserModel::getByEmail($email)) {
                return $this->error("您输入的邮箱已被占用！");
            }

            $Ems = Email::instance();
            if (!empty($email) && !empty($captcha)) {

                if ($Ems->check($email, $captcha, $event)) {
                    $this->model->update(['id' => get_user_id(), 'email' => $email]);
                    return $this->success('修改邮箱成功！');
                }

                return $this->error($Ems->getError());
            }

            $last = $Ems->getLast($email);
            if ($last && (time() - strtotime($last['create_time'])) < 60) {
                return $this->error(__('发送频繁'));
            }

            if ($Ems->captcha($email, $event)->send()) {
                return $this->success("邮件发送成功，请查收！");
            } else {
                return $this->error($Ems->getError());
            }
        }

        return view('/user/email');
    }

    /**
     * 修改手机号
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException|InvalidArgumentException
     */
    public function mobile(Request $request): Response
    {

        if (request()->isPost()) {

            $mobile = input('mobile');
            $event = input('event');
            $captcha = input('captcha');

            if (!is_mobile($mobile)) {
                return $this->error('手机号码不正确');
            }

            if ($mobile && UserModel::getByMobile($mobile)) {
                return $this->error("您输入的手机号已被占用！");
            }

            $Sms = Sms::instance();
            if (!empty($mobile) && !empty($captcha)) {

                if ($Sms->check($mobile, $captcha, $event)) {
                    $this->model->update(['id' => get_user_id(), 'mobile' => (int)$mobile]);
                    return $this->success('修改手机号成功！');
                }

                return $this->error($Sms->getError());
            } else {

                $data = $Sms->getLast($mobile);
                if ($data && (time() - strtotime($data['create_time'])) < 60) {
                    return $this->error(__('发送频繁'));
                }

                if ($Sms->send($mobile, $event)) {
                    return $this->success("验证码发送成功");
                } else {
                    return $this->error($Sms->getError());
                }
            }
        }

        return view('/user/mobile');
    }

    /**
     * 设置密保
     * @param Request $request
     * @return Response
     */
    public function protection(Request $request): Response
    {
        $validate = [
            '你家的宠物叫啥？',
            '你的幸运数字是？',
            '你不想上班的理由是？',
        ];

        if (request()->isPost()) {
            $question = input('question');
            $answer = input('answer');

            if (!$question || !$answer) {
                return $this->error('设置失败');
            }

            if (!in_array($question, $validate)) {
                $question = current($validate);
            }

            try {
                $userInfo = get_user_info();
                $userInfo->question = $question;
                $userInfo->answer = $answer;
                $userInfo->save();
            } catch (\Throwable $th) {
                return $this->error();
            }

            return $this->success();
        }

        return view('/user/protection', [
            'validate' => $validate
        ]);
    }

    /**
     * 安全配置中心
     * @param Request $request
     * @return Response
     */
    public function security(Request $request): Response
    {
        $maxProgress = 5;
        $thisProgress = 1;
        $userInfo = get_user_info();

        if ($userInfo->email) {
            $thisProgress++;
        }

        if ($userInfo->mobile) {
            $thisProgress++;
        }

        if ($userInfo->answer) {
            $thisProgress++;
        }

        if ($userInfo->wechat) {
            $thisProgress++;
        }

        // 计算比例
        $progress = (($thisProgress / $maxProgress) * 100);
        return view('/user/security', [
            'progress' => $progress,
        ]);
    }

    /**
     * 用户头像上传
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public function avatar(Request $request): Response
    {
        if (request()->isPost()) {
            $response = Upload::instance()->upload();
            if (!$response) {
                return $this->error(Upload::instance()->getError());
            }
            $userInfo = get_user_info();
            $userInfo->avatar = $response['url'] . '?' . Random::alpha(12);
            if ($userInfo->save()) {
                return json($response);
            }
        }

        return json(ResultCode::SUCCESS);
    }

    /**
     * 文件上传函数
     */
    public function upload(): Response
    {
        if (request()->isPost()) {
            $file = Upload::instance()->upload();
            if (!$file) {
                return $this->error(Upload::instance()->getError());
            }
            return json($file);
        }

        return json(ResultCode::SUCCESS);
    }

    /**
     * 远程下载图片
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public function getImage()
    {
        if (request()->isPost()) {
            $file = Upload::instance()->download(input('url'));
            if (!$file) {
                return $this->error(Upload::instance()->getError());
            }
            return json($file);
        }
    }

    /**
     * 退出登录
     * @return Response
     * @throws \Exception
     */
    public function logout(): Response
    {
        $this->auth->logout();
        return $this->success('退出成功', url('/'));
    }
}
