<?php

namespace app\controller;

use app\model\Comment;
use app\model\Content;
use app\services\JophielService;
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class CommentController
{
    /**
     * Store a new comment for a specific content.
     *
     * @param Request $request
     * @param integer $content_id
     * @return Response
     */
    public function store(Request $request, int $content_id): Response
    {
        $content = Content::where('id', $content_id)->where('status', 'published')->first();
        if (!$content) {
            return api_response(false, 'Content not found or not published.', null, 404);
        }

        $body = $request->post('body');
        if (empty($body)) {
            return api_response(false, 'Comment body cannot be empty.', null, 400);
        }

        try {
            $comment = Comment::create([
                'content_id' => $content_id,
                'user_id' => $request->user->id,
                'body' => $body,
            ]);

            $comment->load('user:id,username');

            Log::channel('social')->info('Nuevo comentario creado', ['comment_id' => $comment->id, 'user_id' => $request->user->id]);

            // --- INICIO: EVENTO PARA JOPHIEL ---
            if ($content->type === 'audio_sample') {
                jophielEvento('user.interaction.comment', [
                    'user_id' => (int)$request->user->id,
                    'sample_id' => (int)$content->id,
                ]);
            }
            // --- FIN: EVENTO PARA JOPHIEL ---

            return api_response(true, 'Comment posted successfully.', $comment->toArray(), 201);
        } catch (Throwable $e) {
            Log::channel('social')->error('Error al crear comentario', ['error' => $e->getMessage(), 'user_id' => $request->user->id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    /**
     * Delete a specific comment.
     *
     * @param Request $request
     * @param integer $comment_id
     * @return Response
     */
    public function destroy(Request $request, int $comment_id): Response
    {
        $comment = Comment::find($comment_id);
        if (!$comment) {
            return api_response(false, 'Comment not found.', null, 404);
        }

        if ($request->user->role !== 'admin' && $comment->user_id !== $request->user->id) {
            Log::channel('auth')->warning('Intento de eliminación de comentario no autorizado', [
                'user_id' => $request->user->id,
                'comment_id' => $comment_id,
                'owner_id' => $comment->user_id
            ]);
            return api_response(false, 'This action is unauthorized.', null, 403);
        }

        try {
            $comment->delete();
            Log::channel('social')->warning('Comentario eliminado', ['comment_id' => $comment_id, 'user_id' => $request->user->id]);
            return new Response(204); // No Content
        } catch (Throwable $e) {
            Log::channel('social')->error('Error al eliminar comentario', ['error' => $e->getMessage(), 'comment_id' => $comment_id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }
}
