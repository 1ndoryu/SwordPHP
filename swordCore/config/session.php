<?php

use Webman\Session\FileSessionHandler;
use Webman\Session\RedisSessionHandler; // Asegúrate de que RedisSessionHandler está importado
use Webman\Session\RedisClusterSessionHandler;

return [

    'type' => 'redis', // CAMBIAR: de 'file' a 'redis'

    'handler' => RedisSessionHandler::class, // CAMBIAR: de FileSessionHandler::class a RedisSessionHandler::class

    'config' => [
        // La configuración 'file' se ignora, la dejamos como está.
        'file' => [
            'save_path' => runtime_path() . '/sessions',
        ],
        // La configuración 'redis' ahora será utilizada.
        // Se conecta a la configuración 'default' que definimos en config/redis.php
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