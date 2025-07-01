<?php

namespace app\traits;

use support\Response;
use support\Log;
use Throwable;

trait HandlesErrors
{
    /**
     * Handles exceptions with consistent logging and response format.
     *
     * @param Throwable $e The exception
     * @param string $channel Log channel to use
     * @param string $operation Description of the operation that failed
     * @param array $context Additional context for logging
     * @return Response
     */
    protected function handleError(
        Throwable $e, 
        string $channel, 
        string $operation, 
        array $context = []
    ): Response {
        $context['error'] = $e->getMessage();
        
        Log::channel($channel)->error($operation, $context);
        
        return api_response(false, 'An internal error occurred.', null, 500);
    }

    /**
     * Logs operation success with consistent format.
     *
     * @param string $channel Log channel to use
     * @param string $message Success message
     * @param array $context Additional context for logging
     * @return void
     */
    protected function logSuccess(string $channel, string $message, array $context = []): void
    {
        Log::channel($channel)->info($message, $context);
    }

    /**
     * Logs security warnings (unauthorized access attempts, etc.).
     *
     * @param string $message Warning message
     * @param array $context Additional context for logging
     * @return void
     */
    protected function logSecurityWarning(string $message, array $context = []): void
    {
        Log::channel('auth')->warning($message, $context);
    }
}