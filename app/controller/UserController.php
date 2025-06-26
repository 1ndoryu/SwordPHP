<?php

namespace app\controller;

use app\model\Content;
use app\model\User;
use app\model\Role; // <-- Añadido
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

        $new_role_id = $request->post('role_id');
        if (!$new_role_id || !Role::where('id', $new_role_id)->exists()) {
             return api_response(false, 'Invalid or missing role_id provided.', null, 400);
        }
        
        try {
            $old_role_id = $user_to_modify->role_id;
            $user_to_modify->role_id = $new_role_id;
            $user_to_modify->save();

            Log::channel('auth')->warning('Rol de usuario modificado por administrador', [
                'admin_id' => $request->user->id,
                'modified_user_id' => $user_to_modify->id,
                'old_role_id' => $old_role_id,
                'new_role_id' => $new_role_id,
            ]);
            
            // Recargar el usuario con la nueva información del rol
            $user_to_modify->load('role');

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

    /**
     * Retrieves a paginated list of content liked by the authenticated user.
     *
     * @param Request $request
     * @return Response
     */
    public function likedContent(Request $request): Response
    {
        $user = $request->user;

        try {
            $per_page = (int) $request->get('per_page', 15);
            $per_page = min($per_page, 100);

            // Get IDs of content liked by the user
            $liked_content_ids = $user->likes()->pluck('content_id');

            // Retrieve and paginate the actual content
            $liked_contents = Content::whereIn('id', $liked_content_ids)
                ->where('status', 'published') // Ensure content is still public
                ->latest()
                ->paginate($per_page);

            return api_response(true, 'Liked content retrieved successfully.', $liked_contents->toArray());
        } catch (Throwable $e) {
            Log::channel('social')->error('Error retrieving liked content for user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    /**
     * Updates the profile information for the authenticated user.
     *
     * @param Request $request
     * @return Response
     */
    public function updateProfile(Request $request): Response
    {
        $user = $request->user;
        $profile_data_updates = $request->post('profile_data');

        if (!is_array($profile_data_updates)) {
            return api_response(false, 'profile_data must be an object.', null, 400);
        }

        try {
            // Merge new data with existing data to avoid overwriting unrelated fields
            $new_profile_data = array_merge($user->profile_data ?? [], $profile_data_updates);
            $user->profile_data = $new_profile_data;
            $user->save();

            Log::channel('auth')->info('User profile updated.', ['user_id' => $user->id]);

            $user->load('role');

            return api_response(true, 'Profile updated successfully.', [
                'user' => $user->only(['id', 'username', 'email', 'role', 'profile_data'])
            ]);
        } catch (Throwable $e) {
            Log::channel('auth')->error('Error updating user profile.', [
                'user_id' => $user->id,
                'error'   => $e->getMessage()
            ]);
            return api_response(false, 'An internal error occurred while updating the profile.', null, 500);
        }
    }
}