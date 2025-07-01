<?php

namespace app\traits;

use app\model\User;
use app\model\Content;
use support\Response;

trait HandlesAuthorization
{
    /**
     * Checks if a user can modify specific content.
     *
     * @param User $user The user attempting the operation
     * @param Content $content The content to be modified
     * @return bool True if authorized, false otherwise
     */
    protected function canModifyContent(User $user, Content $content): bool
    {
        return $user->role->name === 'admin' || $content->user_id === $user->id;
    }

    /**
     * Returns an unauthorized response with consistent logging.
     *
     * @param User $user The user attempting the operation
     * @param int $content_id The content ID
     * @param int|null $owner_id The content owner ID
     * @return Response
     */
    protected function unauthorizedResponse(User $user, int $content_id, ?int $owner_id = null): Response
    {
        $this->logSecurityWarning('Intento de modificación no autorizado', [
            'user_id' => $user->id,
            'content_id' => $content_id,
            'owner_id' => $owner_id
        ]);

        return api_response(false, 'This action is unauthorized.', null, 403);
    }

    /**
     * Checks if user has admin role.
     *
     * @param User $user
     * @return bool
     */
    protected function isAdmin(User $user): bool
    {
        return $user->role->name === 'admin';
    }

    /**
     * Checks authorization and returns error response if unauthorized.
     *
     * @param User $user The user attempting the operation
     * @param Content $content The content to be modified
     * @return Response|null Returns error response if unauthorized, null if authorized
     */
    protected function checkContentModificationAuth(User $user, Content $content): ?Response
    {
        if (!$this->canModifyContent($user, $content)) {
            return $this->unauthorizedResponse($user, $content->id, $content->user_id);
        }
        
        return null;
    }
}