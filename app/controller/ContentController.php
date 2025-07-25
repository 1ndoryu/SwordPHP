<?php

namespace app\controller;

use app\model\Content;
use app\model\Like;
use app\Action\CreateContentAction;
use app\traits\HasValidation;
use app\traits\HandlesErrors;
use app\traits\HandlesAuthorization;
use app\config\AppConstants;
use support\Request;
use support\Response;
use Throwable;
use support\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ContentController
{
    use HasValidation, HandlesErrors, HandlesAuthorization;

    /**
     * Display a paginated listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        try {
            $per_page = $this->validatePagination(
                (int) $request->get('per_page', AppConstants::DEFAULT_PER_PAGE),
                AppConstants::MAX_PER_PAGE
            );

            $contents = Content::where('status', AppConstants::STATUS_PUBLISHED)
                ->latest()
                ->paginate($per_page);
                
            return api_response(true, 'Contents retrieved successfully.', $contents->toArray());
        } catch (Throwable $e) {
            return $this->handleError($e, 'content', 'Error fetching contents');
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
            $per_page = $this->validatePagination(
                (int) $request->get('per_page', AppConstants::DEFAULT_PER_PAGE),
                AppConstants::MAX_PER_PAGE
            );

            // Admin can see all content, regardless of status
            $contents = Content::latest()->paginate($per_page);
            
            $this->logSuccess('content', 'Admin consultó todos los contenidos', ['user_id' => $request->user->id]);
            
            return api_response(true, 'All contents retrieved successfully for admin.', $contents->toArray());
        } catch (Throwable $e) {
            return $this->handleError($e, 'content', 'Error fetching all contents for admin');
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
        $content = Content::where('slug', $slug)
            ->where('status', AppConstants::STATUS_PUBLISHED)
            ->first();

        if (!$content) {
            return api_response(false, 'Content not found.', null, 404);
        }

        return api_response(true, 'Content retrieved successfully.', $content->toArray());
    }

    /**
     * Display the specified resource for an administrator, regardless of status.
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function showAdmin(Request $request, int $id): Response
    {
        try {
            $content = Content::find($id);

            if (!$content) {
                Log::channel('content')->info('Admin intentó ver un contenido inexistente', ['id' => $id, 'admin_id' => $request->user->id]);
                return api_response(false, 'Content not found.', null, 404);
            }

            Log::channel('content')->info('Admin vio un contenido específico', ['id' => $id, 'admin_id' => $request->user->id]);
            return api_response(true, 'Admin content retrieved successfully.', $content->toArray());
        } catch (Throwable $e) {
            Log::channel('content')->error('Error en showAdmin', ['error' => $e->getMessage(), 'id' => $id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
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

        // Check authorization using trait
        $auth_error = $this->checkContentModificationAuth($request->user, $content);
        if ($auth_error) {
            return $auth_error;
        }

        try {
            $updates = $request->post();
            $content->update($updates);
            
            $this->logSuccess('content', 'Contenido actualizado', [
                'id' => $content->id,
                'user_id' => $request->user->id,
                'is_admin' => $this->isAdmin($request->user)
            ]);

            // Dispatch internal event
            rabbit_event('content.updated', [
                'id' => $content->id,
                'user_id' => $request->user->id,
                'changes' => $updates
            ]);

            // Jophiel event for audio samples
            if ($content->type === AppConstants::CONTENT_TYPE_AUDIO_SAMPLE && isset($updates['content_data'])) {
                jophielEvento('sample.lifecycle.updated', [
                    'sample_id' => $content->id,
                    'creator_id' => $content->user_id,
                    'metadata' => $content->content_data
                ]);
            }

            return api_response(true, 'Content updated successfully.', $content->toArray());
        } catch (Throwable $e) {
            return $this->handleError($e, 'content', 'Error updating content', ['content_id' => $id]);
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
            $content_id = $content->id;
            $content_type = $content->type; // Guardar datos antes de borrar
            
            $content->delete();
            
            Log::channel('content')->warning('Contenido eliminado', [
                'id' => $id,
                'user_id' => $request->user->id,
                'is_admin' => $request->user->role === 'admin'
            ]);

            // Despachar evento interno
            rabbit_event('content.deleted', [
                'id' => $content_id,
                'user_id' => $request->user->id
            ]);

            // --- INICIO: EVENTO PARA JOPHIEL ---
            if ($content_type === 'audio_sample') {
                jophielEvento('sample.lifecycle.deleted', [
                    'sample_id' => $content_id
                ]);
            }
            // --- FIN: EVENTO PARA JOPHIEL ---

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
        $liked = false; // Nuevo flag para indicar estado final

        try {
            $existing_like = Like::where('content_id', $id)->where('user_id', $user_id)->first();

            if ($existing_like) {
                $existing_like->delete();
                $message = 'Like removed successfully.';
                $liked = false;
                Log::channel('social')->info('Like eliminado', ['content_id' => $id, 'user_id' => $user_id]);
                
                rabbit_event('content.unliked', ['content_id' => $id, 'user_id' => $user_id]);

                // --- INICIO: EVENTO PARA JOPHIEL ---
                if ($content->type === 'audio_sample') {
                    jophielEvento('user.interaction.unlike', [
                        'user_id' => $user_id,
                        'sample_id' => $id
                    ]);
                }
                // --- FIN: EVENTO PARA JOPHIEL ---

            } else {
                Like::create([
                    'content_id' => $id,
                    'user_id' => $user_id,
                ]);
                $message = 'Like added successfully.';
                $liked = true;
                Log::channel('social')->info('Like añadido', ['content_id' => $id, 'user_id' => $user_id]);
                
                rabbit_event('content.liked', ['content_id' => $id, 'user_id' => $user_id]);
                
                // --- INICIO: EVENTO PARA JOPHIEL ---
                if ($content->type === 'audio_sample') {
                    jophielEvento('user.interaction.like', [
                        'user_id' => $user_id,
                        'sample_id' => $id
                    ]);
                }
                // --- FIN: EVENTO PARA JOPHIEL ---
            }

            $like_count = $content->likes()->count();

            return api_response(true, $message, ['like_count' => $like_count, 'liked' => $liked]);
        } catch (Throwable $e) {
            Log::channel('social')->error('Error al dar/quitar like', ['error' => $e->getMessage(), 'content_id' => $id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    /**
     * Find content by a specific hash in its metadata.
     * Only accessible by administrators.
     *
     * @param Request $request
     * @param string $hash
     * @return Response
     */
    public function findByHash(Request $request, string $hash): Response
    {
        try {
            // The query looks for an exact match of the value of 'audio_hash' inside the JSONB field.
            $contents = Content::where('content_data->audio_hash', $hash)->get();

            if ($contents->isEmpty()) {
                Log::channel('content')->info('Búsqueda por hash no encontró resultados.', ['hash' => $hash, 'admin_id' => $request->user->id]);
                return api_response(true, 'Content with specified hash not found.', null);
            }

            Log::channel('content')->info('Búsqueda por hash encontró contenido(s).', ['hash' => $hash, 'count' => $contents->count(), 'admin_id' => $request->user->id]);
            return api_response(true, 'Content with specified hash found.', $contents->toArray());

        } catch (Throwable $e) {
            Log::channel('content')->error('Error en búsqueda por hash', ['error' => $e->getMessage(), 'hash' => $hash]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    /**
     * Filter content by an arbitrary key-value pair in its metadata.
     * Only accessible by administrators.
     *
     * @param Request $request
     * @return Response
     */
    public function filterByData(Request $request): Response
    {
        $key = $request->get('key');
        $value = $request->get('value');

        if (empty($key) || !isset($value)) {
            return api_response(false, 'Both "key" and "value" query parameters are required.', null, 400);
        }

        // Sanitize the key to prevent potential injection issues.
        // Allow only alphanumeric characters and underscores.
        $sanitized_key = preg_replace('/[^A-Za-z0-9_]/', '', $key);
        if ($sanitized_key !== $key) {
             return api_response(false, 'Invalid character in "key". Only alphanumeric and underscores are allowed.', null, 400);
        }

        try {
            $per_page = (int) $request->get('per_page', 15);
            $per_page = min($per_page, 100);

            // Build the query using the sanitized key.
            $query_path = 'content_data->' . $sanitized_key;
            $contents = Content::where($query_path, $value)->latest()->paginate($per_page);

            Log::channel('content')->info('Admin filtró contenidos por content_data', [
                'key' => $sanitized_key,
                'value' => $value,
                'results' => $contents->total(),
                'admin_id' => $request->user->id
            ]);

            return api_response(true, 'Contents filtered successfully.', $contents->toArray());

        } catch (Throwable $e) {
            Log::channel('content')->error('Error en filtrado por content_data', ['error' => $e->getMessage(), 'key' => $key, 'value' => $value]);
            return api_response(false, 'An internal error occurred during filtering.', null, 500);
        }
    }

    /**
     * Retrieve like count for a specific content without mutating state.
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function likes(Request $request, int $id): Response
    {
        $content = Content::find($id);
        if (!$content) {
            return api_response(false, 'Content not found.', null, 404);
        }

        $like_count = $content->likes()->count();

        // Nuevo cálculo de liked tratando de identificar al usuario de forma opcional
        $liked = false;
        $user_id = null;

        if ($request->user) {
            $user_id = $request->user->id;
        } else {
            // Intentar extraer token opcionalmente sin forzar autenticación
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
                try {
                    $decoded = JWT::decode($matches[1], new Key(env('JWT_SECRET'), 'HS256'));
                    $user_id = $decoded->data->id ?? null;
                } catch (\Throwable $e) {
                    // Ignorar errores de token para mantener endpoint público
                }
            }
        }

        if ($user_id) {
            $liked = $content->likes()->where('user_id', $user_id)->exists();
        }

        return api_response(true, 'Like count retrieved successfully.', [
            'like_count' => $like_count,
            'liked' => $liked
        ]);
    }

    // Nuevo método para obtener los IDs de usuarios que dieron like
    public function likeUsers(Request $request, int $id): Response
    {
        $content = Content::find($id);
        if (!$content) {
            return api_response(false, 'Content not found.', null, 404);
        }

        $user_ids = $content->likes()->pluck('user_id')->toArray();
        return api_response(true, 'Liked user IDs retrieved successfully.', [
            'user_ids' => $user_ids
        ]);
    }
}