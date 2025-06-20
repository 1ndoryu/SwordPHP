<?php

namespace App\controller;

use App\service\AjaxManagerService;
use support\Request;
use support\Response;
use App\model\Media; // Asegúrate de importar el modelo Media

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

    /**
     * Obtiene los elementos de la biblioteca de medios para el modal de selección.
     *
     * @param Request $request
     * @return Response
     */
    public function obtenerGaleria(Request $request): Response
    {
        try {
            // Obtenemos solo imágenes, las más recientes primero
            $mediaItems = Media::where('tipomime', 'like', 'image/%')
                ->orderBy('created_at', 'desc')
                ->limit(100) // Limitar la carga inicial
                ->get(['id', 'url_publica', 'titulo']);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'exito' => true,
                'media' => $mediaItems
            ]));
        } catch (\Throwable $e) {
            error_log($e);
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'exito' => false,
                'mensaje' => 'Error al obtener la galería: ' . $e->getMessage()
            ]));
        }
    }
}
