<?php

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

/**
 * Middleware de seguridad para servir archivos estáticos.
 * * Este middleware intercepta las solicitudes a archivos estáticos, especialmente
 * dentro del directorio /swordContent/, para evitar la exposición de archivos sensibles.
 */
class StaticFile implements MiddlewareInterface
{
    /**
     * Procesa una solicitud entrante.
     *
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        $path = $request->path();

        // Prohíbe el acceso a cualquier archivo o directorio que comience con un punto (ej. .env, .git).
        if (strpos($path, '/.') !== false) {
            return response('<h1>403 Forbidden</h1>', 403);
        }

        // Aplica reglas de seguridad solo para rutas que intentan acceder a /swordContent/
        if (str_starts_with($path, '/swordContent/')) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            // Lista blanca estricta de extensiones permitidas. Todo lo no listado será denegado.
            $allowedExtensions = [
                // Estilos
                'css',
                // Scripts
                'js',
                // Imágenes
                'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico',
                // Fuentes
                'woff', 'woff2', 'ttf', 'otf', 'eot',
                // Documentos y otros tipos de media
                'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
                'mp3', 'mp4', 'webm', 'ogg', 'wav',
            ];

            // Si la extensión solicitada no está en nuestra lista blanca, denegamos el acceso.
            if (!in_array($extension, $allowedExtensions)) {
                // Opcional: Registrar el intento de acceso a un archivo prohibido.
                support\Log::warning("Acceso denegado a archivo sensible: {$path}");
                return response('<h1>403 Forbidden</h1>', 403);
            }
        }

        // Si la solicitud pasa todas las validaciones de seguridad,
        // se la pasamos al siguiente manejador, que será el servidor de archivos estáticos de Webman.
        return $handler($request);
    }
}