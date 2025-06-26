<?php

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\Log;

class PermissionMiddleware implements MiddlewareInterface
{
    /**
     * @var array Required permissions for this instance of the middleware.
     */
    private array $required_permissions = [];

    /**
     * The constructor accepts the required permissions.
     * Example: new PermissionMiddleware('content.create', 'media.upload')
     *
     * @param string ...$permissions
     */
    public function __construct(string ...$permissions)
    {
        $this->required_permissions = $permissions;
    }

    /**
     * The process method uses the $this->required_permissions property for validation.
     * It will check if the authenticated user has ALL the specified permissions.
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

        if (empty($this->required_permissions)) {
            Log::channel('auth')->warning('PermissionMiddleware configuration error: No permissions were specified in the constructor.', [
                'path' => $request->path()
            ]);
            return api_response(false, 'Internal server error: Permission middleware misconfigured.', null, 500);
        }

        foreach ($this->required_permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                Log::channel('auth')->warning('Permission denied for user.', [
                    'user_id' => $user->id,
                    'user_role' => $user->role->name ?? 'N/A',
                    'required_permission' => $permission,
                    'path' => $request->path()
                ]);
                return api_response(false, 'Forbidden: You do not have the required permission to perform this action.', null, 403);
            }
        }

        return $handler($request);
    }
}
