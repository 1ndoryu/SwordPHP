<?php

namespace App\service;

use App\model\Media;
use App\model\Usuario;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Servicio para gestionar la lógica de negocio de la biblioteca de medios.
 */
class MediaService
{
    /**
     * @var Media
     */
    protected $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Sanitiza una cadena de texto para ser usada como un "slug".
     *
     * @param string $texto
     * @return string
     */
    private function slugify(string $texto): string
    {
        // Reemplaza todo lo que no sean letras o números por guiones
        $texto = preg_replace('~[^\pL\d]+~u', '-', $texto);
        // Elimina guiones al principio y al final
        $texto = trim($texto, '-');
        // Convierte a minúsculas
        $texto = strtolower($texto);
        // Si queda vacío, devuelve 'archivo'
        if (empty($texto)) {
            return 'archivo';
        }
        return $texto;
    }

    /**
     * Gestiona la subida de un archivo, lo valida, lo mueve y crea un registro en la BD.
     *
     * @param \Webman\Http\UploadFile $archivo El archivo subido.
     * @param int $autorId El ID del usuario que sube el archivo.
     * @return \App\model\Media El objeto Media creado.
     * @throws \Exception Si hay un error en la subida o el archivo no es válido.
     */

    public function gestionarSubida(\Webman\Http\UploadFile $archivo, int $autorId): \App\model\Media
    {
        if (!$archivo->isValid()) {
            throw new \Exception('Error en la subida del archivo, código: ' . $archivo->getUploadErrorCode());
        }

        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'video/mp4', 'image/webp'];
        $tamañoMaximo = 15 * 1024 * 1024; // 15MB

        $mimeType = $archivo->getUploadMimeType();
        if (!in_array($mimeType, $tiposPermitidos)) {
            throw new \Exception('Tipo de archivo no permitido: ' . $mimeType);
        }

        if ($archivo->getSize() > $tamañoMaximo) {
            throw new \Exception('El archivo supera el tamaño máximo permitido de 15MB.');
        }

        $basePath = SWORD_CONTENT_PATH . '/media';
        $baseUrl = rtrim(BASE_URL, '/') . '/swordContent/media';

        $subdirectorioFecha = date('Y/m');
        $directorioDestino = $basePath . '/' . $subdirectorioFecha;

        if (!is_dir($directorioDestino) && !mkdir($directorioDestino, 0755, true)) {
            throw new \Exception("No se pudo crear el directorio de destino: {$directorioDestino}");
        }

        $nombreOriginal = pathinfo($archivo->getUploadName(), PATHINFO_FILENAME);
        $extension = $archivo->getUploadExtension();
        $nombreBaseSlug = $this->slugify($nombreOriginal);

        $nombreArchivo = $nombreBaseSlug . '.' . $extension;
        $contador = 1;
        while (file_exists($directorioDestino . '/' . $nombreArchivo)) {
            $nombreArchivo = $nombreBaseSlug . '-' . $contador . '.' . $extension;
            $contador++;
        }

        $archivo->move($directorioDestino . '/' . $nombreArchivo);

        $rutaRelativa = $subdirectorioFecha . '/' . $nombreArchivo;
        $urlPublica = $baseUrl . '/' . $rutaRelativa;
        $titulo = ucfirst(str_replace(['-', '_'], ' ', $nombreOriginal));

        return $this->media->create([
            'autor_id' => $autorId,
            'titulo' => $titulo,
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo' => $rutaRelativa,
            'url_publica' => $urlPublica,
            'tipo_mime' => $mimeType,
        ]);
    }
}
