<?php

namespace app\Action;

use app\model\Content;
use app\model\User;
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

        // 1. Lógica de Validación centralizada
        if (empty($data['content_data']['title'])) {
            return api_response(false, 'Title is required.', null, 400);
        }

        // 2. Lógica de Creación
        try {
            $slug = Str::slug($data['content_data']['title']);
            // Asegurar slug único
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

            // Despachar evento
            dispatch_event('content.created', [
                'id' => $content->id,
                'slug' => $content->slug,
                'type' => $content->type,
                'status' => $content->status,
                'user_id' => $user->id,
                'title' => $content->content_data['title'] ?? ''
            ]);

            return api_response(true, 'Content created successfully.', $content->toArray(), 201);
        } catch (Throwable $e) {
            Log::channel('content')->error('Error creando contenido vía Action', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }
}