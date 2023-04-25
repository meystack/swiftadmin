<?php
return [
    'default' => get_env('CACHE_DRIVER') ?: 'file',
    'stores'  => [
        'file'  => [
            'type'   => 'File',
            // 缓存保存目录
            'path'   => runtime_path() . '/cache/',
            // 缓存前缀
            'prefix' => '',
            // 缓存有效期 0表示永久缓存
            'expire' => 0,
        ],
        'redis' => [
            'type'     => 'redis',
            'host'     => get_env('CACHE_HOSTNAME') ?: '127.0.0.1',
            'port'     => get_env('CACHE_HOSTPORT') ?: 6379,
            'select'   => get_env('CACHE_SELECT') ?: 0,
            'password' => get_env('CACHE_PASSWORD') ?: '',
            'prefix'   => 'redis_',
            'expire'   => 0,
        ],
    ],
];