<?php

namespace App\controller\Api\V1;

use App\controller\Api\ApiBaseController;
use App\service\PaginaService;
use App\service\MediaService; // AÑADIDO
use App\service\SwordQuery;
use support\Request;
use support\Response;
use Webman\Exception\NotFoundException;
use Illuminate\Database\Capsule\Manager as DB;
use App\model\Pagina;
use support\exception\BusinessException; // AÑADIDO
use Throwable; // AÑADIDO

class ContentApiController extends ApiBaseController
{
    private PaginaService $paginaService;
    private MediaService $mediaService; // AÑADIDO

    public function __construct(PaginaService $paginaService, MediaService $mediaService) // MODIFICADO
    {
        $this->paginaService = $paginaService;
        $this->mediaService = $mediaService; // AÑADIDO
    }

    public function index(Request $request): Response
    {
        $perPage = (int) $request->get('per_page', 10);
        $currentPage = (int) $request->get('page', 1);

        $args = [
            'post_type' => $request->get('type', 'pagina'),
            'posts_per_page' => $perPage,
            'paged' => $currentPage,
            'post_status' => $request->get('status', 'publicado'),
            'include' => $request->get('include', ''),
            'q' => $request->get('q', ''),
            'sort_by' => $request->get('sort_by', 'created_at'),
            'order' => $request->get('order', 'desc'),
            'id_autor' => $request->get('id_autor'),
        ];

        $metadataFilters = $request->get('metadata');
        if (is_array($metadataFilters)) {
            $args['meta_query'] = [];
            foreach ($metadataFilters as $key => $value) {
                $args['meta_query'][] = ['key' => $key, 'value' => $value, 'compare' => '='];
            }
        }

        $query = new SwordQuery($args);

        $items = collect($query->entradas)->map(function (Pagina $item) {
            $attributes = $item->toArray();
            if (array_key_exists('idautor', $attributes)) {
                $attributes['id_autor'] = $attributes['idautor'];
                unset($attributes['idautor']);
            }
            return $attributes;
        });

        $paginatedData = [
            'items' => $items,
            'pagination' => [
                'total_items' => $query->totalEntradas,
                'total_pages' => ($query->totalEntradas > 0) ? (int) ceil($query->totalEntradas / $perPage) : 0,
                'current_page' => $currentPage,
                'per_page' => $perPage,
            ]
        ];

        return $this->respuestaExito($paginatedData);
    }

    public function show(Request $request, int $id): Response
    {
        try {
            $include = $request->get('include', '');
            $pagina = $this->paginaService->obtenerPaginaPorId($id, is_string($include) ? explode(',', $include) : []);

            if ($pagina->estado !== 'publicado') {
                return $this->respuestaError('Recurso no encontrado o no disponible.', 404);
            }

            $attributes = $pagina->toArray();
            if (array_key_exists('idautor', $attributes)) {
                $attributes['id_autor'] = $attributes['idautor'];
                unset($attributes['idautor']);
            }
            return $this->respuestaExito($attributes);
        } catch (NotFoundException) {
            return $this->respuestaError('Recurso no encontrado.', 404);
        }
    }

