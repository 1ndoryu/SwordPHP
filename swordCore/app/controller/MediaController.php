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
        $mediaItems = Media::orderBy('creado_en', 'desc')->get();

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
}