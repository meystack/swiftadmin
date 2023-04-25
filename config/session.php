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

return [
    'type'                  => get_env('CACHE_DRIVER') ?: 'file',    // or redis or redis_cluster
    'handler'               => get_env('CACHE_DRIVER') == 'redis' ? Webman\Session\RedisSessionHandler::class : Webman\Session\FileSessionHandler::class,
    'config'                => [
        'file'          => [
            'save_path' => runtime_path() . '/sessions',
        ],
        'redis'         => [
            'host'     => get_env('CACHE_HOSTNAME') ?: '127.0.0.1',
            'port'     => get_env('CACHE_HOSTPORT') ?: 6379,
            'database' => get_env('CACHE_SELECT') ?: 0,
            'auth'     => get_env('CACHE_PASSWORD') ?: '',
            'prefix'   => '', // session key prefix
        ],
        'redis_cluster' => [
            'host'    => ['127.0.0.1:7000', '127.0.0.1:7001', '127.0.0.1:7001'],
            'timeout' => 2,
            'auth'    => '',
            'prefix'  => '',
        ]
    ],
    'session_name'          => 'SESSION_ID',
    'auto_update_timestamp' => false,
    'lifetime'              => 7 * 24 * 60 * 60,
    'cookie_lifetime'       => 7 * 24 * 60 * 60,
    'cookie_path'           => '/',
    'domain'                => '',
    'http_only'             => false,
    'secure'                => false,
    'same_site'             => '',
    'gc_probability'        => [1, 1000],

];
