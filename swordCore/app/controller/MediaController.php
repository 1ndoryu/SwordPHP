<?php

namespace App\controller;

use App\model\Media;
use App\service\MediaService;
use support\Request;
use support\Response;

class MediaController
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function index(Request $request)
    {
        // Obtenemos todos los medios, ordenados por fecha de creación descendente
        $mediaItems = Media::orderBy('created_at', 'desc')->get();

        // Pasamos los medios a la vista
        return view('admin/media/index', [
            'name' => 'sword',
            'mediaItems' => $mediaItems
        ]);
    }

    public function subir(Request $request): Response
    {
        try {
            $archivos = $request->file('archivos');

            if (empty($archivos)) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode(['exito' => false, 'mensaje' => 'No se han enviado archivos.']));
            }

            if (!is_array($archivos)) {
                $archivos = [$archivos];
            }

            $usuarioId = idUsuarioActual();
            if (!$usuarioId) {
                return new Response(401, ['Content-Type' => 'application/json'], json_encode(['exito' => false, 'mensaje' => 'No autorizado. Se requiere iniciar sesión.']));
            }

            $resultadosExitosos = [];
            foreach ($archivos as $archivo) {
                $media = $this->mediaService->gestionarSubida($archivo, $usuarioId);
                $resultadosExitosos[] = [
                    'id' => $media->id,
                    'url_publica' => $media->url_publica,
                    'titulo' => $media->titulo,
                    'tipo_mime' => $media->tipo_mime,
                ];
            }

            return new Response(201, ['Content-Type' => 'application/json'], json_encode(['exito' => true, 'media' => $resultadosExitosos]));

        } catch (\Throwable $e) {
            error_log($e);
            return new Response(500, ['Content-Type' => 'application/json'], json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]));
        }
    }

    /**
     * Elimina un medio de la base de datos y el archivo físico.
     */
    public function destroy(Request $request, $id): Response
    {
        try {
            $media = Media::find($id);
            $session = $request->session(); // Obtenemos el objeto de la sesión

            if (!$media) {
                if ($request->expectsJson()) {
                    return new Response(404, ['Content-Type' => 'application/json'], json_encode(['exito' => false, 'mensaje' => 'Medio no encontrado.']));
                }
                // [+] CORREGIDO: Usamos el sistema de sesión de webman
                $session->set('error', 'Medio no encontrado.');
                return redirect('/panel/media');
            }

            // 1. Construir la ruta completa y eliminar el archivo físico.
            $rutaCompleta = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $media->rutaarchivo;
            if (file_exists($rutaCompleta) && is_file($rutaCompleta)) {
                unlink($rutaCompleta);
            }

            // 2. Eliminar el registro de la base de datos.
            $media->delete();
            
            // [+] CORREGIDO: Usamos el sistema de sesión de webman
            $session->set('exito', 'El medio ha sido eliminado correctamente.');
            return redirect('/panel/media');

        } catch (\Throwable $e) {
            error_log("Error al eliminar medio (ID: $id): " . $e->getMessage());
            $session = $request->session();
            if ($request->expectsJson()) {
                return new Response(500, ['Content-Type' => 'application/json'], json_encode(['exito' => false, 'mensaje' => 'Error del servidor al intentar eliminar el medio.']));
            }
            // [+] CORREGIDO: Usamos el sistema de sesión de webman
            $session->set('error', 'Error del servidor al intentar eliminar el medio.');
            return redirect('/panel/media');
        }
    }
}