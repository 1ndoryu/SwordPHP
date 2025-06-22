<?php

namespace App\controller\Api\V1;

use App\controller\Api\ApiBaseController;
use App\service\UsuarioService;
use support\Request;
use support\Response;
use Webman\Exception\NotFoundException;
use support\exception\BusinessException;
use Illuminate\Support\Str;

class UserApiController extends ApiBaseController
{
    private UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    /**
     * Convierte las claves de un array de snake_case a camelCase.
     */
    private function mapSnakeToCamel(array $data): array
    {
        $mappedData = [];
        foreach ($data as $key => $value) {
            $mappedData[Str::camel($key)] = $value;
        }
        return $mappedData;
    }

    public function me(Request $request): Response
    {
        // El modelo se serializará a JSON, los campos ocultos no se mostrarán.
        // El ApiBaseController lo envolverá en 'data'.
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
            // Mapear claves de snake_case (API) a camelCase (Servicio/Modelo)
            $data = $this->mapSnakeToCamel($request->post());
            $nuevoUsuario = $this->usuarioService->crearUsuario($data);
            return $this->respuestaExito($nuevoUsuario, 201);
        } catch (BusinessException $e) {
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
            // Mapear claves de snake_case (API) a camelCase (Servicio/Modelo)
            $data = $this->mapSnakeToCamel($request->post());

            // Un no-admin no puede cambiar el rol de otro usuario ni el suyo propio.
            if ($usuarioAutenticado->rol !== 'admin') {
                unset($data['rol']);
            }
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

    public function destroy(Request $request, int $id): Response
    {
        if ($request->usuario->rol !== 'admin') {
            return $this->respuestaError('No tienes permiso para eliminar usuarios.', 403);
        }
        try {
            $this->usuarioService->eliminarUsuario($id);
            return new Response(204);
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
