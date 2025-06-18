<?php

namespace App\controller;

use App\service\AjaxManagerService;
use support\Request;
use support\Response;

/**
 * El "Gatekeeper" de las llamadas AJAX.
 * Su única responsabilidad es recibir la solicitud, determinar qué acción se solicita
 * y pasarle el control al AjaxManagerService para su ejecución.
 */
class AjaxController
{
    /**
     * Punto de entrada único para todas las llamadas AJAX.
     *
     * Espera un parámetro 'action' en la solicitud para determinar
     * qué función registrada debe ejecutarse.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        // Obtenemos el nombre de la acción desde la solicitud.
        // Asumimos que vendrá como un dato POST, que es lo más común para AJAX.
        $action = $request->post('action');

        if (empty($action)) {
            // Si no se especifica una acción, devolvemos un error.
            return json([
                'success' => false,
                'message' => 'Error: No se ha especificado ninguna acción AJAX.'
            ], 400); // 400 Bad Request
        }

        // Le pedimos al "Cerebro" que ejecute la acción.
        return AjaxManagerService::ejecutarAccion($action, $request);
    }
}