<?php

/**
 * Here is your custom functions.
 */

use support\Response;

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
