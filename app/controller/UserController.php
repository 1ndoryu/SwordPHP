<?php

namespace app\controller;

use app\model\User;
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class UserController
{
    /**
     * Change the role of a specific user.
     * Only accessible by administrators.
     *
     * @param Request $request
     * @param integer $id The ID of the user to modify.
     * @return Response
     */
    public function changeRole(Request $request, int $id): Response
    {
        $user_to_modify = User::find($id);
        if (!$user_to_modify) {
            return api_response(false, 'User not found.', null, 404);
        }

        if ($request->user->id === $user_to_modify->id) {
            return api_response(false, 'Administrators cannot change their own role.', null, 400);
        }

        $new_role = $request->post('role');
        $allowed_roles = ['admin', 'editor', 'user'];

        if (!$new_role || !in_array($new_role, $allowed_roles)) {
            return api_response(
                false,
                'Invalid or missing role provided.',
                ['allowed_roles' => $allowed_roles],
                400
            );
        }

        try {
            $old_role = $user_to_modify->role;
            $user_to_modify->role = $new_role;
            $user_to_modify->save();

            Log::channel('auth')->warning('Rol de usuario modificado por administrador', [
                'admin_id' => $request->user->id,
                'modified_user_id' => $user_to_modify->id,
                'old_role' => $old_role,
                'new_role' => $new_role,
            ]);

            return api_response(
                true,
                'User role updated successfully.',
                $user_to_modify->only(['id', 'username', 'email', 'role'])
            );
        } catch (Throwable $e) {
            Log::channel('auth')->error('Error al cambiar el rol del usuario', ['error' => $e->getMessage()]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }
}
