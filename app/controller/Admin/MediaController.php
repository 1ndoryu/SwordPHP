<?php

namespace app\controller\Admin;

use support\Request;
use app\model\Media;

/**
 * Controlador para la gestion de medios en el panel admin.
 * Implementa visualizacion, upload y eliminacion de archivos.
 */
class MediaController
{
    /**
     * Muestra la libreria de medios con filtros y paginacion.
     */
    public function index(Request $request): string
    {
        $perPage = 24;
        $currentPage = max(1, (int) $request->get('page', 1));
        $mimeFilter = $request->get('type', '');
        $search = trim($request->get('search', ''));

        $query = Media::with('user:id,username')->orderBy('created_at', 'desc');

        if ($mimeFilter) {
            $query->where('mime_type', 'like', $mimeFilter . '%');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('path', 'like', '%' . $search . '%')
                    ->orWhereRaw("metadata->>'original_name' ILIKE ?", ['%' . $search . '%']);
            });
        }

        $total = $query->count();
        $totalPages = (int) ceil($total / $perPage);
        $offset = ($currentPage - 1) * $perPage;

        $medios = $query->skip($offset)->take($perPage)->get();

        $filters = [
            'type' => $mimeFilter,
            'search' => $search,
        ];

        ob_start();
        $title = 'Medios';
        $user = $request->user->username ?? 'Admin';
        $currentRoute = 'media';

        extract([
            'medios' => $medios,
            'total' => $total,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'filters' => $filters,
        ]);

        include app_path('view/admin/pages/media/index.php');
        $content = ob_get_clean();

        ob_start();
        include app_path('view/admin/layouts/layout.php');
        return ob_get_clean();
    }

    /**
     * Procesa la subida de archivos desde el panel admin.
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');

        if (!$file || !$file->isValid()) {
            if ($request->isAjax()) {
                return json(['success' => false, 'message' => 'Archivo invalido o no proporcionado']);
            }
            return redirect('/admin/media?error=invalid_file');
        }

        try {
            $originalName = $file->getUploadName();
            $mimeType = $file->getUploadMimeType();
            $sizeBytes = $file->getSize();

            $extension = $file->getUploadExtension();
            $newFileName = bin2hex(random_bytes(16)) . '.' . $extension;
            $uploadDir = 'uploads/media';
            $filePath = $uploadDir . '/' . $newFileName;

            $destinationDir = public_path($uploadDir);
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0777, true);
            }

            $file->move(public_path($filePath));

            $media = Media::create([
                'user_id' => $request->user->id,
                'path' => $filePath,
                'mime_type' => $mimeType,
                'metadata' => [
                    'original_name' => $originalName,
                    'size_bytes' => $sizeBytes,
                ]
            ]);

            if ($request->isAjax()) {
                return json([
                    'success' => true,
                    'message' => 'Archivo subido correctamente',
                    'media' => $media->toArray()
                ]);
            }

            return redirect('/admin/media?success=uploaded');
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return json(['success' => false, 'message' => 'Error al subir: ' . $e->getMessage()]);
            }
            return redirect('/admin/media?error=upload_failed');
        }
    }

    /**
     * Obtiene informacion de un medio especifico (AJAX).
     */
    public function show(Request $request, int $id)
    {
        $media = Media::with('user:id,username')->find($id);

        if (!$media) {
            return json(['success' => false, 'message' => 'Medio no encontrado'], 404);
        }

        return json([
            'success' => true,
            'media' => $media->toArray()
        ]);
    }

    /**
     * Actualiza los metadatos de un medio.
     */
    public function update(Request $request, int $id)
    {
        $media = Media::find($id);

        if (!$media) {
            return json(['success' => false, 'message' => 'Medio no encontrado'], 404);
        }

        $metadata = $media->metadata ?? [];

        $altText = $request->post('alt_text');
        if ($altText !== null) {
            $metadata['alt_text'] = trim($altText);
        }
        $title = $request->post('title');
        if ($title !== null) {
            $metadata['title'] = trim($title);
        }
        $description = $request->post('description');
        if ($description !== null) {
            $metadata['description'] = trim($description);
        }

        $media->metadata = $metadata;
        $media->save();

        return json([
            'success' => true,
            'message' => 'Metadatos actualizados',
            'media' => $media->toArray()
        ]);
    }

    /**
     * Elimina un medio del sistema.
     */
    public function destroy(Request $request, int $id)
    {
        $media = Media::find($id);

        if (!$media) {
            if ($request->isAjax()) {
                return json(['success' => false, 'message' => 'Medio no encontrado'], 404);
            }
            return redirect('/admin/media?error=not_found');
        }

        try {
            $filePath = public_path($media->path);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $media->delete();

            if ($request->isAjax()) {
                return json(['success' => true, 'message' => 'Medio eliminado correctamente']);
            }

            return redirect('/admin/media?success=deleted');
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
            }
            return redirect('/admin/media?error=delete_failed');
        }
    }

    /**
     * Endpoint para el modal selector de medios (AJAX).
     * Devuelve HTML parcial o JSON segun el parametro format.
     */
    public function selector(Request $request)
    {
        $perPage = 20;
        $currentPage = max(1, (int) $request->get('page', 1));
        $mimeFilter = $request->get('type', 'image');
        $format = $request->get('format', 'json');

        $query = Media::orderBy('created_at', 'desc');

        if ($mimeFilter) {
            $query->where('mime_type', 'like', $mimeFilter . '%');
        }

        $total = $query->count();
        $totalPages = (int) ceil($total / $perPage);
        $offset = ($currentPage - 1) * $perPage;

        $medios = $query->skip($offset)->take($perPage)->get();

        if ($format === 'html') {
            ob_start();
            include app_path('view/admin/components/media-grid.php');
            return response(ob_get_clean());
        }

        return json([
            'success' => true,
            'medios' => $medios->toArray(),
            'pagination' => [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'total' => $total,
            ]
        ]);
    }
}
