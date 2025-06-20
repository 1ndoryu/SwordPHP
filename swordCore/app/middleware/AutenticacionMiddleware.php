<?php

namespace App\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\Log;

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
        Log::channel('session_debug')->info('Middleware\Autenticacion: Ejecutando...', [
            'uri' => $request->uri(),
            'session_id' => $request->session()->getId(),
        ]);
        $usuario = usuarioActual();

        // Caso 1: El usuario está logueado y es administrador.
        if ($usuario && $usuario->rol === 'admin') {
            Log::channel('session_debug')->info('Middleware\Autenticacion: ACCESO PERMITIDO.', [
                'motivo' => 'Usuario autenticado y es admin.',
                'usuarioId' => $usuario->id,
                'rol' => $usuario->rol
            ]);
            return $handler($request);
        }

        // Caso 2: El usuario está logueado, pero NO es administrador.
        if ($usuario) {
            Log::channel('session_debug')->warning('Middleware\Autenticacion: ACCESO DENEGADO Y REDIRIGIDO A INICIO.', [
                'motivo' => 'Usuario autenticado pero no es admin.',
                'usuarioId' => $usuario->id,
                'rol' => $usuario->rol
            ]);
            $request->session()->set('error', 'No tienes los permisos necesarios para acceder al panel de administración.');
            return redirect('/');
        }
        // Caso 3: No hay ningún usuario logueado.
        Log::channel('session_debug')->warning('Middleware\Autenticacion: ACCESO DENEGADO Y REDIRIGIDO A LOGIN.', [
            'motivo' => 'No hay usuario autenticado (usuarioActual devolvió null).',
        ]);
        $request->session()->set('error', 'Debes iniciar sesión para acceder a esta página.');
        return redirect('/login');
    }
}
