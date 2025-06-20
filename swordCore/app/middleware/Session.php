<?php

namespace App\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\Log;

class Session implements MiddlewareInterface
{
    /**
     * Procesa la petición, asegurando que la sesión se inicie.
     *
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        // La llamada a $request->session() carga o inicia la sesión.
        $session = $request->session();

        // Log para depuración
        Log::channel('session_debug')->info('Middleware\Session: Procesando petición.', [
            'uri' => $request->uri(),
            'session_id' => $session->getId(),
            'session_data' => $session->all()
        ]);

        // Pasamos la petición al siguiente eslabón de la cadena (el controlador).
        return $handler($request);
    }
}
