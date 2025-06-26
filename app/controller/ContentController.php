<?php
// ARCHIVO MODIFICADO: app/controller/ContentController.php

namespace app\controller;

use app\model\Content;
use support\Request;
use support\Response;
use support\Log;
use Throwable;
use Illuminate\Support\Str;

class ContentController
{
    /**
     * Display a paginated listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        try {
            $contents = Content::where('status', 'published')->latest()->paginate(15);
            return json(['success' => true, 'data' => $contents]);
        } catch (Throwable $e) {
            Log::channel('content')->error('Error fetching contents', ['error' => $e->getMessage()]);
            return json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }

    /**
     * Display a paginated listing of all resources for administrators.
     *
     * @param Request $request
     * @return Response
     */
    public function indexAdmin(Request $request): Response
    {
        try {
            // Admin can see all content, regardless of status.
            $contents = Content::latest()->paginate(15);
            Log::channel('content')->info('Admin consultó todos los contenidos', ['user_id' => $request->user->id]);
            return json(['success' => true, 'data' => $contents]);
        } catch (Throwable $e) {
            Log::channel('content')->error('Error fetching all contents for admin', ['error' => $e->getMessage()]);
            return json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param string $slug
     * @return Response
     */
    public function show(Request $request, string $slug): Response
    {
        $content = Content::where('slug', $slug)->where('status', 'published')->first();

        if (!$content) {
            return json(['success' => false, 'message' => 'Content not found.'], 404);
        }

        return json(['success' => true, 'data' => $content]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        $data = $request->post();
        $user = $request->user;

        if (empty($data['content_data']['title'])) {
            return json(['success' => false, 'message' => 'Title is required.'], 400);
        }

        try {
            $slug = Str::slug($data['content_data']['title']);
            $slugCount = Content::where('slug', 'like', $slug . '%')->count();
            if ($slugCount > 0) {
                $slug = $slug . '-' . ($slugCount + 1);
            }

            $content = Content::create([
                'user_id' => $user->id,
                'slug' => $slug,
                'type' => $data['type'] ?? 'post',
                'status' => $data['status'] ?? 'draft',
                'content_data' => $data['content_data'] ?? [],
            ]);

            Log::channel('content')->info('Nuevo contenido creado', ['id' => $content->id, 'user_id' => $user->id]);

            return json(['success' => true, 'message' => 'Content created successfully.', 'data' => $content], 201);
        } catch (Throwable $e) {
            Log::channel('content')->error('Error creating content', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        $content = Content::find($id);
        if (!$content) {
            return json(['success' => false, 'message' => 'Content not found.'], 404);
        }

        // Permitir la acción si el usuario es 'admin' o si es el propietario del contenido.
        if ($request->user->role !== 'admin' && $content->user_id !== $request->user->id) {
            Log::channel('auth')->warning('Intento de modificación no autorizado', [
                'user_id' => $request->user->id,
                'content_id' => $id,
                'owner_id' => $content->user_id
            ]);
            return json(['success' => false, 'message' => 'This action is unauthorized.'], 403);
        }

        try {
            $content->update($request->post());
            Log::channel('content')->info('Contenido actualizado', [
                'id' => $content->id,
                'user_id' => $request->user->id,
                'is_admin' => $request->user->role === 'admin'
            ]);
            return json(['success' => true, 'message' => 'Content updated successfully.', 'data' => $content]);
        } catch (Throwable $e) {
            Log::channel('content')->error('Error updating content', ['error' => $e->getMessage(), 'content_id' => $id]);
            return json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function destroy(Request $request, int $id): Response
    {
        $content = Content::find($id);
        if (!$content) {
            return json(['success' => false, 'message' => 'Content not found.'], 404);
        }

        // Permitir la acción si el usuario es 'admin' o si es el propietario del contenido.
        if ($request->user->role !== 'admin' && $content->user_id !== $request->user->id) {
            Log::channel('auth')->warning('Intento de eliminación no autorizado', [
                'user_id' => $request->user->id,
                'content_id' => $id,
                'owner_id' => $content->user_id
            ]);
            return json(['success' => false, 'message' => 'This action is unauthorized.'], 403);
        }

        try {
            $content->delete();
            Log::channel('content')->warning('Contenido eliminado', [
                'id' => $id,
                'user_id' => $request->user->id,
                'is_admin' => $request->user->role === 'admin'
            ]);
            return response('', 204); // No content
        } catch (Throwable $e) {
            Log::channel('content')->error('Error deleting content', ['error' => $e->getMessage(), 'content_id' => $id]);
            return json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }
}
