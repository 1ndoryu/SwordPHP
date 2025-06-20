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
        // La llamada a $request->session() es la forma estándar y agnóstica al sistema operativo
        // para asegurar que la sesión se cargue y esté disponible. El manejador de sesión
        // configurado en config/session.php (FileSessionHandler por defecto) se encargará
        // de los detalles de bajo nivel, como iniciar la sesión si es necesario.
        $request->session();

        // Pasamos la petición al siguiente eslabón de la cadena (el controlador).
        return $handler($request);
    }
}