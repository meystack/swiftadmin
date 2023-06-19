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

use app\common\exception\OperateException;
use app\common\exception\user\UserException;
use app\common\library\ResultCode;
use app\common\model\system\UserLog;
use app\common\model\system\UserNotice;
use app\common\service\user\UserService;
use app\HomeController;
use app\common\library\Upload;
use app\common\model\system\User as UserModel;
use PHPMailer\PHPMailer\Exception;
use Psr\SimpleCache\InvalidArgumentException;
use support\Request;
use support\Response;
use system\Random;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Event\Event;
use app\common\validate\system\User as UserValidate;

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
    public array $noNeedLogin = ['login', 'register', 'forgot', 'ajaxLogin', 'mobileLogin', 'scanLogin', 'scanTicket'];


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
    public function index(Request $request): Response
    {
        // 未读短消息
        $unread = UserNotice::where('user_id', $request->userId)->where('status', 0)->count();
        return view('/user/index', [
            'unread' => $unread,
        ]);
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
        return view('/user/center', [
            'newsHtml'     => $result ?? '服务器错误',
            'userList'     => $this->model->order('login_count', 'desc')->limit(12)->select()->toArray(),
            'invite_count' => $this->model->where('invite_id', $request->userId)->count(),
        ]);
    }

    /**
     * 用户资料
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws OperateException
     */
    public function profile(Request $request): Response
    {
        if (request()->isPost()) {
            $post = request()->post();
            validate(UserValidate::class)->scene('nickname')->check($post);
            UserService::editProfile($post, $request->userId);
            return $this->success('更新资料成功');
        }

        return view('/user/profile');
    }

    /**
     * 用户注册
     * @return Response
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws OperateException
     */
    public function register(): Response
    {
        if (request()->isPost()) {
            $post = request()->post();
            validate(UserValidate::class)->scene('register')->check($post);
            $response = UserService::register($post);
            return $response['response']->withBody(json_encode(ResultCode::REGISTERSUCCESS));
        }

        return view('/user/register', [
            'style' => saenv('user_register'),
        ]);
    }

    /**
     * 用户登录
     * @return Response
     * @throws InvalidArgumentException|OperateException
     */
    public function login(): Response
    {
        if (request()->isPost()) {
            $nickname = input('nickname');
            $password = input('pwd');
            validate(UserValidate::class)->scene('login')->check([
                'nickname' => $nickname,
                'pwd'      => $password
            ]);
            $result = UserService::accountLogin($nickname, $password);
            return $result['response']->withBody(json_encode(ResultCode::LOGINSUCCESS));
        }

        return view('/user/login', [
            'referer' => $this->referer,
        ]);
    }

    /**
     * 手机登录
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     * @throws OperateException
     */
    public function mobileLogin(Request $request): Response
    {
        if (request()->isPost()) {

            $mobile = input('mobile');
            $captcha = input('captcha');
            validate(UserValidate::class)->scene('mobile')->check([
                'mobile'  => $mobile,
                'captcha' => $captcha
            ]);
            $result = UserService::mobileLogin($mobile, $captcha);
            return $result['response']->withBody(json_encode(ResultCode::LOGINSUCCESS));
        }

        return $this->error('非法请求');
    }

    /**
     * ajax登录
     * @return Response
     */
    public function ajaxLogin(): Response
    {
        return view('/user/ajax_login', ['referer' => $this->referer]);
    }

    /**
     * 用户扫码登录
     * @param Request $request
     * @return Response
     */
    public function scanLogin(Request $request): Response
    {
        if (request()->isAjax()) {
            if (!Event::hasListener('scanLoginBefore')) {
                return $this->error('请安装扫码登录插件');
            }
            try {
                $result = Event::emit('scanLoginBefore', input(), true) ?? [];
                $ticket = $result['ticket'] ?? time();
                $qrcode = $result['qrcode'] ?? '/static/images/qrcode-qun.png';
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
            return $this->success('获取成功', '/', ['ticket' => $ticket, 'qrcode' => $qrcode]);
        }

        return $this->error('非法请求');
    }

    /**
     * 扫码登录
     * @param Request $request
     * @return Response
     */
    public function scanTicket(Request $request): Response
    {
        if (request()->isPost()) {
            $data = request()->post();
            try {

                $result = Event::emit('scanLoginAfter', $data, true) ?? [];
                if (!isset($result['code']) || $result['code'] != 200) {
                    throw new \Exception($result['msg'] ?? '登录异常');
                }

                $response = $result['response'];
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }

            return $response->withBody(json_encode(ResultCode::LOGINSUCCESS));
        }

        return $this->error('缺少参数');
    }

    /**
     * 修改密码
     * @param Request $request
     * @return Response
     * @throws OperateException
     */
    public function changePwd(Request $request): Response
    {
        if (request()->isPost()) {
            $post = request()->post();
            UserService::changePwd($post, $request->userId);
            return $this->success('修改密码成功！');
        }

        return view('/user/change_pwd');
    }

    /**
     * 找回密码
     * @param Request $request
     * @return Response
     * @throws DbException|OperateException
     */
    public function forgot(Request $request): Response
    {
        if (request()->isPost()) {
            $post = request()->post();
            UserService::forgotPwd($post);
            return $this->success('修改密码成功！');
        }

        return view('/user/forgot');
    }

    /**
     * 消息列表
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function message(Request $request): Response
    {
        if (request()->isAjax()) {

            $page = input('page', 1);
            $limit = input('limit', 1);
            $status = input('status', 'all');
            $where[] = ['user_id', '=', $request->userId];
            if ($status !== 'all') {
                $where[] = ['status', '=', $status];
            }

            list($list, $count) = UserService::listMessage($limit, $page, $where);
            return $this->success('查询成功', "/", $list, $count);
        }

        return view('/user/message');
    }

    /**
     * 查看消息
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function viewMessage(Request $request): Response
    {
        $id = input('id', 0);
        $result = UserService::viewMessage($id, $request->userId);
        return view('message_view', [
            'msgInfo' => $result['msgInfo'],
            'unread'  => $result['unread'],
        ]);
    }

    /**
     * 批量操作消息
     * @param Request $request
     * @return Response
     */
    public function batchMessage(Request $request): Response
    {
        if (\request()->isPost()) {
            $ids = input('id');
            $type = input('type', 'del');
            try {
                UserService::batchMessage($ids, $type, $request->userId);
            } catch (UserException $e) {
                return $this->error($e->getMessage());
            }
            return $this->success('操作成功');
        }

        return $this->error('非法操作');
    }

    /**
     * 我的邀请
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public function invite(Request $request): Response
    {
        $inviteList = $this->model->where('invite_id', $request->userId)
            ->limit(9)->field('id,nickname,url,avatar')
            ->order('id desc')
            ->select()
            ->toArray();
        return $this->view('', ['inviteList' => $inviteList]);
    }

    /**
     * 申请appKey
     * @return Response|void
     */
    public function appid(Request $request)
    {
        if (request()->isPost()) {
            $data = array();
            $data['id'] = $request->userId;
            $data['app_id'] = 10000 + $request->userId;
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
     * @throws Exception|OperateException|UserException
     */
    public function changeEmail(Request $request): Response
    {
        if (request()->isPost()) {

            $email = input('email');
            $captcha = input('captcha');
            $event = input('event');
            UserService::changeEmail($email, $captcha, $event, $request->userId);
            return $this->success('修改邮箱成功！');
        }

        return view('/user/change_email');
    }

    /**
     * 修改手机号
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws OperateException
     * @throws UserException
     */
    public function changeMobile(Request $request): Response
    {
        if (request()->isPost()) {
            $mobile = input('mobile');
            $captcha = input('captcha');
            $event = input('event');
            UserService::changeMobile($mobile, $captcha, $event, $request->userId);
            return $this->success('修改手机号成功！');
        }

        return view('/user/change_mobile');
    }

    /**
     * 用户登录日志
     * @param Request $request
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function loginLog(Request $request): Response
    {
        if (request()->isAjax()) {
            $page = input('page', 1);
            $limit = input('limit', 1);
            $where[] = ['login_id', '=', $request->userId];
            $count = UserLog::where($where)->count();
            $page = ($count <= $limit) ? 1 : $page;
            $list = UserLog::where($where)->order('id', 'desc')->limit((int)$limit)->page((int)$page)->select()->toArray();
            return $this->success('查询成功', "", $list, $count);
        }

        return view('/user/login_log');
    }

    /**
     * 实名认证
     * @param Request $request
     * @return Response
     */
    public function certification(Request $request): Response
    {

        if (request()->isPost()) {
            $name = input('name');
            $mobile = input('mobile');
            $idCard = input('idCard');
            $captcha = input('captcha');

            if (!empty($request->userInfo['prove'])) {
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
                $this->model->where('id', $request->userId)->update([
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

        return view('/user/certification', ['prove' => $request->userInfo['prove']]);
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
                $this->model->update([
                    'question' => $question,
                    'answer'   => $answer
                ], ['id' => $request->userId]);

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
        $thisProgress = 1;
        if ($request->userInfo['email']) {
            $thisProgress++;
        }

        if ($request->userInfo['mobile']) {
            $thisProgress++;
        }

        if ($request->userInfo['prove']) {
            $thisProgress++;
        }

        if ($request->userInfo['wechat']) {
            $thisProgress++;
        }

        // 计算比例
        $progress = (($thisProgress / 5) * 100);
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
            $userInfo = $request->userInfo;
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
            $response = Upload::instance()->upload();
            if (empty($response)) {
                return $this->error(Upload::instance()->getError());
            }
            return json($response);
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
    public function getImage(): Response
    {
        $url = input('url', '');
        $response = Upload::instance()->download($url);
        if (empty($response)) {
            return $this->error(Upload::instance()->getError());
        }

        return json($response);
    }
}
