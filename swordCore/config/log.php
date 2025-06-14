<?php

return [
    // Canal de log por defecto
    'default' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/webman.log', // Log general de la aplicación
                    7, // Número máximo de archivos a rotar
                    Monolog\Logger::DEBUG, // Nivel mínimo de log a registrar
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
    // Canal de log para la base de datos
    'database' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/database.log', // Archivo específico para BD
                    7,
                    Monolog\Logger::DEBUG, // Registramos todo para depuración
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
    // Canal de log para eventos de seguridad
    'security' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/security.log', // Archivo específico para seguridad
                    7,
                    Monolog\Logger::INFO, // Registramos desde nivel informativo hacia arriba
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
];