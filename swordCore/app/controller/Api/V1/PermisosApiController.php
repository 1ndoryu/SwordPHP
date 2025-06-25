<?php

namespace App\controller\Api\V1;

use App\controller\Api\ApiBaseController;
use support\Request;
use support\Response;
use support\exception\BusinessException;

class PermisosApiController extends ApiBaseController
{
    public function index(Request $request): Response
    {
        $this->verificarPermiso($request->usuario, 'manage_options');
        $permisos = config('permisos');
        return $this->respuestaExito($permisos);
    }

    public function update(Request $request): Response
    {
        $this->verificarPermiso($request->usuario, 'manage_options');
        $nuevosPermisos = $request->post();

        if (empty($nuevosPermisos)) {
            return $this->respuestaError('El cuerpo de la petición no puede estar vacío.', 400);
        }

        // Aquí se debería validar la estructura de los nuevos permisos

        $configPath = config_path('permisos.php');
        $configContent = "<?php\n\nreturn " . var_export($nuevosPermisos, true) . ";\n";

        if (file_put_contents($configPath, $configContent) === false) {
            return $this->respuestaError('No se pudo escribir en el archivo de configuración de permisos.', 500);
        }

        return $this->respuestaExito($nuevosPermisos);
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
