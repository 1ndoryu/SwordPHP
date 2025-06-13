<?php

namespace App\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

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
        // Forzar el inicio de una sesión nativa de PHP si no hay una activa.
        // Webman gestiona la configuración (como dónde se guardan los archivos),
        // pero para usar session_regenerate_id(), necesitamos que PHP sepa
        // que la sesión está formalmente "iniciada".
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Esta línea permite a Webman manejar los datos de la sesión a través de su objeto.
        $request->session();

        // Pasamos la petición al siguiente eslabón de la cadena (el controlador).
        return $handler($request);
    }
}