<?php

namespace App\controller\Api\V1;

use App\controller\Api\ApiBaseController;
use App\service\MediaService;
use support\Request;
use support\Response;
use support\exception\BusinessException;

class MediaApiController extends ApiBaseController
{
    private MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Gestiona la subida de un archivo (imagen o audio).
     * POST /api/v1/media
     */
    public function upload(Request $request): Response
    {
        // El rol 'fan' puede subir media (ej. avatar), pero 'artista' es requerido para crear 'samples'.
        // La lógica de permisos para asociar esta media a un recurso se hace en el controlador de ese recurso.
        // Aquí solo validamos que el usuario esté autenticado.

        $archivo = $request->file('file');

        if (null === $archivo) {
            return $this->respuestaError('No se ha subido ningún archivo. Asegúrese de usar el campo "file".', 400);
        }

        if (!$archivo->isValid()) {
            return $this->respuestaError('El archivo subido no es válido.', 422);
        }

        try {
            $usuarioId = $request->usuario->id;
            $media = $this->mediaService->gestionarSubidaApi($archivo, $usuarioId);

            $fileType = 'other';
            if (str_starts_with($media->tipomime, 'audio/')) {
                $fileType = 'audio';
            } elseif (str_starts_with($media->tipomime, 'image/')) {
                $fileType = 'image';
            }

            $responseData = [
                'id' => $media->id,
                'file_type' => $fileType,
                'mime_type' => $media->tipomime,
                'file_size_bytes' => $media->metadata['tamaño_bytes'] ?? 0,
                'url' => $media->url_publica,
                'created_at' => $media->created_at->toIso8601String(),
            ];

            return $this->respuestaExito($responseData, 201);
        } catch (BusinessException $e) {
            return $this->respuestaError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            \support\Log::error('Error en API al subir archivo: ' . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno al procesar el archivo.', 500);
        }
    }
}
