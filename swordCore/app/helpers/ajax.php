<?php

if (!function_exists('addAjax')) {
    /**
     * Registra una acción AJAX para que esté disponible en el sistema.
     *
     * @param string $nombreAccion El nombre único para la acción AJAX.
     * @param callable $callback La función que se ejecutará cuando se llame a esta acción.
     */
    function addAjax(string $nombreAccion, callable $callback)
    {
        AjaxManagerService::registrarAccion($nombreAccion, $callback);
    }
}
