<?php

namespace App\controller\Api\V1;

use App\controller\Api\ApiBaseController;
use App\service\UsuarioService;
use support\Request;
use support\Response;
use Webman\Exception\NotFoundException;
use support\exception\BusinessException;

class UserApiController extends ApiBaseController
{
    private UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    /**
     * Devuelve una lista paginada de usuarios.
     * GET /api/v1/users
     */
    public function index(Request $request): Response
    {
        $paginator = $this->usuarioService->obtenerUsuariosPaginados(
            (int) $request->get('per_page', 15)
        );

        // Transformamos la salida del paginador de Laravel a nuestro formato estándar de API.
        $paginatedData = [
            'items' => $paginator->items(),
            'pagination' => [
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
            ]
        ];

        return $this->respuestaExito($paginatedData);
    }

    /**
     * Obtiene la información de un único usuario.
     * GET /api/v1/users/{id}
     */
    public function show(Request $request, int $id): Response
    {
        try {
            $usuario = $this->usuarioService->obtenerUsuarioPorId($id);
            return $this->respuestaExito($usuario);
        } catch (NotFoundException $e) {
            return $this->respuestaError('Usuario no encontrado.', 404);
        }
    }

    /**
     * Crea un nuevo usuario.
     * POST /api/v1/users
     */
    public function store(Request $request): Response
    {
        try {
            $data = $request->post();
            $nuevoUsuario = $this->usuarioService->crearUsuario($data);
            return $this->respuestaExito($nuevoUsuario, 201);
        } catch (BusinessException $e) {
            return $this->respuestaError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            \support\Log::error('Error en API al crear usuario: ' . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno.', 500);
        }
    }

    /**
     * Actualiza un usuario existente.
     * PUT /api/v1/users/{id}
     */
    public function update(Request $request, int $id): Response
    {
        try {
            $data = $request->post();
            $usuarioActualizado = $this->usuarioService->actualizarUsuario($id, $data);
            return $this->respuestaExito($usuarioActualizado);
        } catch (NotFoundException $e) {
            return $this->respuestaError('Usuario no encontrado.', 404);
        } catch (BusinessException $e) {
            return $this->respuestaError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            \support\Log::error("Error en API al actualizar usuario {$id}: " . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno.', 500);
        }
    }

    /**
     * Elimina un usuario.
     * DELETE /api/v1/users/{id}
     */
    public function destroy(Request $request, int $id): Response
    {
        try {
            $this->usuarioService->eliminarUsuario($id);
            return new Response(204); // No Content
        } catch (NotFoundException $e) {
            return $this->respuestaError('Usuario no encontrado.', 404);
        } catch (BusinessException $e) {
            // Captura errores de negocio como "no puedes borrarte a ti mismo".
            return $this->respuestaError($e->getMessage(), 403); // 403 Forbidden
        } catch (\Throwable $e) {
            \support\Log::error("Error en API al eliminar usuario {$id}: " . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno.', 500);
        }
    }
}
