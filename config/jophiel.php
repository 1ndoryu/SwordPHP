<?php
// NUEVO ARCHIVO: config/jophiel.php

return [
    /**
     * RabbitMQ Exchange Settings for Jophiel Events
     */
    'exchange' => [
        'name' => 'sword_events',
        'type' => 'topic',
    ],

    /**
     * Jophiel API configuration for fetching recommendations
     */
    'api' => [
        'base_url' => env('JOPHIEL_API_URL', 'http://127.0.0.1:8787'),
        'timeout' => env('JOPHIEL_API_TIMEOUT', 5), // Timeout in seconds
    ]
];
