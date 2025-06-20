<?php

use Webman\Session\FileSessionHandler;
use Webman\Session\RedisSessionHandler;
use Webman\Session\RedisClusterSessionHandler;

// --- INICIO DE LA LÓGICA DINÁMICA ---

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

// --- FIN DE LA LÓGICA DINÁMICA ---


return [
    // Usamos los valores de la configuración activa que seleccionamos arriba.
    'type' => $activeConfig['type'],
    'handler' => $activeConfig['handler'],

    'config' => [
        // La configuración 'file' siempre está aquí para que no falle si es seleccionada.
        'file' => [
            'save_path' => runtime_path() . '/sessions',
        ],
        // La configuración 'redis' también está siempre disponible.
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'auth' => '',
            'timeout' => 2,
            'database' => '',
            'prefix' => 'redis_session_',
        ],
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