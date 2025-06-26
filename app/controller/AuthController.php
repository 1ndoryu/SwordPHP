<?php

namespace app\controller;

use app\model\User;
use app\model\Role; // <-- Añadido para interactuar con el modelo Role
use Firebase\JWT\JWT;
use Illuminate\Database\Capsule\Manager as Capsule;
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
            // Se obtienen los roles 'admin' y 'user' por su nombre.
            $adminRole = Role::where('name', 'admin')->first();
            $userRole = Role::where('name', 'user')->first();

            // Fallo crítico si los roles por defecto no existen (deberían haber sido creados por db:install)
            if (!$adminRole || !$userRole) {
                Log::channel('auth')->critical('Los roles por defecto "admin" o "user" no se encuentran en la base de datos. Ejecute db:install.');
                return api_response(false, 'Server configuration error: Default roles not found.', null, 500);
            }
            
            // La lógica para asignar el rol se envuelve en una transacción
            // y ahora utiliza los IDs de los roles.
            $role_id_to_assign = Capsule::transaction(function () use ($adminRole, $userRole) {
                // Comprueba correctamente si ya existe un usuario con el role_id de admin.
                $isAdminPresent = User::where('role_id', $adminRole->id)->lockForUpdate()->exists();
                return !$isAdminPresent ? $adminRole->id : $userRole->id;
            });

            $user = User::create([
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                // Se asigna el role_id correcto en lugar de un string.
                'role_id' => $role_id_to_assign
            ]);

            $roleName = ($role_id_to_assign === $adminRole->id) ? 'admin' : 'user';
            Log::channel('auth')->info('Nuevo usuario registrado', ['username' => $username, 'user_id' => $user->id, 'role' => $roleName]);

            // Despachar evento de nuevo usuario
            dispatch_event('user.registered', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role_name' => $roleName
            ]);

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

        $user = User::with('role')->where('username', $identifier)->orWhere('email', $identifier)->first();

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
                    // Se obtiene el nombre del rol a través de la relación para el payload.
                    'role' => $user->role->name ?? null
                ]
            ];

            $jwt = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

            Log::channel('auth')->info('Usuario ha iniciado sesión', ['user_id' => $user->id]);

            // Despachar evento de login
            dispatch_event('user.loggedin', ['user_id' => $user->id]);

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