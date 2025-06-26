<?php

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
            return api_response(false, 'Authentication required.', null, 401);
        }
        
        if (empty($this->allowed_roles)) {
            Log::channel('auth')->warning('Error de configuración de middleware de rol: No se especificaron roles en el constructor.', [
                'path' => $request->path()
            ]);
            return api_response(false, 'Internal server error: Role middleware misconfigured.', null, 500);
        }
        
        // Se obtiene el nombre del rol a través de la relación.
        $user_role_name = $user->role->name ?? null;

        if (!$user_role_name || !in_array($user_role_name, $this->allowed_roles)) {
            Log::channel('auth')->warning('Intento de acceso a ruta protegida por rol no autorizado', [
                'user_id' => $user->id,
                'user_role' => $user_role_name,
                'required_roles' => $this->allowed_roles,
                'path' => $request->path()
            ]);
            return api_response(false, 'This action is unauthorized.', null, 403);
        }

        return $handler($request);
    }
}