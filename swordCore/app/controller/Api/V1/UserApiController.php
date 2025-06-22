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
     * Obtiene la información del usuario autenticado actualmente.
     * GET /api/v1/users/me
     */
    public function me(Request $request): Response
    {
        return $this->respuestaExito($request->usuario);
    }
    
    public function index(Request $request): Response
    {
        if ($request->usuario->rol !== 'admin') {
            return $this->respuestaError('No tienes permiso para listar usuarios.', 403);
        }

        $paginator = $this->usuarioService->obtenerUsuariosPaginados(
            (int) $request->get('per_page', 15)
        );
        
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

    public function show(Request $request, int $id): Response
    {
        if ($request->usuario->rol !== 'admin' && $request->usuario->id != $id) {
             return $this->respuestaError('No tienes permiso para ver este usuario.', 403);
        }
        try {
            $usuario = $this->usuarioService->obtenerUsuarioPorId($id);
            return $this->respuestaExito($usuario);
        } catch (NotFoundException $e) {
            return $this->respuestaError('Usuario no encontrado.', 404);
        }
    }

    public function store(Request $request): Response
    {
        if ($request->usuario->rol !== 'admin') {
            return $this->respuestaError('No tienes permiso para crear usuarios.', 403);
        }
        try {
            $data = $request->post();
            $nuevoUsuario = $this->usuarioService->crearUsuario($data);
            return $this->respuestaExito($nuevoUsuario, 201);
        } catch (BusinessException $e) {
            // CORRECCIÓN: Se elimina la llamada al método inexistente getErrors().
            return $this->respuestaError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            \support\Log::error('Error en API al crear usuario: ' . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno.', 500);
        }
    }

    public function update(Request $request, int $id): Response
    {
        $usuarioAutenticado = $request->usuario;
        if ($usuarioAutenticado->id != $id && $usuarioAutenticado->rol !== 'admin') {
            return $this->respuestaError('No tienes permiso para actualizar este usuario.', 403);
        }

        try {
            $data = $request->post();
            // Un no-admin no puede cambiar el rol de otro usuario ni el suyo propio.
            if ($usuarioAutenticado->rol !== 'admin') {
                unset($data['rol']);
            }
            $usuarioActualizado = $this->usuarioService->actualizarUsuario($id, $data);
            return $this->respuestaExito($usuarioActualizado);
        } catch (NotFoundException $e) {
            return $this->respuestaError('Usuario no encontrado.', 404);
        } catch (BusinessException $e) {
            // CORRECCIÓN: Se elimina la llamada al método inexistente getErrors().
            return $this->respuestaError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            \support\Log::error("Error en API al actualizar usuario {$id}: " . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno.', 500);
        }
    }

    public function destroy(Request $request, int $id): Response
    {
        if ($request->usuario->rol !== 'admin') {
            return $this->respuestaError('No tienes permiso para eliminar usuarios.', 403);
        }
        try {
            $this->usuarioService->eliminarUsuario($id);
            return new Response(204); // No Content
        } catch (NotFoundException $e) {
            return $this->respuestaError('Usuario no encontrado.', 404);
        } catch (BusinessException $e) {
            return $this->respuestaError($e->getMessage(), 403);
        } catch (\Throwable $e) {
            \support\Log::error("Error en API al eliminar usuario {$id}: " . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno.', 500);
        }
    }
}