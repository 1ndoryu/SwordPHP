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
        // Lógica condicional: Mantenemos la llamada explícita a session_start()
        // únicamente para el entorno de Windows, como se ha solicitado, para
        // asegurar la compatibilidad con esa plataforma.
        if (PHP_OS_FAMILY === 'Windows') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }

        // Esta línea es la forma estándar de Webman para manejar la sesión.
        // Se encarga de cargar la sesión usando la configuración del framework,
        // lo cual es seguro y necesario para todos los sistemas operativos.
        $request->session();

        // Pasamos la petición al siguiente eslabón de la cadena (el controlador).
        return $handler($request);
    }
}