<?php

namespace App\controller;

use App\model\Media;
use support\Request;
use support\Response;

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
            // El error original era porque se intentaba seleccionar 'url_publica', que es un accesor, no una columna.
            // La forma correcta es obtener el modelo y Eloquent añadirá el accesor al serializar a JSON.
            // Seleccionamos solo las columnas necesarias para optimizar. 'rutaarchivo' es necesaria para el accesor.
            $mediaItems = Media::select(['id', 'titulo', 'rutaarchivo', 'tipomime'])
                ->where('tipomime', 'like', 'image/%')
                ->orderBy('created_at', 'desc')
                ->limit(100) // Limitar para no sobrecargar
                ->get();

            // Al convertir a JSON, el accesor 'url_publica' se añadirá automáticamente gracias a la propiedad $appends en el modelo Media.
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'exito' => true,
                'media' => $mediaItems
            ]));

        } catch (\Throwable $e) {
            error_log('Error en AjaxController@obtenerGaleria: ' . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'exito' => false,
                'mensaje' => 'Error del servidor al obtener la galería.'
            ]));
        }
    }
}