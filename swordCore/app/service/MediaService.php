<?php

namespace App\service;

use App\model\Media;
use Webman\Http\UploadFile as File;
use support\exception\BusinessException;

class MediaService
{
    public function crearDesdeApi(array $uploadData, int $userId): Media
    {
        $media = new Media();
        $media->idautor = $userId;
        $media->titulo = $uploadData['titulo'];
        $media->rutaarchivo = $uploadData['path'];
        $media->tipomime = $uploadData['mime_type'];
        $media->metadata = [
            'provider' => $uploadData['provider'],
            'url' => $uploadData['url'],
            'size' => $uploadData['size'],
            'nombre_original' => $uploadData['nombre_original'],
        ];

        $media->save();

        return $media;
    }

    public function obtenerMediaPorId(int $id): Media
    {
        $media = Media::find($id);
        if (!$media) {
            throw new BusinessException('Media not found.');
        }
        return $media;
    }
}