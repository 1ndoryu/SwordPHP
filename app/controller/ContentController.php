<?php

namespace app\controller;

use app\model\Content;
use app\model\Like;
use app\Action\CreateContentAction; // <-- Importar la nueva clase
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class ContentController
{
    /**
     * Display a paginated listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        try {
            $per_page = (int) $request->get('per_page', 15);
            $per_page = min($per_page, 100); // Set a max limit of 100 per page

            $contents = Content::where('status', 'published')->latest()->paginate($per_page);
            return api_response(true, 'Contents retrieved successfully.', $contents->toArray());
        } catch (Throwable $e) {
            Log::channel('content')->error('Error fetching contents', ['error' => $e->getMessage()]);
            return api_response(false, 'An internal error occurred.', null, 500);
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
            $per_page = (int) $request->get('per_page', 15);
            $per_page = min($per_page, 100); // Set a max limit of 100 per page

            // Admin can see all content, regardless of status.
            $contents = Content::latest()->paginate($per_page);
            Log::channel('content')->info('Admin consultó todos los contenidos', ['user_id' => $request->user->id]);
            return api_response(true, 'All contents retrieved successfully for admin.', $contents->toArray());
        } catch (Throwable $e) {
            Log::channel('content')->error('Error fetching all contents for admin', ['error' => $e->getMessage()]);
            return api_response(false, 'An internal error occurred.', null, 500);
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
            return api_response(false, 'Content not found.', null, 404);
        }

        return api_response(true, 'Content retrieved successfully.', $content->toArray());
    }

    /**
     * Store a newly created resource in storage by delegating to an action class.
     *
     * @param Request $request
     * @param CreateContentAction $action
     * @return Response
     */
    public function store(Request $request, CreateContentAction $action): Response
    {
        return $action($request);
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
            return api_response(false, 'Content not found.', null, 404);
        }

        if ($request->user->role !== 'admin' && $content->user_id !== $request->user->id) {
            Log::channel('auth')->warning('Intento de modificación no autorizado', [
                'user_id' => $request->user->id,
                'content_id' => $id,
                'owner_id' => $content->user_id
            ]);
            return api_response(false, 'This action is unauthorized.', null, 403);
        }

        try {
            $content->update($request->post());
            Log::channel('content')->info('Contenido actualizado', [
                'id' => $content->id,
                'user_id' => $request->user->id,
                'is_admin' => $request->user->role === 'admin'
            ]);

            // Despachar evento
            dispatch_event('content.updated', [
                'id' => $content->id,
                'user_id' => $request->user->id,
                'changes' => $request->post()
            ]);

            return api_response(true, 'Content updated successfully.', $content->toArray());
        } catch (Throwable $e) {
            Log::channel('content')->error('Error updating content', ['error' => $e->getMessage(), 'content_id' => $id]);
            return api_response(false, 'An internal error occurred.', null, 500);
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
            return api_response(false, 'Content not found.', null, 404);
        }

        if ($request->user->role !== 'admin' && $content->user_id !== $request->user->id) {
            Log::channel('auth')->warning('Intento de eliminación no autorizado', [
                'user_id' => $request->user->id,
                'content_id' => $id,
                'owner_id' => $content->user_id
            ]);
            return api_response(false, 'This action is unauthorized.', null, 403);
        }

        try {
            $content_id = $content->id; // Guardar id antes de borrar
            $content->delete();
            Log::channel('content')->warning('Contenido eliminado', [
                'id' => $id,
                'user_id' => $request->user->id,
                'is_admin' => $request->user->role === 'admin'
            ]);

            // Despachar evento
            dispatch_event('content.deleted', [
                'id' => $content_id,
                'user_id' => $request->user->id
            ]);

            return new Response(204); // No Content
        } catch (Throwable $e) {
            Log::channel('content')->error('Error deleting content', ['error' => $e->getMessage(), 'content_id' => $id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    /**
     * Toggle a like on a specific content.
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function toggleLike(Request $request, int $id): Response
    {
        $content = Content::find($id);
        if (!$content) {
            return api_response(false, 'Content not found.', null, 404);
        }

        $user_id = $request->user->id;
        $message = '';

        try {
            $existing_like = Like::where('content_id', $id)->where('user_id', $user_id)->first();

            if ($existing_like) {
                $existing_like->delete();
                $message = 'Like removed successfully.';
                Log::channel('social')->info('Like eliminado', ['content_id' => $id, 'user_id' => $user_id]);
                
                // Despachar evento
                dispatch_event('content.unliked', ['content_id' => $id, 'user_id' => $user_id]);
            } else {
                Like::create([
                    'content_id' => $id,
                    'user_id' => $user_id,
                ]);
                $message = 'Like added successfully.';
                Log::channel('social')->info('Like añadido', ['content_id' => $id, 'user_id' => $user_id]);

                // Despachar evento
                dispatch_event('content.liked', ['content_id' => $id, 'user_id' => $user_id]);
            }

            $like_count = $content->likes()->count();

            return api_response(true, $message, ['like_count' => $like_count]);
        } catch (Throwable $e) {
            Log::channel('social')->error('Error al dar/quitar like', ['error' => $e->getMessage(), 'content_id' => $id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }
}