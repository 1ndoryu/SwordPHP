<?php

use App\service\HookService;

if (!function_exists('addAction')) {
    /**
     * Registra un callback para una acción específica.
     * Alias de HookService->addAction().
     *
     * @param string $nombreAccion El nombre de la acción.
     * @param callable $callback El callback a ejecutar.
     * @param int $prioridad Orden de ejecución. Números más bajos se ejecutan antes.
     * @param int $argumentosAceptados El número de argumentos que el callback acepta.
     */
    function addAction(string $nombreAccion, callable $callback, int $prioridad = 10, int $argumentosAceptados = 1)
    {
        HookService::getInstancia()->addAction($nombreAccion, $callback, $prioridad, $argumentosAceptados);
    }
}

if (!function_exists('doAction')) {
    /**
     * Ejecuta todos los callbacks asociados a una acción.
     * Alias de HookService->doAction().
     *
     * @param string $nombreAccion El nombre de la acción a ejecutar.
     * @param mixed ...$argumentos Argumentos adicionales para pasar a los callbacks.
     */
    function doAction(string $nombreAccion, ...$argumentos)
    {
        HookService::getInstancia()->doAction($nombreAccion, ...$argumentos);
    }
}

if (!function_exists('addFilter')) {
    /**
     * Registra un callback para un filtro específico.
     * Alias de HookService->addFilter().
     *
     * @param string $nombreFiltro El nombre del filtro.
     * @param callable $callback El callback a ejecutar.
     * @param int $prioridad Orden de ejecución. Números más bajos se ejecutan antes.
     * @param int $argumentosAceptados El número de argumentos que el callback acepta.
     */
    function addFilter(string $nombreFiltro, callable $callback, int $prioridad = 10, int $argumentosAceptados = 1)
    {
        HookService::getInstancia()->addFilter($nombreFiltro, $callback, $prioridad, $argumentosAceptados);
    }
}

if (!function_exists('applyFilters')) {
    /**
     * Aplica todos los filtros a un valor y lo devuelve modificado.
     * Alias de HookService->applyFilters().
     *
     * @param string $nombreFiltro El nombre del filtro a aplicar.
     * @param mixed $valor El valor inicial a filtrar.
     * @param mixed ...$argumentos Argumentos adicionales para pasar a los callbacks.
     * @return mixed El valor después de ser procesado por todos los filtros.
     */
    function applyFilters(string $nombreFiltro, $valor, ...$argumentos)
    {
        return HookService::getInstancia()->applyFilters($nombreFiltro, $valor, ...$argumentos);
    }
}
