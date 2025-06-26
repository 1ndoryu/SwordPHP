<?php

use support\Db;

return [
    // Conexión por defecto
    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [
        'pgsql' => [
            'driver'      => 'pgsql',
            'host'        => env('DB_HOST', '127.0.0.1'),
            'port'        => env('DB_PORT', '5432'),
            'database'    => env('DB_DATABASE', 'sword_db'),
            'username'    => env('DB_USERNAME', 'admin'),
            'password'    => env('DB_PASSWORD', 'admin'),
            'charset'     => 'utf8',
            'prefix'      => '',
            'schema'      => 'public',
            'sslmode'     => 'prefer',
        ],
    ],
];