<?php

namespace App\service;

use App\model\Media;
use Webman\Http\UploadFile as File;
use support\exception\BusinessException;

class MediaService
{
    /**
     * Gestiona la subida de un archivo desde la API, aplicando validaciones específicas.
     *
     * @param File $archivo El archivo subido.
     * @param int $usuarioId El ID del usuario autenticado.
     * @return Media El modelo del medio creado.
     * @throws BusinessException Si el archivo no cumple las validaciones.
     */
    public function gestionarSubidaApi(File $archivo, int $usuarioId): Media
    {
        $mimeType = $this->obtenerMimeTypeReal($archivo);
        $fileSize = $archivo->getSize();

        // 1. Validar tipo de archivo (MIME)
        $isImage = str_starts_with($mimeType, 'image/');
        $isAudio = str_starts_with($mimeType, 'audio/');

        if (!$isImage && !$isAudio) {
            throw new BusinessException("Tipo de archivo no permitido ({$mimeType}). Solo se admiten imágenes y audios.");
        }

        // 2. Validar tamaño del archivo
        $maxSizeImage = 10 * 1024 * 1024; // 10MB
        $maxSizeAudio = 60 * 1024 * 1024; // 60MB

        if ($isImage && $fileSize > $maxSizeImage) {
            throw new BusinessException("La imagen excede el tamaño máximo permitido de 10MB.");
        }

        if ($isAudio && $fileSize > $maxSizeAudio) {
            throw new BusinessException("El audio excede el tamaño máximo permitido de 60MB.");
        }

        // 3. Si pasa las validaciones, proceder con el guardado
        return $this->guardarArchivo($archivo, $usuarioId, $mimeType, $fileSize);
    }

    /**
     * Método original para subidas desde el panel de admin (sin restricciones de API).
     */
    public function gestionarSubida(File $archivo, int $usuarioId): Media
    {
        if (!$archivo->isValid()) {
            throw new \Exception('El archivo no es válido.');
        }

        $mimeType = $this->obtenerMimeTypeReal($archivo);
        $fileSize = $archivo->getSize();

        return $this->guardarArchivo($archivo, $usuarioId, $mimeType, $fileSize);
    }
    
    /**
     * Lógica centralizada para guardar el archivo en disco y registrarlo en la BD.
     */
    private function guardarArchivo(File $archivo, int $usuarioId, string $mimeType, int $fileSize): Media
    {
        $año = date('Y');
        $mes = date('m');
        $directorioBase = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
        $directorioDestino = $directorioBase . DIRECTORY_SEPARATOR . $año . DIRECTORY_SEPARATOR . $mes;

        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }

        $nombreOriginal = pathinfo($archivo->getUploadName(), PATHINFO_FILENAME);
        $extension = strtolower($archivo->getUploadExtension());
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

    private function obtenerMimeTypeReal(File $archivo): string
    {
        $mimeType = $archivo->getUploadMimeType();
        if (empty($mimeType) || $mimeType === 'application/octet-stream') {
            $extensionToMimeMap = [
                'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
                'png'  => 'image/png',  'gif'  => 'image/gif',
                'webp' => 'image/webp', 'svg'  => 'image/svg+xml',
                'ico'  => 'image/x-icon', 'wav' => 'audio/wav',
                'mp3' => 'audio/mpeg', 'ogg' => 'audio/ogg',
            ];
            $extension = strtolower($archivo->getUploadExtension());
            return $extensionToMimeMap[$extension] ?? 'application/octet-stream';
        }
        return $mimeType;
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