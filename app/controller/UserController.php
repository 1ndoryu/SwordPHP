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
        // 1. Validar que el usuario a modificar existe.
        $user_to_modify = User::find($id);
        if (!$user_to_modify) {
            return json(['success' => false, 'message' => 'User not found.'], 404);
        }

        // 2. El administrador no puede cambiar su propio rol.
        if ($request->user->id === $user_to_modify->id) {
            return json(['success' => false, 'message' => 'Administrators cannot change their own role.'], 400);
        }

        // 3. Validar el rol entrante.
        $new_role = $request->post('role');
        $allowed_roles = ['admin', 'editor', 'user']; // Roles permitidos en el sistema.

        if (!$new_role || !in_array($new_role, $allowed_roles)) {
            return json([
                'success' => false,
                'message' => 'Invalid or missing role provided.',
                'allowed_roles' => $allowed_roles
            ], 400);
        }

        // 4. Actualizar y guardar.
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

            return json([
                'success' => true,
                'message' => 'User role updated successfully.',
                'data' => $user_to_modify->only(['id', 'username', 'email', 'role'])
            ]);
        } catch (Throwable $e) {
            Log::channel('auth')->error('Error al cambiar el rol del usuario', ['error' => $e->getMessage()]);
            return json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }
}
