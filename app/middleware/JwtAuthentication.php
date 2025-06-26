<?php

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;
use support\Log;
use app\model\User;

class JwtAuthentication implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            return json(['success' => false, 'message' => 'Authorization token not found.'], 401);
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));

            // Attach user data to the request for use in controllers
            $user = User::find($decoded->data->id);
            if (!$user) {
                return json(['success' => false, 'message' => 'User not found.'], 401);
            }
            $request->user = $user;

            return $handler($request);
        } catch (Throwable $e) {
            Log::channel('auth')->warning('Intento de acceso con token inválido', [
                'error' => $e->getMessage(),
                'ip' => $request->getRealIp()
            ]);
            return json(['success' => false, 'message' => 'Provided token is invalid.'], 401);
        }
    }
}
