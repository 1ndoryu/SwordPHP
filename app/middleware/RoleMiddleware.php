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
            return api_response(false, 'Authentication required.', null, 401);
        }

        // Comprobamos si se definieron roles al crear el middleware.
        if (empty($this->allowed_roles)) {
            Log::channel('auth')->warning('Error de configuración de middleware de rol: No se especificaron roles en el constructor.', [
                'path' => $request->path()
            ]);
            return api_response(false, 'Internal server error: Role middleware misconfigured.', null, 500);
        }

        // --- INICIO DE LA CORRECCIÓN ---
        // Se utiliza trim() para eliminar posibles espacios en blanco en el rol del usuario,
        // haciendo la comprobación más robusta ante inconsistencias en los datos.
        $user_role = trim($user->role);
        if (!in_array($user_role, $this->allowed_roles)) {
            // --- FIN DE LA CORRECCIÓN ---
            Log::channel('auth')->warning('Intento de acceso a ruta protegida por rol no autorizado', [
                'user_id' => $user->id,
                'user_role' => $user->role, // Se loguea el rol original para depuración
                'required_roles' => $this->allowed_roles,
                'path' => $request->path()
            ]);
            return api_response(false, 'This action is unauthorized.', null, 403);
        }

        return $handler($request);
    }
}
