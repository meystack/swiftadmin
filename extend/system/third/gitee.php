<?php
declare (strict_types = 1);

namespace system\third;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Gitee登录类
 */

class gitee 
{

    const GET_AUTH_CODE_URL = "https://gitee.com/oauth/authorize";
    const GET_ACCESS_TOKEN_URL = "https://gitee.com/oauth/token";
    const GET_USERINFO_URL = "https://gitee.com/api/v5/user";

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
        if ($config = saenv('gitee')) {
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
            // "scope"         => 'user_info',
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
     * @throws GuzzleException
     */
    public function getUserInfo(array $params = []): array
    {
        $params = $params ? $params : input();
        if ((isset($params['state']) && $params['state'] == session('state') && isset($params['code']))) { 

            // 获取access_token
            $data = isset($params['code']) ? $this->getAccessToken($params['code']) : $params;
            
            $access_token = isset($data['access_token']) ? $data['access_token'] : '';
            $refresh_token = isset($data['refresh_token']) ? $data['refresh_token'] : '';
            $expires_in = isset($data['expires_in']) ? $data['expires_in'] : 0;
            if ($access_token) {

                // 获取用户信息
                $queryarr = [
                    "access_token" => $access_token,
                ];

                $ret = $this->http->get(self::GET_USERINFO_URL.'?'.http_build_query($queryarr))->getBody()->getContents();
                $userinfo = json_decode($ret, true);
                
                if (!$userinfo || !is_array($userinfo)) {
                    return [];
                }

                $userinfo['avatar'] = isset($userinfo['avatar_url']) ? $userinfo['avatar_url'] : '';
                $userinfo['avatar'] = str_replace('http://','https://',$userinfo['avatar']);
                $data = [
                    'access_token'  => $access_token,
                    'refresh_token' => $refresh_token,
                    'expires_in'    => $expires_in,
                    'userinfo'      => $userinfo,
                    'id'            => $userinfo['id'],
                ];
                
                return $data;
            }
        }

        return [];
    }

    /**
     * 获取access_token
     * @param string $code
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
        

        try {
            $params = $this->http->post(self::GET_ACCESS_TOKEN_URL,['query'=>$queryarr])->getBody()->getContents();
        } catch (\Throwable $th) {
            if (strstr($th->getMessage(),'error_description')) {
                throw new \Exception('登录已过期，请重新登录');
            }
        }
        
        return $params ? json_decode($params,true): [];
    }
}
