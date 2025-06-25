<?php

namespace app\service;

use app\service\OpcionService;

class PermisoService
{
    private $opcionService;
    private const OPCION_KEY = 'sword_permissions';

    public function __construct(OpcionService $opcionService)
    {
        $this->opcionService = $opcionService;
    }

    /**
     * Obtiene la configuración de permisos.
     *
     * Primero intenta obtenerla de la base de datos (tabla de opciones).
     * Si no existe, carga la configuración por defecto desde el archivo de configuración.
     *
     * @return array
     */
    public function getPermisos(): array
    {
        $permisos = $this->opcionService->getOption(self::OPCION_KEY);

        if ($permisos) {
            return $permisos;
        }

        // Fallback al archivo de configuración si no hay nada en la BD
        return config('permisos');
    }

    /**
     * Guarda la configuración de permisos en la base de datos.
     *
     * @param array $permisos La nueva configuración de permisos.
     * @return bool
     */
    public function savePermisos(array $permisos): bool
    {
        return $this->opcionService->updateOption(self::OPCION_KEY, $permisos);
    }
}
