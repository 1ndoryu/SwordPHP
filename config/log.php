<?php
// ARCHIVO CORREGIDO: config/log.php

use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

// Determinar el nivel de log desde el .env, con un valor por defecto
$logLevel = Logger::toMonologLevel(env('LOG_LEVEL', 'DEBUG'));

// Formatter para logs específicos (formato más simple)
$specific_formatter = [
    'class' => LineFormatter::class,
    'constructor' => [
        "[%datetime%] %level_name%: %message% %context% %extra%\n",
        'Y-m-d H:i:s',
        true, // allowInlineLineBreaks
        true  // ignoreEmptyContextAndExtra
    ],
];

// Formatter para el log maestro (incluye el nombre del canal para más contexto)
$master_formatter = [
    'class' => LineFormatter::class,
    'constructor' => [
        "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
        'Y-m-d H:i:s',
        true, // allowInlineLineBreaks
        true  // ignoreEmptyContextAndExtra
    ],
];

// Definición de la configuración del handler maestro (para ser reutilizada)
$master_handler_config = [
    'class' => RotatingFileHandler::class,
    'constructor' => [
        runtime_path() . '/logs/master.log',
        15, // $maxFiles
        $logLevel
    ],
    'formatter' => $master_formatter,
];

// Función helper para crear la configuración de un handler específico de canal
$create_specific_handler_config = function (string $channel_log_file) use ($logLevel, $specific_formatter) {
    return [
        'class' => RotatingFileHandler::class,
        'constructor' => [
            $channel_log_file,
            15, // $maxFiles
            $logLevel
        ],
        'formatter' => $specific_formatter,
    ];
};

return [
    // El canal por defecto escribe directamente en el log maestro.
    // Se usa cuando se llama a Log::info(...) sin especificar un canal.
    'default' => [
        'handlers' => [
            $master_handler_config,
        ],
        'processors' => [],
    ],

    // El canal 'master' también escribe solo en master.log.
    'master' => [
        'handlers' => [
            $master_handler_config,
        ],
    ],

    // Canales específicos que escriben en su propio archivo Y en master.log
    'database' => [
        'handlers' => [
            $create_specific_handler_config(runtime_path() . '/logs/database.log'),
            $master_handler_config
        ],
    ],
    'auth' => [
        'handlers' => [
            $create_specific_handler_config(runtime_path() . '/logs/auth.log'),
            $master_handler_config
        ],
    ],
    'content' => [
        'handlers' => [
            $create_specific_handler_config(runtime_path() . '/logs/content.log'),
            $master_handler_config
        ],
    ],
    'media' => [
        'handlers' => [
            $create_specific_handler_config(runtime_path() . '/logs/media.log'),
            $master_handler_config
        ],
    ],
    'social' => [
        'handlers' => [
            $create_specific_handler_config(runtime_path() . '/logs/social.log'),
            $master_handler_config
        ],
    ],
    'options' => [
        'handlers' => [
            $create_specific_handler_config(runtime_path() . '/logs/options.log'),
            $master_handler_config
        ],
    ],
    'events' => [
        'handlers' => [
            $create_specific_handler_config(runtime_path() . '/logs/events.log'),
            $master_handler_config
        ],
    ],
    'webhooks' => [
        'handlers' => [
            $create_specific_handler_config(runtime_path() . '/logs/webhooks.log'),
            $master_handler_config
        ],
    ],
];
