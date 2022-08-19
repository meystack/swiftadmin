<?php
declare (strict_types = 1);

namespace system\third;
use GuzzleHttp\Client;

/**
 * 微博登录类
 */

class weibo 
{

    const GET_AUTH_CODE_URL = "https://api.weibo.com/oauth2/authorize";
    const GET_ACCESS_TOKEN_URL = "https://api.weibo.com/oauth2/access_token";
    const GET_USERINFO_URL = "https://api.weibo.com/2/users/show.json";

    /**
     * 配置信息
     * @var array
     */
    private $config = [];

    /**
     * Http实例
     * @var Object
     */
    protected $http = null;

    public function __construct($options = [])
    {
        if ($config = saenv('weibo')) {
            $this->config = array_merge($this->config, $config);
        }
        $this->config = array_merge($this->config, is_array($options) ? $options : []);

        $this->http = new Client();
    }

    /**
     * 用户登录
     */
    public function login() {
        return redirect($this->getAuthorizeUrl());
    }

    /**
     * 获取登录地址
     */
    public function getAuthorizeUrl()
    {
        $state = hash('sha256',uniqid((string)mt_rand()));
        session('state', $state);
        $queryarr = array(
            "response_type" => "code",
            "client_id"     => $this->config['app_id'],
            "redirect_uri"  => $this->config['callback'],
            "state"         => $state,
        );

        request()->isMobile() && $queryarr['display'] = 'mobile';
        $url = self::GET_AUTH_CODE_URL . '?' . http_build_query($queryarr);
     
        return $url;        
    }

   /**
     * 获取用户信息
     * @param array $params
     * @return array
     */
    public function getUserInfo($params = [])
    {
        $params = $params ? $params : input();
        if (isset($params['access_token']) || (isset($params['state']) && $params['state'] == session('state') && isset($params['code']))) {
            //获取access_token
            $data = isset($params['code']) ? $this->getAccessToken($params['code']) : $params;
            $access_token = isset($data['access_token']) ? $data['access_token'] : '';
            $refresh_token = isset($data['refresh_token']) ? $data['refresh_token'] : '';
            $expires_in = isset($data['expires_in']) ? $data['expires_in'] : 0;
            if ($access_token) {
                $uid = isset($data['uid']) ? $data['uid'] : '';
                //获取用户信息
                $queryarr = [
                    "access_token" => $access_token,
                    "uid"          => $uid,
                ];
                $ret = $this->http->get(self::GET_USERINFO_URL.'?'.http_build_query($queryarr))->getBody()->getContents();
                $userinfo = (array)json_decode($ret, true);
                if (!$userinfo || isset($userinfo['error_code'])) {
                    return [];
                }
                $userinfo = $userinfo ? $userinfo : [];
                $userinfo['nickname'] = isset($userinfo['screen_name']) ? $userinfo['screen_name'] : '';
                $userinfo['avatar'] = isset($userinfo['profile_image_url']) ? $userinfo['profile_image_url'] : '';
                $userinfo['avatar'] = str_replace('http://','https://',$userinfo['avatar']);
                $data = [
                    'access_token'  => $access_token,
                    'refresh_token' => $refresh_token,
                    'expires_in'    => $expires_in,
                    'openid'        => $uid,
                    'userinfo'      => $userinfo
                ];
                return $data;
            }
        }
        return [];
    }

    /**
     * 获取access_token
     * @param string code
     * @return array
     */
    public function getAccessToken($code = '')
    {
        if (!$code) {
            return [];
        }

        $queryarr = array(
            "grant_type"    => "authorization_code",
            "client_id"     => $this->config['app_id'],
            "client_secret" => $this->config['app_key'],
            "redirect_uri"  => $this->config['callback'],
            "code"          => $code,
        );
        
        $response = $this->http->post(self::GET_ACCESS_TOKEN_URL,['query'=>$queryarr])->getBody()->getContents();
        $ret = (array)json_decode($response, true);
        return $ret ? $ret : [];
    }
}
