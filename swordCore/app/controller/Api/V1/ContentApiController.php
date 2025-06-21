<?php

namespace App\controller\Api\V1;

use App\controller\Api\ApiBaseController;
use App\service\PaginaService;
use App\service\SwordQuery;
use support\Request;
use support\Response;
use Webman\Exception\NotFoundException;

class ContentApiController extends ApiBaseController
{
    private PaginaService $paginaService;

    public function __construct(PaginaService $paginaService)
    {
        $this->paginaService = $paginaService;
    }

    /**
     * Lista el contenido según los parámetros de la consulta (type, per_page, page).
     * GET /api/v1/content
     */
    public function index(Request $request): Response
    {
        $perPage = (int) $request->get('per_page', 10);
        $currentPage = (int) $request->get('page', 1);

        $args = [
            'post_type' => $request->get('type', 'pagina'),
            'posts_per_page' => $perPage,
            'paged' => $currentPage,
            'post_status' => $request->get('status', 'publicado')
        ];

        $query = new SwordQuery($args);

        // Se asume que $query->totalEntradas está disponible. Se calcula el total de páginas manualmente.
        $totalItems = $query->totalEntradas;
        $totalPages = ($totalItems > 0) ? (int) ceil($totalItems / $perPage) : 0;

        $paginatedData = [
            'items' => $query->entradas,
            'pagination' => [
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
                'current_page' => $currentPage,
                'per_page' => $perPage,
            ]
        ];

        return $this->respuestaExito($paginatedData);
    }
    
    // ... Resto de los métodos (show, store, update, destroy) sin cambios
    
    /**
     * Obtiene una única pieza de contenido por su ID.
     * GET /api/v1/content/{id}
     */
    public function show(Request $request, int $id): Response
    {
        try {
            $pagina = $this->paginaService->obtenerPaginaPorId($id);

            // En la API pública, solo se debe mostrar contenido con estado 'publicado'.
            if ($pagina->estado !== 'publicado') {
                return $this->respuestaError('Recurso no encontrado o no disponible.', 404);
            }

            return $this->respuestaExito($pagina);
        } catch (NotFoundException $e) {
            return $this->respuestaError('Recurso no encontrado.', 404);
        }
    }

    /**
     * Crea una nueva pieza de contenido.
     * POST /api/v1/content
     */
    public function store(Request $request): Response
    {
        $data = $request->post();

        if (empty($data['titulo']) || empty($data['tipocontenido'])) {
            return $this->respuestaError('Los campos "titulo" y "tipocontenido" son obligatorios.', 422); // 422 Unprocessable Entity
        }
        
        // El middleware ApiAuthMiddleware ya ha autenticado al usuario y lo ha adjuntado a la request.
        $data['idautor'] = $request->usuario->id;
        $data['estado'] = $data['estado'] ?? 'borrador'; // 'borrador' como estado por defecto.

        try {
            $nuevaPagina = $this->paginaService->crearPagina($data);
            return $this->respuestaExito($nuevaPagina, 201); // 201 Created
        } catch (\support\exception\BusinessException $e) {
            return $this->respuestaError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            \support\Log::error('Error en API al crear contenido: ' . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno al crear el recurso.', 500);
        }
    }

    /**
     * Actualiza una pieza de contenido existente.
     * PUT/PATCH /api/v1/content/{id}
     */
    public function update(Request $request, int $id): Response
    {
        try {
            $pagina = $this->paginaService->obtenerPaginaPorId($id);
            $data = $request->post();

            // Aquí se podría añadir una capa de permisos, por ejemplo:
            // if ($pagina->idautor !== $request->usuario->id && $request->usuario->rol !== 'admin') {
            //     return $this->respuestaError('No tienes permiso para editar este recurso.', 403);
            // }

            $this->paginaService->actualizarPagina($pagina, $data);
            
            // Refrescamos el modelo para devolver el objeto completo y actualizado.
            $paginaActualizada = $this->paginaService->obtenerPaginaPorId($id);

            return $this->respuestaExito($paginaActualizada);
        } catch (NotFoundException $e) {
            return $this->respuestaError('Recurso no encontrado.', 404);
        } catch (\support\exception\BusinessException $e) {
            return $this->respuestaError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            \support\Log::error("Error en API al actualizar contenido {$id}: " . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno al actualizar el recurso.', 500);
        }
    }

    /**
     * Elimina una pieza de contenido.
     * DELETE /api/v1/content/{id}
     */
    public function destroy(Request $request, int $id): Response
    {
        try {
            // Se obtiene la página para asegurar que existe antes de intentar borrarla.
            // Esto también permite añadir una capa de permisos si es necesario.
            $this->paginaService->obtenerPaginaPorId($id);

            $this->paginaService->eliminarPagina($id);

            return new Response(204); // 204 No Content: Éxito, sin cuerpo en la respuesta.
        } catch (NotFoundException $e) {
            return $this->respuestaError('Recurso no encontrado.', 404);
        } catch (\Throwable $e) {
            \support\Log::error("Error en API al eliminar contenido {$id}: " . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno al eliminar el recurso.', 500);
        }
    }
}