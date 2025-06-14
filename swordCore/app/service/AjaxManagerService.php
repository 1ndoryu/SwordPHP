<?php

namespace App\service;

use support\Request;
use support\Response;

/**
 * El "Cerebro" Central del sistema AJAX.
 * Gestiona el registro y la ejecución de acciones AJAX de forma centralizada,
 * permitiendo que los temas y plugins registren sus propias funciones de forma segura.
 */
class AjaxManagerService
{
    /**
     * Almacena todas las acciones AJAX registradas.
     * La clave es el nombre de la acción y el valor es la función (callable) a ejecutar.
     *
     * @var array<string, callable>
     */
    private static array $accionesRegistradas = [];

    /**
     * Registra una nueva acción AJAX.
     *
     * @param string $nombreAccion El nombre único de la acción (ej: 'mi_accion_custom').
     * @param callable $callback La función que se ejecutará cuando se llame a la acción.
     * @return void
     */
    public static function registrarAccion(string $nombreAccion, callable $callback): void
    {
        if (isset(self::$accionesRegistradas[$nombreAccion])) {
            // Opcional: Podríamos lanzar una advertencia o un log si se intenta registrar una acción ya existente.
            // Por ahora, simplemente sobreescribimos para mantenerlo simple.
        }
        self::$accionesRegistradas[$nombreAccion] = $callback;
    }

    /**
     * Busca y ejecuta una acción AJAX registrada.
     *
     * @param string $nombreAccion El nombre de la acción a ejecutar.
     * @param Request $request El objeto de la solicitud actual, que se pasará a la función de callback.
     * @return Response La respuesta generada por la función de callback, o una respuesta de error si la acción no se encuentra.
     */
    public static function ejecutarAccion(string $nombreAccion, Request $request): Response
    {
        if (isset(self::$accionesRegistradas[$nombreAccion]) && is_callable(self::$accionesRegistradas[$nombreAccion])) {
            // La acción existe, la ejecutamos y le pasamos el objeto Request.
            $callback = self::$accionesRegistradas[$nombreAccion];
            // La función de callback es responsable de devolver una \support\Response.
            return call_user_func($callback, $request);
        }

        // Si la acción no se encuentra registrada, devolvemos una respuesta JSON de error.
        return json([
            'success' => false,
            'message' => 'Error: La acción AJAX solicitada no está registrada.',
            'action' => $nombreAccion
        ], 404);
    }
}
