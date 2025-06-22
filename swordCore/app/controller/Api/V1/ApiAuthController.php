<?php

namespace App\controller\Api\V1;

use App\controller\Api\ApiBaseController;
use App\service\UsuarioService;
use support\Request;
use support\Response;
use support\exception\BusinessException;

class ApiAuthController extends ApiBaseController
{
    private UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    /**
     * Autentica a un usuario y devuelve un token de API y los datos del usuario.
     * Endpoint: POST /auth/token
     *
     * @param Request $request
     * @return Response
     */
    public function token(Request $request): Response
    {
        // 1. Obtener las credenciales del cuerpo de la petición.
        $nombreUsuario = $request->post('nombreusuario');
        $clave = $request->post('clave');

        // 2. Validar que las credenciales no estén vacías.
        if (empty($nombreUsuario) || empty($clave)) {
            return $this->respuestaError('El nombre de usuario y la clave son obligatorios.', 422); // 422 Unprocessable Entity
        }

        try {
            // 3. Autenticar al usuario y generar un nuevo token.
            $resultado = $this->usuarioService->autenticarYGenerarToken($nombreUsuario, $clave);

            // 4. Construir la respuesta según las especificaciones.
            $respuesta = [
                'token' => $resultado['token'],
                'usuario' => [
                    'id' => $resultado['usuario']->id,
                    'nombreusuario' => $resultado['usuario']->nombreusuario,
                    'nombremostrado' => $resultado['usuario']->nombremostrado,
                    'correoelectronico' => $resultado['usuario']->correoelectronico,
                    'rol' => $resultado['usuario']->rol,
                ]
            ];

            return $this->respuestaExito($respuesta);
        } catch (BusinessException $e) {
            // Captura errores de negocio (ej. credenciales incorrectas).
            return $this->respuestaError($e->getMessage(), 401); // 401 Unauthorized
        } catch (\Throwable $e) {
            // Captura cualquier otro error inesperado.
            \support\Log::error('Error en el endpoint de autenticación: ' . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno en el servidor.', 500);
        }
    }
}