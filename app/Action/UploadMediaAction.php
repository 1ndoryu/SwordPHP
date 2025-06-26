<?php

namespace app\Action;

use app\model\Media;
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class UploadMediaAction
{
    /**
     * Validates the request and stores a new media file.
     *
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $file = $request->file('file');

        if (!$file || !$file->isValid()) {
            return api_response(false, 'No file uploaded or invalid file.', null, 400);
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

            Log::channel('media')->info('Archivo subido exitosamente vía Action', ['id' => $media->id, 'user_id' => $request->user->id, 'path' => $filePath]);

            return api_response(true, 'File uploaded successfully.', $media->toArray(), 201);
        } catch (Throwable $e) {
            Log::channel('media')->error('Error al subir archivo vía Action', ['error' => $e->getMessage(), 'user_id' => $request->user->id]);

            $errorMessage = 'An internal error occurred during file upload.';
            if (env('APP_DEBUG', false)) {
                $errorMessage = $e->getMessage();
            }
            return api_response(false, $errorMessage, null, 500);
        }
    }
}
