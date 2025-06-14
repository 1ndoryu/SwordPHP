<?php

namespace App\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AutenticacionMiddleware implements MiddlewareInterface
{
    /**
     * Procesa una solicitud entrante.
     *
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        $usuario = usuarioActual();

        // Caso 1: El usuario está logueado y es administrador.
        // Se le permite el acceso al panel.
        if ($usuario && $usuario->rol === 'admin') {
            return $handler($request);
        }

        // Caso 2: El usuario está logueado, pero NO es administrador.
        // Se le deniega el acceso al panel y se le redirige al inicio,
        // pero se mantiene su sesión activa.
        if ($usuario) {
            $request->session()->set('error', 'No tienes los permisos necesarios para acceder al panel de administración.');
            return redirect('/');
        }
        // Caso 3: No hay ningún usuario logueado.
        // Se le pide iniciar sesión para acceder a la página solicitada.
        $request->session()->set('error', 'Debes iniciar sesión para acceder a esta página.');
        return redirect('/login');
    }
}