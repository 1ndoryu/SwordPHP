<?php

namespace App\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use App\model\Usuario;

class ApiAuthMiddleware implements MiddlewareInterface
{
    /**
     * Procesa una petición entrante a la API.
     *
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                'error' => ['code' => 401, 'message' => 'Token de autorización no proporcionado o con formato incorrecto.']
            ]));
        }

        $token = $matches[1];
        if (empty($token)) {
            return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                'error' => ['code' => 401, 'message' => 'Token de autorización vacío.']
            ]));
        }

        $usuario = Usuario::where('api_token', $token)->first();

        if (!$usuario) {
            return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                'error' => ['code' => 401, 'message' => 'No Autorizado: El token es inválido.']
            ]));
        }

        // Adjuntamos el usuario autenticado a la petición para que los controladores puedan usarlo.
        $request->usuario = $usuario;

        // El token es válido, la petición continúa.
        return $handler($request);
    }
}
