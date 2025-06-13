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
        // Al acceder a la sesión a través del método de la petición,
        // Webman se encarga de iniciarla si aún no lo ha hecho.
        $request->session();

        // Pasamos la petición al siguiente eslabón de la cadena (el controlador).
        return $handler($request);
    }
}
