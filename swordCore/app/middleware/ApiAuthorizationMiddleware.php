<?php

namespace App\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use App\service\PermisoService;

class ApiAuthorizationMiddleware implements MiddlewareInterface
{
    private $permisoService;

    public function __construct(PermisoService $permisoService)
    {
        $this->permisoService = $permisoService;
    }

    public function process(Request $request, callable $handler): Response
    {
        $usuario = $request->usuario;
        $rol = $usuario ? $usuario->rol : 'anonimo';

        // Definir qué capacidad se necesita para cada ruta protegida
        $rutaCapacidadMap = [
            '/api/v1/permisos' => 'manage_options',
            // Añadir aquí otras rutas y sus capacidades requeridas
        ];

        $rutaActual = $request->path();

        // Comprobar si la ruta actual requiere una capacidad específica
        if (isset($rutaCapacidadMap[$rutaActual])) {
            $capacidadRequerida = $rutaCapacidadMap[$rutaActual];
            $permisos = $this->permisoService->getPermisos();

            if (!$this->tieneCapacidad($rol, $capacidadRequerida, $permisos)) {
                return new Response(403, ['Content-Type' => 'application/json'], json_encode([
                    'error' => ['code' => 403, 'message' => 'Prohibido: No tienes los permisos necesarios para realizar esta acción.']
                ]));
            }
        }

        return $handler($request);
    }

    private function tieneCapacidad(string $rol, string $capacidad, array $permisos): bool
    {
        if ($rol === 'admin') {
            return true; // El admin siempre tiene todos los permisos
        }

        $capacidadesDelRol = $permisos['api'][$rol] ?? [];
        return in_array($capacidad, $capacidadesDelRol, true);
    }
}
