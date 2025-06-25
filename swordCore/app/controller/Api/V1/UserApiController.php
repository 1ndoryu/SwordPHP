<?php

namespace App\controller\Api\V1;

use App\controller\Api\ApiBaseController;
use App\service\UsuarioService;
use support\Request;
use support\Response;
use Webman\Exception\NotFoundException;
use support\exception\BusinessException;
use Illuminate\Support\Str;
use App\model\Media; // <-- Añadir importación del modelo Media

class UserApiController extends ApiBaseController
{
    private UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function me(Request $request): Response
    {
        return $this->respuestaExito($request->usuario->toArray());
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

    public function index(Request $request): Response
    {
        $this->verificarPermiso($request->usuario, 'manage_users');

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
        if ($request->usuario->id != $id) {
            $this->verificarPermiso($request->usuario, 'manage_users');
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
        $this->verificarPermiso($request->usuario, 'manage_users');
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
        if ($usuarioAutenticado->id != $id) {
            $this->verificarPermiso($usuarioAutenticado, 'manage_users');
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
        $this->verificarPermiso($request->usuario, 'manage_users');
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

    
    

    public function profilePicture(Request $request, int $id): Response
    {
        try {
            $usuario = $this->usuarioService->obtenerUsuarioPorId($id);

            // 1. Obtener el ID de la imagen desde los metadatos del usuario.
            $profilePictureId = $usuario->obtenerMeta('_imagen_destacada_id');

            if ($profilePictureId) {
                // 2. Buscar el registro de Media correspondiente.
                $media = Media::find($profilePictureId);

                if ($media && $media->rutaarchivo) {
                    // 3. Construir la ruta absoluta al archivo.
                    $absolutePath = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $media->rutaarchivo;

                    if (file_exists($absolutePath)) {
                        // 4. Servir el archivo.
                        return response()->file($absolutePath);
                    }
                }
            }

            // Si no se encuentra el ID, el media, o el archivo, devolver 404.
            return $this->respuestaError('Imagen de perfil no encontrada.', 404);
        } catch (NotFoundException $e) {
            return $this->respuestaError('Usuario no encontrado.', 404);
        }
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
}