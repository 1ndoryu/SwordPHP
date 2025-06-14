<?php

namespace App\controller;

use support\Request;
use support\Response;

/**
 * Controlador para gestionar todas las peticiones AJAX de la aplicación.
 * Utiliza un parámetro 'action' en la ruta para invocar dinámicamente
 * a métodos específicos con el prefijo 'action_'.
 */
class AjaxController
{
    /**
     * Punto de entrada para todas las llamadas AJAX.
     *
     * @param Request $request La solicitud entrante.
     * @param string $action El nombre de la acción a ejecutar.
     * @return Response Una respuesta JSON.
     */
    public function handle(Request $request, string $action): Response
    {
        // Sanitiza la acción para prevenir vulnerabilidades de path traversal.
        // Permite solo caracteres alfanuméricos, guiones bajos y guiones.
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $action)) {
            return json(['success' => false, 'message' => 'Acción no válida.'], 400);
        }

        // Construye el nombre del método de acción (ej: 'action_mi_accion').
        $nombreMetodo = 'action_' . str_replace('-', '_', $action);

        // Verifica si el método de acción existe en esta clase y es privado.
        // Forzamos que los métodos de acción sean privados para que solo sean
        // accesibles a través del manejador 'handle', no directamente por la ruta.
        if (method_exists($this, $nombreMetodo)) {
            $reflection = new \ReflectionMethod($this, $nombreMetodo);
            if ($reflection->isPrivate()) {
                // Llama al método de acción correspondiente.
                return $this->{$nombreMetodo}($request);
            }
        }

        // Si la acción no se encuentra o no es un método privado, devuelve un error.
        return json(['success' => false, 'message' => 'Acción no encontrada o no permitida.'], 404);
    }

    /**
     * Acción de prueba para verificar que el sistema AJAX funciona.
     *
     * @param Request $request
     * @return Response
     */
    private function action_test(Request $request): Response
    {
        return json([
            'success' => true,
            'message' => 'Respuesta de prueba del manejador AJAX.',
            'data' => [
                'timestamp' => time(),
                'received_params' => $request->all()
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Acciones para el CRUD de Páginas
    |--------------------------------------------------------------------------
    | Aquí se añadirán los métodos para crear, leer, actualizar y eliminar
    | páginas vía AJAX. Por ejemplo:
    |
    | private function action_borrar_pagina(Request $request): Response
    | {
    |     // Lógica para borrar la página
    | }
    |
    */
}
