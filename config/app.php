<?php

declare(strict_types=1);

return [
    'appName' => $_ENV['APP_NAME'] ?? 'SwordPHP',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => (bool) ($_ENV['APP_DEBUG'] ?? false),
    'view' => [
        'path' => TEMPLATES_PATH,
    ],
    'db' => [
        'driver' => $_ENV['DB_DRIVER'] ?? 'pgsql',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => (int) ($_ENV['DB_PORT'] ?? 5432),
        'database' => $_ENV['DB_DATABASE'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'charset' => 'utf8',
    ]
];
