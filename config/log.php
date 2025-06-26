<?php

use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

// Determinar el nivel de log desde el .env, con un valor por defecto
$logLevel = Logger::toMonologLevel(env('LOG_LEVEL', 'DEBUG'));

return [
    // Canal de log por defecto (enviará a 'master')
    'default' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\GroupHandler::class,
                'constructor' => [
                    // Agrupa los manejadores que procesarán los logs del canal 'default'
                    'handlers' => [
                        // Aquí podrías redirigir a otros canales si fuese necesario,
                        // pero para el master log, lo definimos directamente abajo.
                    ],
                ],
            ],
        ],
        'processors' => [],
    ],
    
    // Canal 'master' que captura todos los logs
    'master' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/master.log',
                    7, // $maxFiles
                    $logLevel
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                        'Y-m-d H:i:s',
                        true
                    ],
                ],
            ]
        ],
    ],
    
    // Canal de ejemplo para la base de datos
    'database' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/database.log',
                    7, // $maxFiles
                    $logLevel
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        "[%datetime%] %level_name%: %message%\n",
                        'Y-m-d H:i:s',
                        true
                    ],
                ],
            ]
        ],
    ],
];