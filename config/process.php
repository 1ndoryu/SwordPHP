<?php
// ARCHIVO MODIFICADO: config/process.php

use support\Log;
use support\Request;
use app\process\Http;
use app\process\WebhookListener;
use app\process\JophielSyncProcess; // <-- AÑADIDO

global $argv;

return [
    'webman' => [
        'handler' => Http::class,
        'listen' => 'http://0.0.0.0:8787',
        'count' => cpu_count() * 4,
        'user' => '',
        'group' => '',
        'reusePort' => false,
        'eventLoop' => '',
        'context' => [],
        'constructor' => [
            'requestClass' => Request::class,
            'logger' => Log::channel('default'),
            'appPath' => app_path(),
            'publicPath' => public_path()
        ]
    ],
    // File update detection and automatic reload
    'monitor' => [
        'handler' => app\process\Monitor::class,
        'reloadable' => false,
        'constructor' => [
            // Monitor these directories
            'monitorDir' => array_merge([
                app_path(),
                config_path(),
                base_path() . '/process',
                base_path() . '/support',
                base_path() . '/resource',
                base_path() . '/.env',
            ], glob(base_path() . '/plugin/*/app'), glob(base_path() . '/plugin/*/config'), glob(base_path() . '/plugin/*/api')),
            // Files with these suffixes will be monitored
            'monitorExtensions' => [
                'php', 'html', 'htm', 'env'
            ],
            'options' => [
                'enable_file_monitor' => !in_array('-d', $argv) && DIRECTORY_SEPARATOR === '/',
                'enable_memory_monitor' => DIRECTORY_SEPARATOR === '/',
            ]
        ]
    ],
    'webhook-listener' => [
        'handler' => WebhookListener::class,
        'count' => 1, // Usualmente, un solo listener es suficiente.
        'user' => '',
        'group' => '',
        'reloadable' => true, // Puede ser recargado si su código cambia
    ],
    // --- INICIO: NUEVO PROCESO DE SINCRONIZACIÓN ---
    'jophiel-sync' => [
        'handler' => JophielSyncProcess::class,
        'count'   => 1, // Solo se necesita una instancia de este proceso.
        'user'    => '',
        'group'   => '',
        'reloadable' => true,
    ]
    // --- FIN: NUEVO PROCESO DE SINCRONIZACIÓN ---
];