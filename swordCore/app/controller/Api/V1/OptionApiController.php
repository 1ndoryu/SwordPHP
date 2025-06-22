<?php

namespace App\controller\Api\V1;

use App\controller\Api\ApiBaseController;
use App\service\OpcionService;
use support\Request;
use support\Response;

class OptionApiController extends ApiBaseController
{
    private OpcionService $opcionService;

    public function __construct(OpcionService $opcionService)
    {
        $this->opcionService = $opcionService;
    }
    
    public function show(Request $request, string $key): Response
    {
        if ($request->usuario->rol !== 'admin') {
            return $this->respuestaError('Acceso denegado. Se requiere rol de administrador.', 403);
        }

        if (!\App\model\Opcion::where('opcion_nombre', $key)->exists()) {
            return $this->respuestaError('Opción no encontrada.', 404);
        }

        $value = $this->opcionService->getOption($key);
        return $this->respuestaExito(['key' => $key, 'value' => $value]);
    }

    public function store(Request $request): Response
    {
        if ($request->usuario->rol !== 'admin') {
            return $this->respuestaError('Acceso denegado. Se requiere rol de administrador.', 403);
        }

        $data = $request->post();
        $key = $data['key'] ?? null;
        $value = array_key_exists('value', $data) ? $data['value'] : null;

        if (empty($key)) {
            return $this->respuestaError('El campo "key" es obligatorio.', 422);
        }

        try {
            $this->opcionService->updateOption($key, $value);
            return $this->respuestaExito(['key' => $key, 'value' => $value]);
        } catch (\Throwable $e) {
            \support\Log::error("Error en API al guardar opción {$key}: " . $e->getMessage());
            return $this->respuestaError('Ocurrió un error interno al guardar la opción.', 500);
        }
    }
}