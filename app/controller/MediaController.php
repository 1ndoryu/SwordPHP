<?php
// app/controller/MediaController.php

namespace app\controller;

use app\model\Media;
use app\Action\UploadMediaAction;
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class MediaController
{
    /**
     * Store a newly uploaded file by delegating to an action class.
     *
     * @param Request $request
     * @param UploadMediaAction $action
     * @return Response
     */
    public function store(Request $request, UploadMediaAction $action): Response
    {
        return $action($request);
    }

    /**
     * Display the specified media resource.
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function show(Request $request, int $id): Response
    {
        $media = Media::find($id);

        if (!$media) {
            return api_response(false, 'Media not found.', null, 404);
        }

        return api_response(true, 'Media retrieved successfully.', $media->toArray());
    }

    /**
     * Display a listing of the media files for administrators.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        try {
            $per_page = (int) $request->get('per_page', 15);
            $per_page = min($per_page, 100); // Set a max limit of 100 per page

            // Eager load user relationship to show who uploaded the file.
            $media = Media::with('user:id,username')->latest()->paginate($per_page);
            Log::channel('media')->info('Admin consultó todos los archivos', ['user_id' => $request->user->id]);
            return api_response(true, 'Media retrieved successfully.', $media->toArray());
        } catch (Throwable $e) {
            Log::channel('media')->error('Error fetching all media for admin', ['error' => $e->getMessage()]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    /**
     * Remove the specified media from storage and database.
     * Only accessible by administrators.
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function destroy(Request $request, int $id): Response
    {
        $media = Media::find($id);
        if (!$media) {
            return api_response(false, 'Media not found.', null, 404);
        }

        try {
            // Delete the physical file from public storage.
            $filePath = public_path($media->path);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            // Delete the database record.
            $media->delete();

            Log::channel('media')->warning('Archivo eliminado por administrador', [
                'id' => $id,
                'path' => $media->path,
                'admin_id' => $request->user->id
            ]);

            return new Response(204); // No Content
        } catch (Throwable $e) {
            Log::channel('media')->error('Error deleting media', ['error' => $e->getMessage(), 'media_id' => $id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }
}
