<?php
// ARCHIVO MODIFICADO: app/middleware/RoleMiddleware.php

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\Log;

class RoleMiddleware implements MiddlewareInterface
{
    /**
     * @var array Almacena los roles permitidos para esta instancia del middleware.
     */
    private array $allowed_roles = [];

    /**
     * El constructor ahora acepta los roles y los guarda en una propiedad de la clase.
     * Por ejemplo: new RoleMiddleware('admin', 'editor')
     *
     * @param string ...$roles
     */
    public function __construct(string ...$roles)
    {
        $this->allowed_roles = $roles;
    }

    /**
     * El método process ahora usa la propiedad $this->allowed_roles para la validación.
     * Ya no recibe los roles como parámetro.
     *
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        $user = $request->user;
        if (!$user) {
            return json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        // Comprobamos si se definieron roles al crear el middleware.
        if (empty($this->allowed_roles)) {
            Log::channel('auth')->warning('Error de configuración de middleware de rol: No se especificaron roles en el constructor.', [
                'path' => $request->path()
            ]);
            return json(['success' => false, 'message' => 'Internal server error: Role middleware misconfigured.'], 500);
        }

        if (!in_array($user->role, $this->allowed_roles)) {
            Log::channel('auth')->warning('Intento de acceso a ruta protegida por rol no autorizado', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_roles' => $this->allowed_roles,
                'path' => $request->path()
            ]);
            return json(['success' => false, 'message' => 'This action is unauthorized.'], 403);
        }

        return $handler($request);
    }
}
