<?php

namespace App\controller\Api\V1;

use App\controller\Api\ApiBaseController;
use App\service\CasielStorageService;
use App\service\ExternalStorageService;
use App\service\LocalStorageService;
use App\service\MediaService;
use App\service\StorageServiceInterface;
use support\Request;
use support\Response;

class MediaApiController extends ApiBaseController
{
    private MediaService $mediaService;
    private StorageServiceInterface $storageService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function upload(Request $request): Response
    {
        $provider = $request->post('storage_provider', 'local');
        $file = $request->file('file');
        $url = $request->post('url');
        $titulo = $request->post('titulo');

        $this->setStorageProvider($provider);

        $data = [];

        if ($provider === 'external') {
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                return $this->respuestaError('Se requiere una URL válida para el proveedor externo.', 422);
            }
            $data = ['url' => $url];
            if (empty($titulo)) {
                $path_parts = pathinfo($url);
                $titulo = $path_parts['filename'];
            }
        } else {
            if (!$file || !$file->isValid()) {
                return $this->respuestaError('No se ha subido ningún archivo o el archivo no es válido.', 422);
            }
            $data = ['file' => $file];
            if (empty($titulo)) {
                $titulo = $file->getUploadName();
            }
        }
        
        $data['titulo'] = $titulo;

        try {
            $uploadData = $this->storageService->upload($request, $data, $request->usuario->id);
            $media = $this->mediaService->crearDesdeApi($uploadData, $request->usuario->id);
            return $this->respuestaExito($media, 201);
        } catch (\Exception $e) {
            return $this->respuestaError('Error al procesar el archivo: ' . $e->getMessage(), 500);
        }
    }

    public function download(Request $request, int $id): Response
    {
        try {
            $media = $this->mediaService->obtenerMediaPorId($id);

            // [CORRECCIÓN] Obtener el proveedor desde los metadatos.
            $provider = $media->obtenerMeta('provider');
            if (!$provider) {
                throw new \Exception("El proveedor de almacenamiento no está definido para el medio con ID: $id.");
            }
            $this->setStorageProvider($provider);

            // [CORRECCIÓN] Obtener la ruta del archivo y el nombre original.
            // La ruta del archivo está en la columna 'rutaarchivo'.
            $filePath = $media->rutaarchivo;
            // El nombre original está en los metadatos.
            $originalName = $media->obtenerMeta('nombre_original', basename($filePath));

            if (!$filePath) {
                throw new \Exception("La ruta del archivo no está definida para el medio con ID: $id.");
            }
            
            $stream = $this->storageService->download($filePath);

            return new Response(200, [
                'Content-Type' => $media->tipomime,
                'Content-Disposition' => 'attachment; filename="' . $originalName . '"',
            ], $stream);
        } catch (\Exception $e) {
            \support\Log::error("Error en MediaApiController@download para ID $id: " . $e->getMessage());
            return $this->respuestaError('Error al descargar el archivo: ' . $e->getMessage(), 500);
        }
    }

    private function setStorageProvider(string $provider): void
    {
        switch ($provider) {
            case 'casiel':
                $this->storageService = new CasielStorageService();
                break;
            case 'external':
                $this->storageService = new ExternalStorageService();
                break;
            case 'local':
            default:
                $this->storageService = new LocalStorageService();
                break;
        }
    }
}