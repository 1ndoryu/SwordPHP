<?php

return [
    // Canal de log por defecto
    'default' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\StreamHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/webman.log', // Archivo único para el canal por defecto
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
                'class' => Monolog\Handler\StreamHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/database.log', // Archivo único para BD
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
                'class' => Monolog\Handler\StreamHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/security.log', // Archivo único para seguridad
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
