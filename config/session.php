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

use Webman\Session\FileSessionHandler;
use Webman\Session\RedisSessionHandler;
use Webman\Session\RedisClusterSessionHandler;

return [

    'type' => get_env('CACHE_DRIVER') ?: 'file',    // or redis or redis_cluster
    'handler' => get_env('CACHE_DRIVER') == 'redis' ? Webman\Session\RedisSessionHandler::class : Webman\Session\FileSessionHandler::class,
    'config'                => [
        'file'          => [
            'save_path' => runtime_path() . '/sessions',
        ],
        'redis'         => [
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'auth'     => '',
            'timeout'  => 2,
            'database' => '',
            'prefix'   => 'redis_session_',
        ],
        'redis_cluster' => [
            'host'    => ['127.0.0.1:7000', '127.0.0.1:7001', '127.0.0.1:7001'],
            'timeout' => 2,
            'auth'    => '',
            'prefix'  => 'redis_session_',
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
