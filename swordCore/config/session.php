<?php

use Webman\Session\FileSessionHandler;
use Webman\Session\RedisSessionHandler;
use Webman\Session\RedisClusterSessionHandler;

// 1. Obtenemos el driver de la variable de entorno. Si no existe, usamos 'file' por defecto.
$sessionDriver = env('SESSION_DRIVER', 'file');

// 2. Definimos las configuraciones para cada driver.
$handlerConfig = [
    'file' => [
        'type'    => 'file',
        'handler' => FileSessionHandler::class,
    ],
    'redis' => [
        'type'    => 'redis',
        'handler' => RedisSessionHandler::class,
    ],
];

// 3. Seleccionamos la configuración activa. Si se especifica un driver inválido, se usará 'file'.
$activeConfig = $handlerConfig[$sessionDriver] ?? $handlerConfig['file'];


return [
    // Usamos los valores de la configuración activa que seleccionamos arriba.
    'type' => $activeConfig['type'],
    'handler' => $activeConfig['handler'],

    'config' => [
        'file' => [
            'save_path' => runtime_path() . '/sessions',
        ],

        // --- INICIO DE LA CORRECCIÓN ---
        // Ahora esta sección lee las variables de entorno de Coolify
        'redis' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => (int) env('REDIS_PORT', 6379), // <--- ¡AÑADIR (int) AQUÍ!
            'auth'     => env('REDIS_PASSWORD', null),
            'timeout'  => 2,
            'database' => 0,
            'prefix'   => 'redis_session_',
        ],
        // --- FIN DE LA CORRECCIÓN ---

        'redis_cluster' => [
            'host' => ['127.0.0.1:7000', '127.0.0.1:7001', '127.0.0.1:7001'],
            'timeout' => 2,
            'auth' => '',
            'prefix' => 'redis_session_',
        ]
    ],

    'session_name' => 'PHPSID',
    'auto_update_timestamp' => false,
    'lifetime' => 7*24*60*60,
    'cookie_lifetime' => 365*24*60*60,
    'cookie_path' => '/',
    'domain' => '',
    'http_only' => true,
    'secure' => false,
    'same_site' => '',
    'gc_probability' => [1, 1000],
];