<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace support;

use system\Random;

/**
 * Class Request
 * @package support
 */

class Request extends \Webman\Http\Request
{
    /**
     * 用户ID
     * @var int
     */
    public $userId = 0;

    /**
     * 用户信息
     */
    public $userInfo = [];

    /**
     * 管理员ID
     * @var int
     */
    public $adminId = 0;

    /**
     * 管理员信息
     * @return string
     */
    public $adminInfo = [];

    /**
     * 生成请求令牌
     * @access public
     * @param string $name 令牌名称
     * @param mixed $type 令牌生成方法
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function buildToken(string $name = '__token__', $type = 'md5'): string
    {
        $type = is_callable($type) ? $type : 'md5';
        $token = call_user_func($type, Random::alpha(32));
        request()->session()->set($name, $token);
        return $token;
    }

    /**
     * 检查请求令牌
     * @access public
     * @param string $token 令牌名称
     * @param array $data 表单数据
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function checkToken(string $token = '__token__', array $data = []): bool
    {
        if (in_array($this->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        if (!request()->session()->has($token)) {
            // 令牌数据无效
            return false;
        }

        // Header验证
        if (request()->header('X-CSRF-TOKEN') && request()->session()->get($token) === request()->header('X-CSRF-TOKEN')) {
            // 防止重复提交
            request()->session()->delete($token); // 验证完成销毁session
            return true;
        }

        if (empty($data)) {
            $data = request()->all();
        }

        // 令牌验证
        if (isset($data[$token]) && request()->session()->get($token) === $data[$token]) {
            // 防止重复提交
            request()->session()->delete($token); // 验证完成销毁session
            return true;
        }
        // 开启TOKEN重置
        request()->session()->delete($token);
        return false;
    }

    /**
     * 获取token
     * @return array|string|null
     */
    public function getToken($token = '__token__')
    {
        return request()->header($token, input($token, request()->cookie($token)));
    }

    /**
     * 是否为GET请求
     * @access public
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->method() == 'GET';
    }

    /**
     * 是否为POST请求
     * @access public
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method() == 'POST';
    }

    /**
     * 是否为PUT请求
     * @access public
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->method() == 'PUT';
    }

    public function server($name = null)
    {
        return $name ? ($_SERVER[$name] ?? null) : $_SERVER;
    }

    /**
     * 获取应用
     * @return string
     */
    public function getApp(): string
    {
        return '/' . request()->app;
    }

    /**
     * 获取控制器
     * @return string|string[]|null
     */
    public function getController(bool $lower = false)
    {
        $controller = str_replace(["app\\$this->app\\controller\\", '\\'], ['', '/'], request()->controller);
        return $lower ? strtolower($controller) : $controller;
    }

    /**
     * 获取控制器方法
     * @return string
     */
    public function getAction(): string
    {
        return request()->action ?? 'null';
    }

    /**
     * 检测是否使用手机访问
     * @access public
     * @return bool
     */
    public function isMobile(): bool
    {
        if (request()->header('HTTP_VIA') && stristr(request()->header('HTTP_VIA'), "wap")) {
            return true;
        }

        if (request()->header('accept') && strpos(strtoupper(request()->header('accept')), "VND.WAP.WML")) {
            return true;
        }

        if (request()->header('HTTP_X_WAP_PROFILE') || request()->header('HTTP_PROFILE')) {
            return true;
        }

        if (request()->header('user-agent') && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', request()->header('user-agent'))) {
            return true;
        }

        return false;
    }

    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    public function isSsl(): bool
    {
        if ($this->server('HTTPS') && ('1' == $this->server('HTTPS') || 'on' == strtolower($this->server('HTTPS')))) {
            return true;
        } elseif ('https' == $this->server('REQUEST_SCHEME')) {
            return true;
        } elseif ('443' == $this->server('SERVER_PORT')) {
            return true;
        } elseif ('https' == $this->server('HTTP_X_FORWARDED_PROTO')) {
            return true;
        }

        return false;
    }

    /**
     * 当前URL地址中的scheme参数
     * @access public
     * @return string
     */
    public function scheme(): string
    {
        return $this->isSsl() ? 'https' : 'http';
    }

    /**
     * 获取当前包含协议的域名
     * @access public
     * @param  bool $port 是否需要去除端口号
     * @return string
     */
    public function domain(bool $port = false): string
    {
        return $this->scheme() . '://' . $this->host($port);
    }

    /**
     * 获取当前根域名
     * @access public
     * @return string
     */
    public function rootDomain(): string
    {
        $item = explode('.', request()->host());
        $count = count($item);
        return $count > 1 ? $item[$count - 2] . '.' . $item[$count - 1] : $item[0];
    }

    /**
     * 获取当前子域名
     * @access public
     * @return string
     */
    public function subDomain(): string
    {
        $rootDomain = \request()->rootDomain();
        if ($rootDomain) {
            $sub = stristr(\request()->host(), $rootDomain, true);
            $subDomain = $sub ? rtrim($sub, '.') : '';
        } else {
            $subDomain = '';
        }
        return $subDomain;
    }

}