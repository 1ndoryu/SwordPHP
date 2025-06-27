<?php

/**
 * Here is your custom functions.
 */

use app\model\Option;
use app\services\EventService;
use support\Response;
use Webman\Redis;
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

if (!function_exists('get_option')) {
    /**
     * Retrieves an option value from the database, using Redis as a cache.
     * Gracefully falls back to the database if Redis is unavailable.
     *
     * @param string $key The option key to retrieve.
     * @param mixed|null $default The default value to return if the key is not found.
     * @return mixed
     */
    function get_option(string $key, $default = null)
    {
        $cache_key = 'sword_options';
        $options = null;

        try {
            $cached_options = Redis::get($cache_key);
            if ($cached_options) {
                $options = json_decode($cached_options, true);
            }
        } catch (Throwable $e) {
            Log::channel('options')->warning('No se pudo leer el caché de Redis. Usando la base de datos como fallback.', [
                'error' => $e->getMessage()
            ]);
        }

        if ($options === null) {
            try {
                Log::channel('options')->info('Cargando opciones desde la base de datos.');
                $options = Option::all()->pluck('value', 'key')->toArray();

                try {
                    Redis::setex($cache_key, 86400, json_encode($options));
                } catch (Throwable $e) {
                    Log::channel('options')->warning('No se pudo escribir en el caché de Redis.', [
                        'error' => $e->getMessage()
                    ]);
                }
            } catch (Throwable $e) {
                Log::channel('master')->error('Error crítico al obtener opciones de la DB en get_option()', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
                return $default;
            }
        }

        return array_key_exists($key, $options ?? []) ? $options[$key] : $default;
    }
}

// --- INICIO: NUEVA FUNCIÓN ---
if (!function_exists('dispatch_event')) {
    /**
     * Dispatches an event to the event queue (e.g., RabbitMQ).
     * This is a "fire and forget" operation. It logs errors but doesn't block execution.
     *
     * @param string $eventName The name of the event (e.g., 'content.created').
     * @param array $payload The data associated with the event.
     * @return void
     */
    function dispatch_event(string $eventName, array $payload): void
    {
        try {
            EventService::getInstance()->dispatch($eventName, $payload);
            Log::channel('events')->info("Evento despachado: {$eventName}", ['payload' => $payload]);
        } catch (Throwable $e) {
            Log::channel('events')->error("Fallo al despachar evento: {$eventName}", [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
        }
    }
}
// --- FIN: NUEVA FUNCIÓN ---