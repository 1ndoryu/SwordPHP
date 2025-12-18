<?php

namespace app\services;

use app\model\Content;
use app\model\Like;
use app\config\AppConstants;
use support\Log;
use Throwable;

/**
 * Servicio de lógica de negocio para Likes/Interacciones sociales.
 */
class LikeService
{
    /**
     * Alterna el estado de like (toggle) para un contenido y usuario.
     * Maneja la creación/eliminación del like y el despacho de eventos.
     *
     * @param int $contentId ID del contenido
     * @param int $userId ID del usuario
     * @return array Resultado de la operación ['liked' => bool, 'message' => string, 'count' => int]
     * @throws Throwable Si ocurre un error interno
     */
    public function toggleLike(int $contentId, int $userId): array
    {
        $content = Content::find($contentId);
        if (!$content) {
            throw new \RuntimeException('Content not found', 404);
        }

        $existingLike = Like::where('content_id', $contentId)
            ->where('user_id', $userId)
            ->first();

        $liked = false;
        $message = '';

        if ($existingLike) {
            // Eliminar like
            $existingLike->delete();
            $message = 'Like removed successfully.';
            $liked = false;

            $this->logLikeAction('unliked', $contentId, $userId);
            rabbit_event('content.unliked', ['content_id' => $contentId, 'user_id' => $userId]);

            // Evento Jophiel específico para audio samples
            if ($content->type === AppConstants::CONTENT_TYPE_AUDIO_SAMPLE) {
                jophielEvento('user.interaction.unlike', [
                    'user_id' => $userId,
                    'sample_id' => $contentId
                ]);
            }
        } else {
            // Crear like
            Like::create([
                'content_id' => $contentId,
                'user_id' => $userId,
            ]);
            $message = 'Like added successfully.';
            $liked = true;

            $this->logLikeAction('liked', $contentId, $userId);
            rabbit_event('content.liked', ['content_id' => $contentId, 'user_id' => $userId]);

            if ($content->type === AppConstants::CONTENT_TYPE_AUDIO_SAMPLE) {
                jophielEvento('user.interaction.like', [
                    'user_id' => $userId,
                    'sample_id' => $contentId
                ]);
            }
        }

        // Obtener nuevo conteo
        $likeCount = $content->likes()->count();

        return [
            'liked' => $liked,
            'message' => $message,
            'like_count' => $likeCount
        ];
    }

    /**
     * Obtiene la información de likes de un contenido.
     *
     * @param int $contentId ID del contenido
     * @param int|null $userId ID del usuario actual (opcional) para verificar si dio like
     * @return array ['like_count' => int, 'liked' => bool]
     */
    public function getLikeStatus(int $contentId, ?int $userId = null): array
    {
        $content = Content::find($contentId);
        if (!$content) {
            throw new \RuntimeException('Content not found', 404);
        }

        $likeCount = $content->likes()->count();
        $liked = false;

        if ($userId) {
            $liked = $content->likes()->where('user_id', $userId)->exists();
        }

        return [
            'like_count' => $likeCount,
            'liked' => $liked
        ];
    }

    /**
     * Obtiene los IDs de los usuarios que dieron like a un contenido.
     *
     * @param int $contentId ID del contenido
     * @return array Lista de IDs de usuarios
     */
    public function getLikeUserIds(int $contentId): array
    {
        $content = Content::find($contentId);
        if (!$content) {
            throw new \RuntimeException('Content not found', 404);
        }

        return $content->likes()->pluck('user_id')->toArray();
    }

    /**
     * Helper privado para logging
     */
    private function logLikeAction(string $action, int $contentId, int $userId): void
    {
        Log::channel('social')->info("Contenido {$action}", [
            'content_id' => $contentId,
            'user_id' => $userId
        ]);
    }
}
