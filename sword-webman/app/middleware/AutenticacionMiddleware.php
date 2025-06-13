<?php

namespace App\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class AutenticacionMiddleware implements MiddlewareInterface
{
    /**
     * Procesa una solicitud entrante para verificar la autenticación del usuario.
     *
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        if ($request->session()->get('usuarioId')) {
            // Si el 'usuarioId' existe en la sesión, el usuario está autenticado.
            // Le permitimos continuar hacia el controlador correspondiente.
            return $handler($request);
        }

        // Si no está autenticado, guardamos un mensaje de error en la sesión
        // y lo redirigimos al formulario de inicio de sesión.
        session()->set('error', 'Debes iniciar sesión para acceder a esta página.');
        return redirect('/login');
    }
}