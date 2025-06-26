<?php
// ARCHIVO NUEVO: app/middleware/RoleMiddleware.php

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\Log;

class RoleMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming request.
     *
     * @param Request  $request
     * @param callable $handler
     * @param string   ...$roles A comma-separated list of roles, e.g., 'admin' or 'admin,editor'
     * @return Response
     */
    public function process(Request $request, callable $handler, ...$roles): Response
    {
        // The 'auth' middleware should run before this one.
        $user = $request->user;
        if (!$user) {
            // This case should ideally be prevented by the JwtAuthentication middleware.
            return json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        // The route definition provides roles like 'role:admin' or 'role:admin|editor'.
        // Webman passes the part after the colon as parameters.
        // We expect one parameter string like 'admin|editor'.
        $allowed_roles = isset($roles[0]) ? explode('|', $roles[0]) : [];

        if (empty($allowed_roles)) {
            Log::channel('auth')->warning('Error de configuración de middleware de rol: No se especificaron roles.', [
                'path' => $request->path()
            ]);
            // Prevent access if the middleware is misconfigured.
            return json(['success' => false, 'message' => 'Internal server error: Role middleware misconfigured.'], 500);
        }

        if (!in_array($user->role, $allowed_roles)) {
            Log::channel('auth')->warning('Intento de acceso a ruta protegida por rol no autorizado', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_roles' => $allowed_roles,
                'path' => $request->path()
            ]);
            return json(['success' => false, 'message' => 'This action is unauthorized.'], 403);
        }

        return $handler($request);
    }
}
