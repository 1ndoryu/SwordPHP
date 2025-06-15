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

        $año = date('Y');
        $mes = date('m');
        // CORRECCIÓN 1: Usar la constante SWORD_CONTENT_PATH para la ruta base.
        $directorioBase = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
        $directorioDestino = $directorioBase . DIRECTORY_SEPARATOR . $año . DIRECTORY_SEPARATOR . $mes;

        if (!is_dir($directorioDestino)) {
            // Crear el directorio recursivamente.
            mkdir($directorioDestino, 0755, true);
        }

        $nombreOriginal = pathinfo($archivo->getUploadName(), PATHINFO_FILENAME);
        $titulo = $this->sanitizarTitulo($nombreOriginal);
        $extension = strtolower($archivo->getUploadExtension());
        $nombreArchivo = $this->generarNombreUnico($titulo, $extension, $directorioDestino, $usuarioId);

        $rutaCompleta = $directorioDestino . DIRECTORY_SEPARATOR . $nombreArchivo;

        // Mover el archivo subido a su destino final.
        $archivo->move($rutaCompleta);

        // CORRECCIÓN 2: Normalizar slashes para la ruta relativa y la URL.
        $rutaRelativa = $año . '/' . $mes . '/' . $nombreArchivo;
        $urlPublica = \url_contenido('media/' . $rutaRelativa);

        $media = new Media();
        $media->usuario_id = $usuarioId;
        $media->titulo = $titulo;
        $media->nombre_archivo = $nombreArchivo;
        $media->ruta_archivo = $rutaRelativa; // Guardar con slashes uniformes.
        $media->url_publica = $urlPublica;
        $media->tipo_mime = $archivo->getUploadMimeType();
        
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
