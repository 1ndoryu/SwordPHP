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
        $nombreUsuario = $request->post('nombre_usuario');
        $clave = $request->post('clave');

        if (empty($nombreUsuario) || empty($clave)) {
            return $this->respuestaError('El nombre de usuario y la clave son requeridos.', 422);
        }

        try {
            $resultado = $this->usuarioService->autenticarYGenerarToken($nombreUsuario, $clave);
            return $this->respuestaExito($resultado);
        } catch (BusinessException $e) {
            return $this->respuestaError($e->getMessage(), 401);
        } catch (\Throwable $e) {
            \support\Log::error('Error en token endpoint: ' . $e->getMessage());
            return $this->respuestaError('Error interno del servidor.', 500);
        }
    }

    public function me(Request $request): Response
    {
        return $this->respuestaExito($request->usuario->toArray());
    }
}