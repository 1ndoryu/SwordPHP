<?php

namespace App\controller\Api;

use support\Response;

class ApiBaseController
{
    /**
     * Devuelve una respuesta JSON estandarizada para éxito.
     *
     * @param mixed $datos Los datos a enviar en la respuesta.
     * @param int $codigo El código de estado HTTP.
     * @return Response
     */
    protected function respuestaExito($datos, int $codigo = 200): Response
    {
        return new Response($codigo, ['Content-Type' => 'application/json'], json_encode([
            'data' => $datos
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * Devuelve una respuesta JSON estandarizada para error.
     *
     * @param string $mensaje El mensaje de error.
     * @param int $codigo El código de estado HTTP.
     * @return Response
     */
    protected function respuestaError(string $mensaje, int $codigo = 400): Response
    {
        return new Response($codigo, ['Content-Type' => 'application/json'], json_encode([
            'error' => [
                'code' => $codigo,
                'message' => $mensaje
            ]
        ]));
    }
}
