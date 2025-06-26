<?php

return [
    /**
     * JWT Secret Key.
     * IMPORTANT: This should be a long, random string.
     * You can generate one with: openssl rand -base64 32
     */
    'secret' => env('JWT_SECRET', 'change-this-secret-key'),

    /**
     * JWT Time To Live (in seconds).
     * Default is 1 hour (3600 seconds).
     */
    'ttl' => env('JWT_TTL', 3600),

    /**
     * JWT Algorithm.
     */
    'algo' => env('JWT_ALGO', 'HS256'),
];
