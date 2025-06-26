<?php
// ARCHIVO NUEVO: app/controller/MediaController.php

namespace app\controller;

use app\model\Media;
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class MediaController
{
    /**
     * Store a newly uploaded file.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        $file = $request->file('file');

        if (!$file || !$file->isValid()) {
            return json(['success' => false, 'message' => 'No file uploaded or invalid file.'], 400);
        }

        try {
            // Generate a unique path and name for the file.
            $extension = $file->getUploadExtension();
            $newFileName = bin2hex(random_bytes(16)) . '.' . $extension;
            $uploadDir = 'uploads/media';
            $filePath = $uploadDir . '/' . $newFileName;

            // Move the file to the public directory.
            $file->move(public_path($uploadDir) . '/' . $newFileName);

            $media = Media::create([
                'user_id' => $request->user->id,
                'path' => $filePath,
                'mime_type' => $file->getUploadMimeType(),
                'metadata' => [
                    'original_name' => $file->getUploadName(),
                    'size_bytes' => $file->getSize(),
                ]
            ]);

            Log::channel('media')->info('Archivo subido exitosamente', ['id' => $media->id, 'user_id' => $request->user->id, 'path' => $filePath]);

            return json(['success' => true, 'message' => 'File uploaded successfully.', 'data' => $media], 201);
        } catch (Throwable $e) {
            Log::channel('media')->error('Error al subir archivo', ['error' => $e->getMessage(), 'user_id' => $request->user->id]);
            return json(['success' => false, 'message' => 'An internal error occurred during file upload.'], 500);
        }
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
            // Eager load user relationship to show who uploaded the file.
            $media = Media::with('user:id,username')->latest()->paginate(15);
            Log::channel('media')->info('Admin consultó todos los archivos', ['user_id' => $request->user->id]);
            return json(['success' => true, 'data' => $media]);
        } catch (Throwable $e) {
            Log::channel('media')->error('Error fetching all media for admin', ['error' => $e->getMessage()]);
            return json(['success' => false, 'message' => 'An internal error occurred.'], 500);
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
            return json(['success' => false, 'message' => 'Media not found.'], 404);
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

            return response('', 204); // No Content
        } catch (Throwable $e) {
            Log::channel('media')->error('Error deleting media', ['error' => $e->getMessage(), 'media_id' => $id]);
            return json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }
}
