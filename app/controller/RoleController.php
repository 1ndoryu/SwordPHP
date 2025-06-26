<?php

namespace app\controller;

use app\model\Role;
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class RoleController
{
    /**
     * Display a listing of all roles.
     *
     * @return Response
     */
    public function index(): Response
    {
        try {
            $roles = Role::all();
            return api_response(true, 'Roles retrieved successfully.', $roles->toArray());
        } catch (Throwable $e) {
            Log::channel('auth')->error('Error retrieving roles', ['error' => $e->getMessage()]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    /**
     * Store a newly created role in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        $data = $request->post();
        if (empty($data['name'])) {
            return api_response(false, 'Role name is required.', null, 400);
        }

        try {
            $role = Role::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'permissions' => $data['permissions'] ?? [],
            ]);

            Log::channel('auth')->info('Nuevo rol creado', ['role_id' => $role->id, 'name' => $role->name]);
            return api_response(true, 'Role created successfully.', $role->toArray(), 201);
        } catch (Throwable $e) {
            Log::channel('auth')->error('Error creating role', ['error' => $e->getMessage(), 'data' => $data]);
            return api_response(false, 'Could not create role.', null, 500);
        }
    }

    /**
     * Update the specified role in storage.
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        $role = Role::find($id);
        if (!$role) {
            return api_response(false, 'Role not found.', null, 404);
        }

        try {
            $role->update($request->post());
            Log::channel('auth')->info('Rol actualizado', ['role_id' => $id, 'admin_id' => $request->user->id]);
            return api_response(true, 'Role updated successfully.', $role->toArray());
        } catch (Throwable $e) {
            Log::channel('auth')->error('Error updating role', ['error' => $e->getMessage(), 'role_id' => $id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    /**
     * Remove the specified role from storage.
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function destroy(Request $request, int $id): Response
    {
        $role = Role::find($id);
        if (!$role) {
            return api_response(false, 'Role not found.', null, 404);
        }

        if (in_array($role->name, ['admin', 'user'])) {
            return api_response(false, 'Cannot delete default roles.', null, 403);
        }

        // Prevent deletion if role is in use
        if ($role->users()->exists()) {
            return api_response(false, 'Cannot delete role: it is currently assigned to users.', null, 409);
        }

        try {
            $role->delete();
            Log::channel('auth')->warning('Rol eliminado', ['role_id' => $id, 'admin_id' => $request->user->id]);
            return new Response(204); // No Content
        } catch (Throwable $e) {
            Log::channel('auth')->error('Error deleting role', ['error' => $e->getMessage(), 'role_id' => $id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }
}
