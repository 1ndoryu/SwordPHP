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
            // --- INICIO DE LA CORRECCIÓN ---
            // Se obtienen los metadatos del archivo ANTES de moverlo.
            // La llamada a getSize() falla después de mover el archivo temporal.
            $originalName = $file->getUploadName();
            $mimeType = $file->getUploadMimeType();
            $sizeBytes = $file->getSize();
            // --- FIN DE LA CORRECCIÓN ---

            // Generate a unique path and name for the file.
            $extension = $file->getUploadExtension();
            $newFileName = bin2hex(random_bytes(16)) . '.' . $extension;
            $uploadDir = 'uploads/media';
            $filePath = $uploadDir . '/' . $newFileName;
            
            // Asegurar que el directorio de destino exista y sea escribible.
            $destinationDir = public_path($uploadDir);
            if (!is_dir($destinationDir)) {
                // Se crea el directorio recursivamente si no existe.
                mkdir($destinationDir, 0777, true);
            }
            // Se fuerza el permiso de escritura en el directorio para evitar errores de 500.
            @chmod($destinationDir, 0777);

            $file->move(public_path($filePath));

            $media = Media::create([
                'user_id' => $request->user->id,
                'path' => $filePath,
                'mime_type' => $mimeType, // Usar la variable guardada
                'metadata' => [
                    'original_name' => $originalName, // Usar la variable guardada
                    'size_bytes' => $sizeBytes, // Usar la variable guardada
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