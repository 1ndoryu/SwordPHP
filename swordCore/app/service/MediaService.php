<?php

namespace App\service;

use App\model\Media;
use Webman\Http\UploadFile as File;

class MediaService
{
    public function gestionarSubida(File $archivo, int $usuarioId): Media
    {
        if (!$archivo->isValid()) {
            throw new \Exception('El archivo no es válido.');
        }

        $fileSize = $archivo->getSize();
        $extension = strtolower($archivo->getUploadExtension());

        // Lógica mejorada para obtener el MIME type
        $mimeType = $archivo->getUploadMimeType();
        if (empty($mimeType) || $mimeType === 'application/octet-stream') {
            $extensionToMimeMap = [
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                'svg'  => 'image/svg+xml',
                'ico'  => 'image/x-icon',
            ];
            $mimeType = $extensionToMimeMap[$extension] ?? 'application/octet-stream';
        }

        $año = date('Y');
        $mes = date('m');
        $directorioBase = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
        $directorioDestino = $directorioBase . DIRECTORY_SEPARATOR . $año . DIRECTORY_SEPARATOR . $mes;

        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }

        $nombreOriginal = pathinfo($archivo->getUploadName(), PATHINFO_FILENAME);
        $titulo = $this->sanitizarTitulo($nombreOriginal);
        $nombreArchivo = $this->generarNombreUnico($titulo, $extension, $directorioDestino, $usuarioId);

        $rutaCompleta = $directorioDestino . DIRECTORY_SEPARATOR . $nombreArchivo;
        $archivo->move($rutaCompleta);
        $rutaRelativa = $año . '/' . $mes . '/' . $nombreArchivo;

        $media = new Media();
        $media->idautor = $usuarioId;
        $media->titulo = $titulo;
        $media->rutaarchivo = $rutaRelativa;
        $media->tipomime = $mimeType;
        $media->metadata = [
            'tamaño_bytes' => $fileSize,
            'nombre_original' => $archivo->getUploadName()
        ];

        $media->save();

        return $media;
    }

    private function sanitizarTitulo(string $titulo): string
    {
        $titulo = preg_replace('/[^a-zA-Z0-9\s-]/', '', $titulo);
        $titulo = str_replace(' ', '-', $titulo);
        return strtolower($titulo);
    }

    private function generarNombreUnico(string $titulo, string $extension, string $directorio, int $usuarioId): string
    {
        $nombreBase = $titulo . '-' . $usuarioId;
        $nombreArchivo = $nombreBase . '.' . $extension;
        $contador = 1;
        while (file_exists($directorio . DIRECTORY_SEPARATOR . $nombreArchivo)) {
            $nombreArchivo = $nombreBase . '-' . $contador . '.' . $extension;
            $contador++;
        }
        return $nombreArchivo;
    }
}
