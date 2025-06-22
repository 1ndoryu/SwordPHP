<?php

namespace App\controller\Api;

use support\Response;

class ApiBaseController
{
    /**
     * Devuelve una respuesta JSON estandarizada para éxito.
     * La estructura de $datos debe ser preparada por el controlador.
     * Para listas: ['items' => [...], 'pagination' => {...}]
     * Para un solo recurso: ['recurso' => {...}]
     *
     * @param mixed $datos Los datos a enviar en la respuesta.
     * @param int $codigo El código de estado HTTP.
     * @return Response
     */
    protected function respuestaExito($datos, int $codigo = 200): Response
    {
        return new Response($codigo, ['Content-Type' => 'application/json'], json_encode([
            'data' => $datos
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
    }

    /**
     * Devuelve una respuesta JSON estandarizada para error.
     *
     * @param string $mensaje El mensaje de error para el desarrollador.
     * @param int $codigo El código de estado HTTP.
     * @param array|null $detalles Un array opcional con detalles específicos del error (ej. fallos de validación).
     * @return Response
     */
    protected function respuestaError(string $mensaje, int $codigo = 400, ?array $detalles = null): Response
    {
        $payload = [
            'error' => [
                'code' => $codigo,
                'message' => $mensaje,
            ]
        ];

        if ($detalles !== null) {
            $payload['error']['details'] = $detalles;
        }

        return new Response($codigo, ['Content-Type' => 'application/json'], json_encode($payload));
    }
}