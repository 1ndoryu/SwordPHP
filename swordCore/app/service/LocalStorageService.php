<?php

namespace App\service;

use Psr\Http\Message\StreamInterface;
use support\Request;
use Webman\Http\UploadFile;

class LocalStorageService implements StorageServiceInterface
{
    public function upload(Request $request, array $data, int $userId): array
    {
        /** @var UploadFile $uploadFile */
        $uploadFile = $data['file'];
        
        // Directorio relativo por fecha (ej: 202506)
        $filePathDir = date('Ym');
        // Ruta completa al directorio de destino dentro de swordContent
        $fullPath = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $filePathDir;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Nombre de archivo único
        $fileName = uniqid() . '.' . $uploadFile->getUploadExtension();
        $uploadFile->move($fullPath . DIRECTORY_SEPARATOR . $fileName);

        // Ruta relativa que se guardará en la BD (ej: 202506/xxxxx.jpg)
        $relativePath = $filePathDir . DIRECTORY_SEPARATOR . $fileName;

        return [
            'provider'        => 'local',
            'path'            => $relativePath,
            'url'             => url_contenido('media/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath)),
            'titulo'          => $data['titulo'],
            'mime_type'       => $uploadFile->getUploadMimeType(),
            'size'            => $uploadFile->getSize(),
            'nombre_original' => $uploadFile->getUploadName(),
        ];
    }

    public function download(string $filePath): StreamInterface
    {
        $fullPath = public_path() . $filePath;
        if (!file_exists($fullPath)) {
            throw new \Exception('File not found.');
        }

        return fopen($fullPath, 'r');
    }
}