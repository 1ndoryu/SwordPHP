<?php

/**
 * Here is your custom functions.
 */

use app\model\Option;
use support\Response;
use Webman\Redis;
use Throwable;
use support\Log;


if (!function_exists('api_response')) {
    /**
     * Creates a standard API JSON response.
     *
     * @param boolean $success Indicates if the operation was successful.
     * @param string $message A message to return.
     * @param array|null $data The payload data.
     * @param integer $status_code The HTTP status code.
     * @param array $headers Additional headers.
     * @return Response
     */
    function api_response(
        bool $success,
        string $message,
        ?array $data = null,
        int $status_code = 200,
        array $headers = []
    ): Response {
        $body = [
            'success' => $success,
            'message' => $message,
        ];

        if ($data !== null) {
            $body['data'] = $data;
        }

        return new Response(
            $status_code,
            ['Content-Type' => 'application/json'] + $headers,
            json_encode($body)
        );
    }
}

// --- INICIO DE LA IMPLEMENTACIÓN ---

if (!function_exists('get_option')) {
    /**
     * Retrieves an option value from the database with Redis caching.
     * The entire set of options is cached to reduce DB queries on subsequent calls.
     *
     * @param string $key The option key to retrieve.
     * @param mixed|null $default The default value to return if the key is not found.
     * @return mixed
     */
    function get_option(string $key, $default = null)
    {
        $cache_key = 'sword_options';

        try {
            // First, try to get the options from the Redis cache.
            $cached_options = Redis::get($cache_key);

            if ($cached_options) {
                $options = json_decode($cached_options, true);
            } else {
                // If not in cache, fetch all options from the database.
                Log::channel('options')->info('Caché de opciones no encontrado. Cargando desde la base de datos.');
                $options = Option::all()->pluck('value', 'key')->toArray();

                // Cache the result for 24 hours. The cache is invalidated on update.
                Redis::setex($cache_key, 86400, json_encode($options));
            }

            // Return the specific option value if it exists, otherwise return the default.
            return array_key_exists($key, $options) ? $options[$key] : $default;
        } catch (Throwable $e) {
            // If Redis or the DB fails, log the error and return the default value to prevent crashes.
            Log::channel('master')->error('Error crítico en el helper get_option()', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }
}