    public function store(Request $request): Response
    {
        $data = $request->post();
        $usuario = $request->usuario;

        if (empty($data['titulo']) || empty($data['tipocontenido'])) {
            return $this->respuestaError('Los campos "titulo" y "tipocontenido" son obligatorios.', 422);
        }

        if ($data['tipocontenido'] === 'sample' && !in_array($usuario->rol, ['admin', 'artista'])) {
            return $this->respuestaError('No tienes permiso para crear samples.', 403);
        }

        $data['idautor'] = $usuario->id;
        $data['estado'] = $data['estado'] ?? 'borrador';

        try {
            $nuevaPagina = $this->paginaService->crearPagina($data);
            return $this->respuestaExito($nuevaPagina, 201);
        } catch (\support\exception\BusinessException $e) {
            return $this->respuestaError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            \support\Log::error('Error en API al crear contenido: ' . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno al crear el recurso.', 500);
        }
    }

    /**
     * Crea un 'sample' subiendo el archivo y los metadatos en una sola petición.
     * POST /api/v1/samples/upload
     *
     * @param Request $request
     * @return Response
     */
    public function uploadSample(Request $request): Response
    {
        $usuario = $request->usuario;

        // 1. Validar Permisos
        if (!in_array($usuario->rol, ['admin', 'artista'])) {
            return $this->respuestaError('No tienes permiso para subir samples.', 403);
        }

        // 2. Validar Campos Obligatorios del Formulario
        $archivo = $request->file('archivoSample');
        $titulo = $request->post('titulo');
        $tipoContenido = $request->post('tipocontenido');
        $estado = $request->post('estado');

        $errors = [];
        if (null === $archivo) $errors[] = ['field' => 'archivoSample', 'issue' => 'El archivo es obligatorio.'];
        if (empty($titulo)) $errors[] = ['field' => 'titulo', 'issue' => 'El título es obligatorio.'];
        if ($tipoContenido !== 'sample') $errors[] = ['field' => 'tipocontenido', 'issue' => 'El tipo de contenido debe ser "sample".'];
        if (empty($estado)) $errors[] = ['field' => 'estado', 'issue' => 'El estado es obligatorio.'];

        if (!empty($errors)) {
            return $this->respuestaError('Faltan campos obligatorios o son incorrectos.', 422, $errors);
        }
        
        if (!$archivo->isValid()) {
            return $this->respuestaError('El archivo subido no es válido.', 422);
        }

        DB::beginTransaction();

        try {
            // 3. Manejar la subida del archivo
            $media = $this->mediaService->gestionarSubidaApi($archivo, $usuario->id);

            // 4. Preparar datos para crear el contenido
            $metadata = [
                'url_archivo' => $media->url_publica,
                'media_id' => $media->id,
            ];

            // Recoger todos los demás campos del post como metadatos opcionales
            $knownFields = ['titulo', 'subtitulo', 'tipocontenido', 'estado'];
            foreach ($request->post() as $key => $value) {
                if (!in_array($key, $knownFields) && !empty($value)) {
                    $metadata[$key] = $value;
                }
            }
            
            $contentData = [
                'titulo' => $titulo,
                'subtitulo' => $request->post('subtitulo'),
                'tipocontenido' => $tipoContenido,
                'estado' => $estado,
                'idautor' => $usuario->id,
                'metadata' => $metadata
            ];

            // 5. Crear la entrada de contenido
            $nuevaPagina = $this->paginaService->crearPagina($contentData);

            DB::commit();
            
            // Transformar la salida para asegurar consistencia en las claves (id_autor)
            $attributes = $nuevaPagina->toArray();
            if (array_key_exists('idautor', $attributes)) {
                $attributes['id_autor'] = $attributes['idautor'];
                unset($attributes['idautor']);
            }

            return $this->respuestaExito($attributes, 201);

        } catch (BusinessException $e) {
            DB::rollBack();
            return $this->respuestaError($e->getMessage(), 422);
        } catch (Throwable $e) {
            DB::rollBack();
            \support\Log::error('Error en endpoint uploadSample: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return $this->respuestaError('Ocurrió un error interno al procesar la petición.', 500);
        }
    }

    public function update(Request $request, int $id): Response
    {
        try {
            $pagina = $this->paginaService->obtenerPaginaPorId($id);
            $usuario = $request->usuario;

            $isOwner = $pagina->idautor == $usuario->id;
            $isAdmin = $usuario->rol === 'admin';

            $canUpdate = false;
            if ($isAdmin) $canUpdate = true;
            if ($pagina->tipocontenido === 'sample' && $isOwner && $usuario->rol === 'artista') $canUpdate = true;
            if ($pagina->tipocontenido === 'comment' && $isOwner) $canUpdate = true;

            if (!$canUpdate) {
                return $this->respuestaError('No tienes permiso para actualizar este recurso.', 403);
            }

            $this->paginaService->actualizarPagina($pagina, $request->post());
            $paginaActualizada = $this->paginaService->obtenerPaginaPorId($id);

            return $this->respuestaExito($paginaActualizada);
        } catch (NotFoundException) {
            return $this->respuestaError('Recurso no encontrado.', 404);
        } catch (\support\exception\BusinessException $e) {
            return $this->respuestaError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            \support\Log::error("Error en API al actualizar contenido {$id}: " . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno al actualizar el recurso.', 500);
        }
    }

    public function destroy(Request $request, int $id): Response
    {
        try {
            $pagina = $this->paginaService->obtenerPaginaPorId($id);
            $usuario = $request->usuario;

            $isOwner = $pagina->idautor == $usuario->id;
            $isAdmin = $usuario->rol === 'admin';

            $canDelete = false;
            if ($isAdmin) $canDelete = true;
            if ($pagina->tipocontenido === 'sample' && $isOwner && $usuario->rol === 'artista') $canDelete = true;
            if ($pagina->tipocontenido === 'comment' && $isOwner) $canDelete = true;

            if (!$canDelete) {
                return $this->respuestaError('No tienes permiso para eliminar este recurso.', 403);
            }

            $this->paginaService->eliminarPagina($id);
            return new Response(204);
        } catch (NotFoundException) {
            return $this->respuestaError('Recurso no encontrado.', 404);
        } catch (\Throwable $e) {
            \support\Log::error("Error en API al eliminar contenido {$id}: " . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno al eliminar el recurso.', 500);
        }
    }

    public function storeComment(Request $request, int $sampleId): Response
    {
        try {
            $sample = $this->paginaService->obtenerPaginaPorId($sampleId);
            if ($sample->tipocontenido !== 'sample' || $sample->estado !== 'publicado') {
                return $this->respuestaError('El sample no existe o no está disponible.', 404);
            }

            $data = $request->post();
            $contenido = $data['contenido'] ?? '';

            if (empty($contenido)) {
                return $this->respuestaError('El contenido del comentario no puede estar vacío.', 422);
            }

            $commentData = [
                'titulo' => 'Comentario en ' . $sample->titulo,
                'contenido' => $contenido,
                'tipocontenido' => 'comment',
                'estado' => 'publicado',
                'idautor' => $request->usuario->id,
                'metadata' => ['parent_id' => $sampleId]
            ];

            $nuevoComentario = $this->paginaService->crearPagina($commentData);
            return $this->respuestaExito($nuevoComentario, 201);
        } catch (NotFoundException) {
            return $this->respuestaError('El sample sobre el que intentas comentar no fue encontrado.', 404);
        } catch (\Throwable $e) {
            \support\Log::error("Error creando comentario para sample {$sampleId}: " . $e->getMessage());
            return $this->respuestaError('Error interno al guardar el comentario.', 500);
        }
    }

    public function getComments(Request $request, int $sampleId): Response
    {
        $perPage = (int) $request->get('per_page', 15);
        $currentPage = (int) $request->get('page', 1);

        $query = new SwordQuery([
            'post_type' => 'comment',
            'post_status' => 'publicado',
            'posts_per_page' => $perPage,
            'paged' => $currentPage,
            'include' => 'autor',
            'meta_query' => [
                [
                    'key' => 'parent_id',
                    'value' => $sampleId,
                    'compare' => '='
                ]
            ]
        ]);

        $items = collect($query->entradas)->map(function (Pagina $item) {
            $attributes = $item->toArray();
            if (array_key_exists('idautor', $attributes)) {
                $attributes['id_autor'] = $attributes['idautor'];
                unset($attributes['idautor']);
            }
            return $attributes;
        });

        $paginatedData = [
            'items' => $items,
            'pagination' => [
                'total_items' => $query->totalEntradas,
                'total_pages' => ($query->totalEntradas > 0) ? (int) ceil($query->totalEntradas / $perPage) : 0,
                'current_page' => $currentPage,
                'per_page' => $perPage,
            ]
        ];

        return $this->respuestaExito($paginatedData);
    }

    public function like(Request $request, int $sampleId): Response
    {
        $userId = $request->usuario->id;

        DB::transaction(function () use ($userId, $sampleId) {
            $exists = DB::table('likes')->where('user_id', $userId)->where('content_id', $sampleId)->exists();
            if (!$exists) {
                if (!DB::table('paginas')->where('id', $sampleId)->exists()) {
                    return;
                }
                DB::table('likes')->insert([
                    'user_id' => $userId,
                    'content_id' => $sampleId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        });

        return new Response(204);
    }

    public function unlike(Request $request, int $sampleId): Response
    {
        $userId = $request->usuario->id;
        DB::table('likes')->where('user_id', $userId)->where('content_id', $sampleId)->delete();
        return new Response(204);
    }
}