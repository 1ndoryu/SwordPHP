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

        $this->verificarPermiso($usuario, 'create_content', $data['tipocontenido']);

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

    

    public function update(Request $request, int $id): Response
    {
        try {
            $pagina = $this->paginaService->obtenerPaginaPorId($id);
            $usuario = $request->usuario;

            $capacidad = ($pagina->idautor == $usuario->id) ? 'edit_own_content' : 'edit_others_content';
            $this->verificarPermiso($usuario, $capacidad, $pagina->tipocontenido);

            $this->paginaService->actualizarPagina($pagina, $request->post());
            $paginaActualizada = $this->paginaService->obtenerPaginaPorId($id);

            return $this->respuestaExito($paginaActualizada);
        } catch (NotFoundException) {
            return $this->respuestaError('Recurso no encontrado.', 404);
        } catch (BusinessException $e) {
            return $this->respuestaError($e->getMessage(), $e->getCode());
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

            $capacidad = ($pagina->idautor == $usuario->id) ? 'delete_own_content' : 'delete_others_content';
            $this->verificarPermiso($usuario, $capacidad, $pagina->tipocontenido);

            $this->paginaService->eliminarPagina($id);
            return new Response(204);
        } catch (NotFoundException) {
            return $this->respuestaError('Recurso no encontrado.', 404);
        } catch (BusinessException $e) {
            return $this->respuestaError($e->getMessage(), $e->getCode());
        } catch (\Throwable $e) {
            \support\Log::error("Error en API al eliminar contenido {$id}: " . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno al eliminar el recurso.', 500);
        }
    }

    public function storeComment(Request $request, int $contentId): Response
    {
        try {
            $content = $this->paginaService->obtenerPaginaPorId($contentId);
            if ($content->estado !== 'publicado') {
                return $this->respuestaError('El contenido no existe o no está disponible.', 404);
            }

            $data = $request->post();
            $contenido = $data['contenido'] ?? '';

            if (empty($contenido)) {
                return $this->respuestaError('El contenido del comentario no puede estar vacío.', 422);
            }

            $commentData = [
                'titulo' => 'Comentario en ' . $content->titulo,
                'contenido' => $contenido,
                'tipocontenido' => 'comment',
                'estado' => 'publicado',
                'idautor' => $request->usuario->id,
                'metadata' => ['parent_id' => $contentId]
            ];

            $nuevoComentario = $this->paginaService->crearPagina($commentData);
            return $this->respuestaExito($nuevoComentario, 201);
        } catch (NotFoundException) {
            return $this->respuestaError('El contenido sobre el que intentas comentar no fue encontrado.', 404);
        } catch (\Throwable $e) {
            \support\Log::error("Error creando comentario para contenido {$contentId}: " . $e->getMessage());
            return $this->respuestaError('Error interno al guardar el comentario.', 500);
        }
    }

    public function getComments(Request $request, int $contentId): Response
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
                    'value' => $contentId,
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

    public function like(Request $request, int $contentId): Response
    {
        $userId = $request->usuario->id;

        DB::transaction(function () use ($userId, $contentId) {
            $exists = DB::table('likes')->where('user_id', $userId)->where('content_id', $contentId)->exists();
            if (!$exists) {
                if (!DB::table('paginas')->where('id', $contentId)->exists()) {
                    return;
                }
                DB::table('likes')->insert([
                    'user_id' => $userId,
                    'content_id' => $contentId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        });

        return new Response(204);
    }

    private function verificarPermiso($usuario, $capacidad, $tipoContenido = null)
    {
        $permisos = config('permisos.api');
        $rol = $usuario->rol ?? 'anonimo';

        $capacidadesRol = $permisos[$rol] ?? [];

        if (in_array('manage_options', $capacidadesRol)) {
            return;
        }

        if (!in_array($capacidad, $capacidadesRol)) {
            throw new BusinessException('No tienes permiso para realizar esta acción.', 403);
        }

        if ($tipoContenido) {
            $tiposPermitidos = $permisos['tipos_contenido'][$rol] ?? [];
            if (!in_array($tipoContenido, $tiposPermitidos)) {
                throw new BusinessException("No tienes permiso para gestionar el tipo de contenido '{$tipoContenido}'.", 403);
            }
        }
    }

    public function unlike(Request $request, int $contentId): Response
    {
        $userId = $request->usuario->id;
        DB::table('likes')->where('user_id', $userId)->where('content_id', $contentId)->delete();
        return new Response(204);
    }

    
}
