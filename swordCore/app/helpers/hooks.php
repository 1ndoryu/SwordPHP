<?php

use App\service\HookService;

if (!function_exists('agregarAccion')) {
    /**
     * Registra un callback para una acción específica.
     * Alias de HookService->agregarAccion().
     *
     * @param string $nombreAccion El nombre de la acción.
     * @param callable $callback El callback a ejecutar.
     * @param int $prioridad Orden de ejecución. Números más bajos se ejecutan antes.
     * @param int $argumentosAceptados El número de argumentos que el callback acepta.
     */
    function agregarAccion(string $nombreAccion, callable $callback, int $prioridad = 10, int $argumentosAceptados = 1)
    {
        HookService::getInstancia()->agregarAccion($nombreAccion, $callback, $prioridad, $argumentosAceptados);
    }
}

if (!function_exists('hacerAccion')) {
    /**
     * Ejecuta todos los callbacks asociados a una acción.
     * Alias de HookService->hacerAccion().
     *
     * @param string $nombreAccion El nombre de la acción a ejecutar.
     * @param mixed ...$argumentos Argumentos adicionales para pasar a los callbacks.
     */
    function hacerAccion(string $nombreAccion, ...$argumentos)
    {
        HookService::getInstancia()->hacerAccion($nombreAccion, ...$argumentos);
    }
}

if (!function_exists('agregarFiltro')) {
    /**
     * Registra un callback para un filtro específico.
     * Alias de HookService->agregarFiltro().
     *
     * @param string $nombreFiltro El nombre del filtro.
     * @param callable $callback El callback a ejecutar.
     * @param int $prioridad Orden de ejecución. Números más bajos se ejecutan antes.
     * @param int $argumentosAceptados El número de argumentos que el callback acepta.
     */
    function agregarFiltro(string $nombreFiltro, callable $callback, int $prioridad = 10, int $argumentosAceptados = 1)
    {
        HookService::getInstancia()->agregarFiltro($nombreFiltro, $callback, $prioridad, $argumentosAceptados);
    }
}

if (!function_exists('aplicarFiltro')) {
    /**
     * Aplica todos los filtros a un valor y lo devuelve modificado.
     * Alias de HookService->aplicarFiltro().
     *
     * @param string $nombreFiltro El nombre del filtro a aplicar.
     * @param mixed $valor El valor inicial a filtrar.
     * @param mixed ...$argumentos Argumentos adicionales para pasar a los callbacks.
     * @return mixed El valor después de ser procesado por todos los filtros.
     */
    function aplicarFiltro(string $nombreFiltro, $valor, ...$argumentos)
    {
        return HookService::getInstancia()->aplicarFiltro($nombreFiltro, $valor, ...$argumentos);
    }
}
