<?php

namespace App\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use support\Log; // <-- Asegúrate de añadir esta línea

class NormalizePathMiddleware implements MiddlewareInterface
{
    /**
     * Procesa una solicitud para normalizar su ruta si contiene errores comunes
     * como una doble barra al inicio.
     *
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        // LOG DE DIAGNÓSTICO:
        /*Log::channel('default')->info(
            '[NormalizePathMiddleware] -> Ruta recibida. Path: ' . $request->path() . ' | Full URL: ' . $request->fullUrl()
        );*/

        $path = $request->path();

        // Comprueba si la ruta empieza con una doble barra (ej: "//admin")
        if (strpos($path, '//') === 0) {
            // Limpia la ruta, asegurando que empiece con una sola barra
            $newPath = '/' . ltrim($path, '/');

            // Preserva cualquier parámetro en la URL (ej: ?foo=bar)
            $queryString = $request->queryString();
            $redirectUrl = $newPath . ($queryString ? '?' . $queryString : '');

            // Emite una redirección permanente (código 301) a la URL corregida.
            // Esto es bueno para el SEO y para que los navegadores aprendan la ruta correcta.
            return new Response(301, ['Location' => $redirectUrl]);
        }

        // Si la ruta es correcta, simplemente continúa con la petición.
        return $handler($request);
    }
}