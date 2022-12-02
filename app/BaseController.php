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
namespace app;

use support\Response;
use think\helper\Str;
use think\Validate;
use Webman\Http\Request;
use Gregwar\Captcha\CaptchaBuilder;

class BaseController
{
    /**
     * 数据库实例
     * @var object
     */
    public object $model;

    /**
     * 是否验证
     * @var bool
     */
    public bool $isValidate = true;

    /**
     * 验证场景
     * @var string
     */
    public string $scene = '';

    /**
     * 是否批量验证
     * @var bool
     */
    protected bool $batchValidate = false;

    /**
     * 获取访问来源
     * @var string
     */
    public mixed $referer;

    public function __construct()
    {
        $this->referer = \request()->header('referer');
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param $validate
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return bool
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false): bool
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = str_contains($validate, '\\') ? $validate : $this->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch();
        }

        return $v->failException()->check($data);
    }

    /**
     * 解析应用类的类名
     * @access public
     * @param string $layer 层名 controller model ...
     * @param string $name 类名
     * @return string
     */
    protected function parseClass(string $layer, string $name): string
    {
        $name = str_replace(['/', '.'], '\\', $name);
        $array = explode('\\', $name);
        $class = Str::studly(array_pop($array));
        $path = $array ? implode('\\', $array) . '\\' : '';
        return 'app' . '\\' . $layer . '\\' . $path . $class;
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param null $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param int $count
     * @param int $code
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     * @return Response
     */
    protected function success(mixed $msg = '', $url = null, mixed $data = '', int $count = 0, int $code = 200, int $wait = 3, array $header = []): Response
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        }

        $msg = !empty($msg) ? __($msg) : __('操作成功！');
        $result = [
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data,
            'count' => $count,
            'url'   => (string)$url,
            'wait'  => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html') {
            return view(config('app.dispatch_success'), $result);
        }

        return json($result);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param null $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param int $code
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     * @return Response
     */
    protected function error(mixed $msg = '', $url = null, mixed $data = '', int $code = 101, int $wait = 3, array $header = []): Response
    {
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        }

        $msg = !empty($msg) ? __($msg) : __('操作失败！');
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => (string)$url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html') {
            return view(config('app.dispatch_error'), $result);
        }

        return json($result);
    }


    /**
     * URL重定向
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param integer $code http code
     * @param array $headers
     * @return Response
     */
    protected function redirect(string $url, int $code = 302, array $headers = []): Response
    {
        return redirect($url, $code, $headers);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType(): string
    {
        return request()->isAjax() || request()->acceptJson() ? 'json' : 'html';
    }

    /**
     * 返回错误信息
     * @param string $msg
     * @param int $code
     * @return Response
     */
    protected function retResponseError(string $msg = '404 not found', int $code = 404): Response
    {
        if (\request()->expectsJson()) {
            return json(['code' => 404, 'msg' => $msg]);
        }
        return response(request_error(), $code);
    }

    /**
     * 获取模型字段集
     * @param null $model
     */
    protected function getTableFields($model = null)
    {
        $model = $model ?: $this->model;
        $tableFields = $model->getTableFields();
        if (!empty($tableFields) && is_array($tableFields)) {
            foreach ($tableFields as $key => $value) {
                $filter = ['update_time', 'create_time', 'delete_time'];
                if (!in_array($value, $filter)) {
                    $tableFields[$value] = '';
                }

                unset($tableFields[$key]);
            }
        }

        return $tableFields;
    }

    /**
     * 输出验证码图像
     * @param Request $request
     * @return Response
     */
    public function captcha(Request $request): Response
    {
        $builder = new CaptchaBuilder;
        $builder->build();
        $request->session()->set('captcha', strtolower($builder->getPhrase()));
        $img_content = $builder->get();
        return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }

    /**
     * 检查验证码
     * @param string $text
     * @return bool
     */
    protected function captchaCheck(string $text): bool
    {
        $captcha = $text ?? \request()->post('captcha');
        if (strtolower($captcha) !== \request()->session()->get('captcha')) {
            return false;
        }

        return true;
    }
}