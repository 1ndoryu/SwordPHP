<?php

namespace app\controller\Api;

use app\service\PermisoService;
use Webman\Http\Request;
use Webman\Http\Response;

class PermisosController
{
    private $permisoService;

    public function __construct(PermisoService $permisoService)
    {
        $this->permisoService = $permisoService;
    }

    /**
     * Obtiene la configuración de permisos actual.
     *
     * @return Response
     */
    public function getPermisos(): Response
    {
        $permisos = $this->permisoService->getPermisos();
        return new Response(200, ['Content-Type' => 'application/json'], json_encode(['data' => $permisos]));
    }

    /**
     * Actualiza la configuración de permisos.
     *
     * @param Request $request
     * @return Response
     */
    public function updatePermisos(Request $request): Response
    {
        $nuevosPermisos = $request->post();

        if (empty($nuevosPermisos)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error' => ['code' => 400, 'message' => 'El cuerpo de la petición no puede estar vacío.']
            ]));
        }

        if ($this->permisoService->savePermisos($nuevosPermisos)) {
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'data' => $this->permisoService->getPermisos()
            ]));
        } else {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error' => ['code' => 500, 'message' => 'No se pudo guardar la configuración de permisos.']
            ]));
        }
    }
}
