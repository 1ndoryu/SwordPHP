<?php

namespace app\controller;

use app\model\User;
use Firebase\JWT\JWT;
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class AuthController
{
    /**
     * Register a new user.
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request): Response
    {
        // Basic validation
        $username = $request->post('username');
        $email = $request->post('email');
        $password = $request->post('password');

        if (!$username || !$email || !$password) {
            return api_response(false, 'Missing required fields.', null, 400);
        }

        // Check if user already exists
        if (User::where('username', $username)->orWhere('email', $email)->exists()) {
            return api_response(false, 'User already exists.', null, 409);
        }

        try {
            $role = 'user';

            $user = User::create([
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role
            ]);

            Log::channel('auth')->info('Nuevo usuario registrado', ['username' => $username, 'user_id' => $user->id, 'role' => $role]);

            return api_response(true, 'User registered successfully.');
        } catch (Throwable $e) {
            Log::channel('auth')->error('Error durante el registro de usuario', ['error' => $e->getMessage()]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    /**
     * Login a user and return a JWT.
     *
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
        $identifier = $request->post('identifier'); // Can be username or email
        $password = $request->post('password');

        if (!$identifier || !$password) {
            return api_response(false, 'Identifier and password are required.', null, 400);
        }

        $user = User::where('username', $identifier)->orWhere('email', $identifier)->first();

        if (!$user || !password_verify($password, $user->password)) {
            Log::channel('auth')->warning('Intento de login fallido', ['identifier' => $identifier]);
            return api_response(false, 'Invalid credentials.', null, 401);
        }

        try {
            $payload = [
                'iss' => env('APP_URL'), // Issuer
                'aud' => env('APP_URL'), // Audience
                'iat' => time(), // Issued at
                'nbf' => time(), // Not before
                'exp' => time() + (int)env('JWT_TTL', 3600), // Expiration time (1 hour default)
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role
                ]
            ];

            $jwt = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

            Log::channel('auth')->info('Usuario ha iniciado sesión', ['user_id' => $user->id]);

            return api_response(true, 'Login successful.', [
                'token_type' => 'bearer',
                'access_token' => $jwt,
                'expires_in' => $payload['exp'] - time()
            ]);
        } catch (Throwable $e) {
            Log::channel('auth')->error('Error durante la generación de JWT', ['error' => $e->getMessage()]);
            return api_response(false, 'Could not create token.', null, 500);
        }
    }
}
