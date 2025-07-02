<?php
// ARCHIVO CORREGIDO: config/middleware.php

/**
 * Middleware configuration file
 */
return [
    // Global middleware.
    '' => [
        app\middleware\RequestLogger::class, // Logs every incoming request
    ],

    // Se elimina por completo la entrada de alias ('role' => ...) que causaba el error.
];
