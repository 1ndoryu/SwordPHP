<?php

return [
    /**
     * RabbitMQ Connection Settings
     */
    'rabbitmq' => [
        'host' => env('RABBITMQ_HOST', '127.0.0.1'),
        'port' => env('RABBITMQ_PORT', 5672),
        'user' => env('RABBITMQ_USER', 'guest'),
        'password' => env('RABBITMQ_PASS', 'guest'),
        'vhost' => env('RABBITMQ_VHOST', '/'),
    ],

    /**
     * The name of the queue where application events will be published.
     */
    'queue' => env('RABBITMQ_EVENTS_QUEUE', 'sword_events_queue'),

];