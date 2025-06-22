<?php

namespace App\controller\Api\V1;

use App\controller\Api\ApiBaseController;
use App\service\PaginaService;
use App\service\SwordQuery;
use support\Request;
use support\Response;
use Webman\Exception\NotFoundException;
use Illuminate\Database\Capsule\Manager as DB;

class ContentApiController extends ApiBaseController
{
    private PaginaService $paginaService;

    public function __construct(PaginaService $paginaService)
    {
        $this->paginaService = $paginaService;
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
            // Nuevos parámetros para capacidades avanzadas
            'include' => $request->get('include', ''),
            'q' => $request->get('q', ''),
            'sort_by' => $request->get('sort_by', 'created_at'),
            'order' => $request->get('order', 'desc'),
            'id_autor' => $request->get('id_autor'),
        ];
        
        // Procesar filtros de metadatos (ej: ?metadata[tonalidad]=Cm)
        $metadataFilters = $request->get('metadata');
        if (is_array($metadataFilters)) {
            $args['meta_query'] = [];
            foreach ($metadataFilters as $key => $value) {
                $args['meta_query'][] = ['key' => $key, 'value' => $value, 'compare' => '='];
            }
        }

        $query = new SwordQuery($args);
        
        $paginatedData = [
            'items' => $query->entradas,
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
            // Aplicar Eager Loading también en la vista individual
            $include = $request->get('include', '');
            $pagina = $this->paginaService->obtenerPaginaPorId($id, is_string($include) ? explode(',', $include) : []);
            
            if ($pagina->estado !== 'publicado') {
                 return $this->respuestaError('Recurso no encontrado o no disponible.', 404);
            }
            return $this->respuestaExito($pagina);
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

    public function update(Request $request, int $id): Response
    {
        try {
            $pagina = $this->paginaService->obtenerPaginaPorId($id);
            $usuario = $request->usuario;

            $isOwner = $pagina->idautor == $usuario->id;
            $isAdmin = $usuario->rol === 'admin';
            
            // Lógica de Permisos de la Matriz
            $canUpdate = false;
            if ($isAdmin) $canUpdate = true;
            if ($pagina->tipocontenido === 'sample' && $isOwner && $usuario->rol === 'artista') $canUpdate = true;
            if ($pagina->tipocontenido === 'comment' && $isOwner) $canUpdate = true; // Cualquier rol puede editar su propio comentario
            // Añadir más reglas si es necesario...

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

            // Lógica de Permisos de la Matriz
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

    /**
     * Crea un comentario para un sample.
     * POST /api/v1/samples/{id}/comments
     */
    public function storeComment(Request $request, int $sampleId): Response
    {
        try {
            // Verificar que el sample existe y está publicado
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

    /**
     * Obtiene los comentarios de un sample.
     * GET /api/v1/samples/{id}/comments
     */
    public function getComments(Request $request, int $sampleId): Response
    {
        // Se asume que los comentarios son un tipo de contenido con parent_id en metadata.
        $query = new SwordQuery([
            'post_type' => 'comment',
            'post_status' => 'publicado',
            'meta_query' => [
                [
                    'key' => 'parent_id',
                    'value' => $sampleId,
                    'compare' => '='
                ]
            ]
        ]);
        
        return $this->respuestaExito(['items' => $query->entradas]);
    }

    /**
     * Da "Me Gusta" a un sample.
     * POST /api/v1/samples/{id}/like
     */
    public function like(Request $request, int $sampleId): Response
    {
        $userId = $request->usuario->id;

        // Asumimos que la tabla 'likes' existe: id, user_id, content_id, created_at
        // Usamos una transacción para evitar condiciones de carrera.
        DB::transaction(function () use ($userId, $sampleId) {
            $exists = DB::table('likes')->where('user_id', $userId)->where('content_id', $sampleId)->exists();
            if (!$exists) {
                DB::table('likes')->insert([
                    'user_id' => $userId,
                    'content_id' => $sampleId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        });
        
        return new Response(204); // Éxito, sin contenido
    }

    /**
     * Quita el "Me Gusta" de un sample.
     * DELETE /api/v1/samples/{id}/like
     */
    public function unlike(Request $request, int $sampleId): Response
    {
        $userId = $request->usuario->id;
        DB::table('likes')->where('user_id', $userId)->where('content_id', $sampleId)->delete();
        return new Response(204); // Éxito, sin contenido
    }
}