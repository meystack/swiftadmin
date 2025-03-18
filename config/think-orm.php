<?php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            // 数据库类型
            'type' => 'mysql',
            // 服务器地址
            'hostname' => get_env('DATABASE_HOSTNAME') ?: '127.0.0.1',
            // 数据库名
            'database' => get_env('DATABASE_DATABASE') ?: 'sademo',
            // 数据库用户名
            'username' => get_env('DATABASE_USERNAME') ?: 'root',
            // 数据库密码
            'password' => get_env('DATABASE_PASSWORD') ?: '123456',
            // 数据库连接端口
            'hostport' => get_env('DATABASE_HOSTPORT') ?: '3306',
            // 数据库连接参数
            'params' => [
                // 连接超时3秒
                \PDO::ATTR_TIMEOUT => 3,
            ],
            // 数据库编码默认采用utf8
            'charset' => 'utf8mb4',
            // 数据库表前缀
            'prefix' => get_env('DATABASE_PREFIX') ?: 'sa_',
            // 断线重连
            'break_reconnect' => true,
            // 自定义分页类
            'bootstrap' =>  '',
            // 连接池配置(仅在swow/swoole驱动下有效)
            'pool' => [
                'max_connections' => 5,
                'min_connections' => 1,
                'wait_timeout' => 3,
                'idle_timeout' => 60,
                'heartbeat_interval' => 50,
            ],
        ],
    ],
];
