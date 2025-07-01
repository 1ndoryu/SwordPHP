<?php

namespace app\Action;

use app\model\Content;
use app\model\User;
use app\config\AppConstants;
use app\traits\HasValidation;
use app\traits\HandlesErrors;
use Illuminate\Support\Str;
use support\Request;
use support\Response;
use Throwable;

class CreateContentAction
{
    use HasValidation, HandlesErrors;

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
                'type' => $data['type'] ?? AppConstants::DEFAULT_CONTENT_TYPE,
                'status' => $data['status'] ?? AppConstants::DEFAULT_CONTENT_STATUS,
                'content_data' => $data['content_data'] ?? [],
            ]);

            $this->logSuccess('content', 'Nuevo contenido creado vía Action', [
                'id' => $content->id, 
                'user_id' => $user->id
            ]);

            rabbit_event('content.created', [
                'id' => $content->id,
                'slug' => $content->slug,
                'type' => $content->type,
                'user_id' => $user->id
            ]);

            // Notify Casiel for audio samples
            if ($content->type === AppConstants::CONTENT_TYPE_AUDIO_SAMPLE && !empty($data['content_data']['media_id'])) {
                try {
                    casielEvento((int)$content->id, (int)$data['content_data']['media_id']);
                } catch (Throwable $e) {
                    // Log error but don't fail the content creation
                    $this->handleError($e, 'content', 'Fallo al intentar notificar a Casiel', [
                        'content_id' => $content->id
                    ]);
                }
            }

            return api_response(true, 'Content created successfully.', $content->toArray(), 201);
        } catch (Throwable $e) {
            return $this->handleError($e, 'content', 'Error creando contenido vía Action', ['user_id' => $user->id]);
        }
    }
}