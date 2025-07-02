<?php

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Request;
use Webman\Http\Response;
use support\Log;

/**
 * Class RequestLogger
 * Logs every incoming HTTP request and its response status/duration.
 * The log channel is chosen based on the uri path to keep records organized.
 */
class RequestLogger implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $startTime = microtime(true);

        // Forward the request down the middleware/route pipeline
        $response = $handler($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2); // ms
        $method   = $request->method();
        $path     = $request->path();
        $status   = $response->getStatusCode();
        $userId   = $request->user->id ?? null;

        // Choose channel according to the request path
        $channel = $this->determineChannel($path);

        // Prepare a short representation of the response (solo para depuración)
        $responseBodyPreview = $this->extractResponseBody($response);

        // Assemble the log context
        $context = [
            'method'       => $method,
            'path'         => $path,
            'params'       => $request->all(),
            'status'       => $status,
            'duration_ms'  => $duration,
            'ip'           => $request->getRemoteIp(),
            'user_id'      => $userId,
            'response'     => $responseBodyPreview,
        ];

        Log::channel($channel)->info('HTTP request', $context);

        return $response;
    }

    /**
     * Determine which Monolog channel to use based on the URI path.
     */
    private function determineChannel(string $path): string
    {
        if (str_starts_with($path, '/contents') || str_starts_with($path, '/feed')) {
            return 'content';
        }
        if (str_starts_with($path, '/auth') || str_starts_with($path, '/user') || str_starts_with($path, '/users')) {
            return 'auth';
        }
        if (str_starts_with($path, '/media')) {
            return 'media';
        }
        return 'master';
    }

    /**
     * Safely extract the request body for logging, avoiding large binary payloads.
     */
    private function extractBody(Request $request): ?array
    {
        $contentType = $request->header('content-type', '');
        if (str_contains($contentType, 'application/json')) {
            return $request->all();
        }
        // For other content types (form-data, etc.) we avoid logging to prevent large/binary data in logs
        return null;
    }

    /**
     * Extract a preview of the response body to avoid huge/binary payloads.
     */
    private function extractResponseBody(Response $response): mixed
    {
        $contentType = $response->getHeader('Content-Type') ?? $response->getHeader('content-type') ?? '';

        // Solo loguear JSON o texto plano y recortar a 2k caracteres
        if (str_contains($contentType, 'application/json') || str_contains($contentType, 'text/plain')) {
            $raw = $response->rawBody();
            // Limitamos longitud para evitar logs gigantes
            if (strlen($raw) > 2000) {
                $raw = substr($raw, 0, 2000) . '...';
            }

            // Intentamos decodificar JSON si es posible para mejor legibilidad
            if (str_contains($contentType, 'application/json')) {
                $decoded = json_decode($raw, true);
                return $decoded ?? $raw;
            }

            return $raw;
        }

        // Otro tipo de contenido (binario, descargas, etc.)
        return null;
    }
} 