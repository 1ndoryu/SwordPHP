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

        // --- INICIO DE LA CORRECCIÓN ---
        // 1. Obtener todos los datos ANTES de mover el archivo.
        $originalName = $uploadFile->getUploadName();
        $mimeType = $uploadFile->getUploadMimeType();
        $size = $uploadFile->getSize();
        // --- FIN DE LA CORRECCIÓN ---

        // Directorio relativo por fecha (ej: 202506)
        $filePathDir = date('Ym');
        // Ruta completa al directorio de destino dentro de swordContent
        $fullPath = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $filePathDir;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Nombre de archivo único
        $fileName = uniqid() . '.' . $uploadFile->getUploadExtension();
        
        // 2. Mover el archivo a su destino final.
        $uploadFile->move($fullPath . DIRECTORY_SEPARATOR . $fileName);

        // Ruta relativa que se guardará en la BD (ej: 202506/xxxxx.jpg)
        $relativePath = $filePathDir . DIRECTORY_SEPARATOR . $fileName;

        // 3. Devolver los datos que ya hemos recopilado.
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
        // La ruta completa debe construirse desde SWORD_CONTENT_PATH para consistencia.
        $fullPath = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $filePath;
        if (!file_exists($fullPath)) {
            throw new \Exception('Archivo no encontrado.');
        }

        return fopen($fullPath, 'r');
    }
}