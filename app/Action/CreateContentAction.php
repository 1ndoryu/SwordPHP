<?php

namespace app\Action;

use app\model\Content;
use app\services\CasielService; // <-- AÑADIR IMPORT
use Illuminate\Support\Str;
use support\Request;
use support\Response;
use Throwable;
use support\Log;

class CreateContentAction
{
    /**
     * Valida los datos y crea un nuevo contenido.
     *
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $data = $request->post();
        $user = $request->user;

        if (empty($data['content_data']['title'])) {
            return api_response(false, 'Title is required.', null, 400);
        }

        try {
            $slug = Str::slug($data['content_data']['title']);
            if (Content::where('slug', $slug)->exists()) {
                $slug .= '-' . time();
            }

            $content = Content::create([
                'user_id' => $user->id,
                'slug' => $slug,
                'type' => $data['type'] ?? 'post',
                'status' => $data['status'] ?? 'draft',
                'content_data' => $data['content_data'] ?? [],
            ]);

            Log::channel('content')->info('Nuevo contenido creado vía Action', ['id' => $content->id, 'user_id' => $user->id]);

            // Despachar evento interno (para webhooks de Sword, etc.)
            // Se añade media_id para que esté disponible en los webhooks si es necesario.
            dispatch_event('content.created', [
                'id' => $content->id,
                'slug' => $content->slug,
                'type' => $content->type,
                'status' => $content->status,
                'user_id' => $user->id,
                'title' => $content->content_data['title'] ?? '',
                'media_id' => $data['content_data']['media_id'] ?? null
            ]);

            // --- INICIO: NOTIFICACIÓN A CASIEL ---
            // Si el contenido es un 'audio_sample' y tiene un 'media_id', notificar a Casiel.
            if ($content->type === 'audio_sample' && !empty($data['content_data']['media_id'])) {
                try {
                    $casielService = new CasielService();
                    $casielService->notifyNewAudio((int)$content->id, (int)$data['content_data']['media_id']);
                } catch (Throwable $e) {
                    // La notificación a servicios externos no debe romper la operación principal.
                    // Solo se registra el error.
                    Log::channel('content')->error('Fallo al intentar notificar a Casiel', [
                        'content_id' => $content->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            // --- FIN: NOTIFICACIÓN A CASIEL ---

            return api_response(true, 'Content created successfully.', $content->toArray(), 201);
        } catch (Throwable $e) {
            Log::channel('content')->error('Error creando contenido vía Action', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }
}