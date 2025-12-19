<?php

namespace app\middleware;

use app\support\CgiRequest;
use app\support\CgiResponse;

/**
 * Middleware de autenticación del admin para modo CGI.
 * Compatible con el sistema de middlewares del CgiRouter.
 */
class CgiAdminAuth
{
    /**
     * Procesa la solicitud y verifica autenticación.
     */
    public function process(CgiRequest $request, callable $next)
    {
        $session = $request->session();

        /* Verificar si está autenticado */
        if (!$session->get('admin_logged_in') && $request->path() !== '/admin/login') {
            return CgiResponse::redirect('/admin/login');
        }

        return $next($request);
    }
}
