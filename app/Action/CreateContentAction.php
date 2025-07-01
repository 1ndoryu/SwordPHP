<?php

namespace app\Action;

use app\model\Content;
use app\model\User;
use app\services\CasielService; 
use Illuminate\Support\Str;
use support\Request;
use support\Response;
use Throwable;
use support\Log;

class CreateContentAction
{
    /**
     * Handles the HTTP request from the controller.
     * Extracts data and delegates to the core business logic.
     *
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $data = $request->post();
        $user = $request->user;
        return $this->execute($user, $data);
    }

    /**
     * Core business logic for creating content.
     * Decoupled from the HTTP Request object for better reusability and testing.
     *
     * @param User $user The user creating the content.
     * @param array $data The content data.
     * @return Response
     */
    public function execute(User $user, array $data): Response
    {
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

            rabbit_event('content.created', [
                'id' => $content->id,
                'slug' => $content->slug,
                'type' => $content->type,
                'user_id' => $user->id
            ]);

            if ($content->type === 'audio_sample' && !empty($data['content_data']['media_id'])) {
                try {
                    casielEvento((int)$content->id, (int)$data['content_data']['media_id']);
                } catch (Throwable $e) {
                    Log::channel('content')->error('Fallo al intentar notificar a Casiel', [
                        'content_id' => $content->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return api_response(true, 'Content created successfully.', $content->toArray(), 201);
        } catch (Throwable $e) {
            Log::channel('content')->error('Error creando contenido vía Action', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }
}