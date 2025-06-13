<?php
declare(strict_types=1);

return [
    'app' => [
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'name' => $_ENV['APP_NAME'] ?? 'SwordPHP'
    ],
    'view' => [
        'path' => TEMPLATES_PATH,
    ]
];