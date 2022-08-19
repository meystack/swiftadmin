<?php

namespace system;

use GuzzleHttp\Client;

/**
 * Http 请求类
 */
class Http
{
    /**
     * PC/Mobile 标识
     * @var object 对象实例
     */
    protected static $agent = [
        'Opera/9.80 (Android 2.3.4; Linux; Opera Mobi/build-1107180945; U; en-GB) Presto/2.8.149 Version/11.10',
        'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36',
    ];

    /**
     * 发送一个POST请求
     * @param string $url
     * @param array $params
     * @param bool $agent
     * @param array $options
     * @param $header
     * @return mixed|string
     */
    public static function post(string $url, array $params = [], bool $agent = true, array $options = [], $header = '')
    {
        $req = self::request($url, $params, $agent, 'POST', $options, $header);
        return $req['ret'] ? $req['msg'] : '';
    }

    /**
     * 发送一个GET请求
     * @param string $url
     * @param array $params
     * @param bool $agent
     * @param array $options
     * @param $header
     * @return mixed|string
     */
    public static function get(string $url, array $params = [], bool $agent = true, array $options = [], $header = [])
    {
        $req = self::request($url, $params, $agent, 'GET', $options, $header);
        return $req['ret'] ? $req['msg'] : '';
    }

    /**
     * @param string $url
     * @param array $params
     * @param bool $agent
     * @param string $method
     * @param array $options
     * @param array $header
     * @return array
     */
    public static function request(string $url, array $params, bool $agent, string $method = 'GET', array $options = [], array $header = []): array
    {
        try {
            $client = self::getClient($agent, $options, $header);
            $response = $client->request($method, $url, $params ? ['query' => $params] : [])->getBody()->getContents();
            if (!empty($response)) {
                return ['ret' => true, 'msg' => $response];
            }
        } catch (\Throwable $e) {
            return ['ret' => false, 'msg' => $e->getMessage()];
        }

        return ['ret' => false, 'msg' => $response];
    }

    /**
     * 获取访问客户端
     * @param bool $agent
     * @param array $options
     * @param array $header
     * @return mixed
     */
    private static function getClient(bool $agent, array $options = [], array $header = [])
    {
        if (empty($options)) {
            $options = [
                'timeout'         => 60,
                'connect_timeout' => 60,
                'verify'          => false,
                'http_errors'     => false,
                'headers'         => [
                    'X-REQUESTED-WITH' => 'XMLHttpRequest',
                    'Referer'          => dirname(request()->url()),
                    'User-Agent'       => self::$agent[$agent]
                ]
            ];
        }

        if (!empty($header)) {
            $options['headers'] = array_merge($options['headers'], $header);
        }

        return new Client($options);
    }

}
