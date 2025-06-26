<?php


return [
    'default' => [
        'password' => '',
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
        'pool' => [
            'max_connections' => 5,
            'min_connections' => 1,
            'wait_timeout' => 3,
            'idle_timeout' => 60,
            'heartbeat_interval' => 50,
        ],
    ]
];
