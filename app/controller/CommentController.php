<?php

namespace app\controller;

use app\model\Comment;
use app\model\Content;
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
        if (!Content::where('id', $content_id)->where('status', 'published')->exists()) {
            return json(['success' => false, 'message' => 'Content not found or not published.'], 404);
        }

        $body = $request->post('body');
        if (empty($body)) {
            return json(['success' => false, 'message' => 'Comment body cannot be empty.'], 400);
        }

        try {
            $comment = Comment::create([
                'content_id' => $content_id,
                'user_id' => $request->user->id,
                'body' => $body,
            ]);

            // Cargar la relación del usuario para devolverla en la respuesta.
            $comment->load('user:id,username');

            // --- Log modificado al canal 'social' ---
            Log::channel('social')->info('Nuevo comentario creado', ['comment_id' => $comment->id, 'user_id' => $request->user->id]);

            return json(['success' => true, 'message' => 'Comment posted successfully.', 'data' => $comment], 201);
        } catch (Throwable $e) {
            // --- Log modificado al canal 'social' ---
            Log::channel('social')->error('Error al crear comentario', ['error' => $e->getMessage(), 'user_id' => $request->user->id]);
            return json(['success' => false, 'message' => 'An internal error occurred.'], 500);
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
            return json(['success' => false, 'message' => 'Comment not found.'], 404);
        }

        // Authorization: Only the owner or an admin can delete the comment.
        if ($request->user->role !== 'admin' && $comment->user_id !== $request->user->id) {
            Log::channel('auth')->warning('Intento de eliminación de comentario no autorizado', [
                'user_id' => $request->user->id,
                'comment_id' => $comment_id,
                'owner_id' => $comment->user_id
            ]);
            return json(['success' => false, 'message' => 'This action is unauthorized.'], 403);
        }

        try {
            $comment->delete();
            // --- Log modificado al canal 'social' ---
            Log::channel('social')->warning('Comentario eliminado', ['comment_id' => $comment_id, 'user_id' => $request->user->id]);
            return response('', 204); // No Content
        } catch (Throwable $e) {
            // --- Log modificado al canal 'social' ---
            Log::channel('social')->error('Error al eliminar comentario', ['error' => $e->getMessage(), 'comment_id' => $comment_id]);
            return json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }
}