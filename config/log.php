<?php
// ARCHIVO MODIFICADO: config/log.php

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
                    'handlers' => [],
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
                    15, // $maxFiles
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

    // Canal para la base de datos
    'database' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/database.log',
                    15,
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

    // Canal para la autenticación
    'auth' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/auth.log',
                    15, // $maxFiles
                    $logLevel
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        "[%datetime%] %level_name%: %message% %context%\n",
                        'Y-m-d H:i:s',
                        true
                    ],
                ],
            ]
        ],
        'processors' => [
            // Aquí se podrían añadir procesadores para añadir más datos a los logs de auth
        ],
    ],

    // Canal para el contenido
    'content' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/content.log',
                    15, // $maxFiles
                    $logLevel
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        "[%datetime%] %level_name%: %message% %context%\n",
                        'Y-m-d H:i:s',
                        true
                    ],
                ],
            ]
        ],
    ],

    // Canal para la gestión de media
    'media' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/media.log',
                    15, // $maxFiles
                    $logLevel
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        "[%datetime%] %level_name%: %message% %context%\n",
                        'Y-m-d H:i:s',
                        true
                    ],
                ],
            ]
        ],
    ],

    'social' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/social.log',
                    15, // $maxFiles
                    $logLevel
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        "[%datetime%] %level_name%: %message% %context%\n",
                        'Y-m-d H:i:s',
                        true
                    ],
                ],
            ]
        ],
    ],

    // Canal para el sistema de opciones
    'options' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/options.log',
                    15, // $maxFiles
                    $logLevel
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        "[%datetime%] %level_name%: %message% %context%\n",
                        'Y-m-d H:i:s',
                        true
                    ],
                ],
            ]
        ],
    ],

    // --- INICIO: NUEVO CANAL ---
    // Canal para el sistema de eventos
    'events' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/events.log',
                    15, // $maxFiles
                    $logLevel
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        "[%datetime%] %level_name%: %message% %context%\n",
                        'Y-m-d H:i:s',
                        true
                    ],
                ],
            ]
        ],
    ],
    // --- FIN: NUEVO CANAL ---
];