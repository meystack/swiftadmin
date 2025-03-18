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
    'default' => [
        'host'     => get_env('CACHE_HOSTNAME', '127.0.0.1'),
        'password' => get_env('CACHE_PASSWORD', null),
        'port'     => (int)get_env('CACHE_HOSTPORT',6379),
        'database' => get_env('CACHE_SELECT', 0),
        'prefix'   => 'redis_',
        'expire'   => 0,
        'pool' => [
            'max_connections' => 5,
            'min_connections' => 1,
            'wait_timeout' => 3,
            'idle_timeout' => 60,
            'heartbeat_interval' => 50,
        ],
    ]
];
