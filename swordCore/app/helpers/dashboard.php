<?php

use App\service\DashboardWidgetService;

if (!function_exists('addWidgetPanel')) {
    /**
     * Registra un nuevo widget para mostrar en el panel de inicio.
     *
     * @param string $id Un identificador único para el widget.
     * @param string $titulo El título del widget que se mostrará en la cabecera.
     * @param callable $callback La función que se encargará de renderizar el contenido HTML del widget.
     * @param int $columna La columna del dashboard en la que se mostrará (1 o 2). Por defecto 1.
     * @param int $prioridad Orden de ejecución (menor se muestra antes). Por defecto 10.
     */
    function addWidgetPanel(string $id, string $titulo, callable $callback, int $columna = 1, int $prioridad = 10): void
    {
        DashboardWidgetService::getInstancia()->registrar($id, $titulo, $callback, $columna, $prioridad);
    }
}
