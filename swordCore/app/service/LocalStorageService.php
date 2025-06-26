<?php

namespace App\service;

use Psr\Http\Message\StreamInterface;
use support\Request;
use Webman\Http\UploadFile;
use Laminas\Diactoros\Stream; // Asegúrate de tener esta dependencia (laminas/laminas-diactoros)

class LocalStorageService implements StorageServiceInterface
{
    public function upload(Request $request, array $data, int $userId): array
    {
        /** @var UploadFile $uploadFile */
        $uploadFile = $data['file'];

        $originalName = $uploadFile->getUploadName();
        $mimeType = $uploadFile->getUploadMimeType();
        $size = $uploadFile->getSize();

        $filePathDir = date('Ym');
        $fullPath = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $filePathDir;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        $fileName = uniqid() . '.' . $uploadFile->getUploadExtension();
        
        $uploadFile->move($fullPath . DIRECTORY_SEPARATOR . $fileName);

        $relativePath = $filePathDir . DIRECTORY_SEPARATOR . $fileName;

        return [
            'provider'        => 'local',
            'path'            => $relativePath,
            'url'             => url_contenido('media/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath)),
            'titulo'          => $data['titulo'],
            'mime_type'       => $mimeType,
            'size'            => $size,
            'nombre_original' => $originalName,
        ];
    }

    public function download(string $filePath): StreamInterface
    {
        $fullPath = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $filePath;
        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            throw new \Exception("Archivo no encontrado en la ruta: $fullPath");
        }

        // --- ESTA ES LA CORRECCIÓN CLAVE ---
        // 1. Abrimos el archivo, lo que nos da un 'resource'.
        $resource = fopen($fullPath, 'r');
        if ($resource === false) {
            throw new \RuntimeException("No se pudo abrir el stream para el archivo: $fullPath");
        }

        // 2. Envolvemos el 'resource' en un objeto Stream que cumple con PSR-7.
        return new Stream($resource);
    }
}