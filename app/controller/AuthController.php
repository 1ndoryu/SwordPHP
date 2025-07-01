<?php

namespace app\controller;

use app\model\User;
use app\model\Role;
use app\traits\HasValidation;
use app\traits\HandlesErrors;
use app\config\AppConstants;
use Firebase\JWT\JWT;
use Illuminate\Database\Capsule\Manager as Capsule;
use support\Request;
use support\Response;
use Throwable;

class AuthController
{
    use HasValidation, HandlesErrors;

    /**
     * Register a new user.
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request): Response
    {
        $data = $request->post();
        
        // Validate required fields using trait
        $validation_error = $this->validateRequiredFields($data, ['username', 'email', 'password']);
        if ($validation_error) {
            return $validation_error;
        }

        // Check if user already exists
        if (User::where('username', $data['username'])
                ->orWhere('email', $data['email'])
                ->exists()) {
            return api_response(false, 'User already exists.', null, 409);
        }

        try {
            // Get default roles
            $admin_role = Role::where('name', AppConstants::ROLE_ADMIN)->first();
            $user_role = Role::where('name', AppConstants::ROLE_USER)->first();

            // Critical failure if default roles don't exist
            if (!$admin_role || !$user_role) {
                $this->logSecurityWarning('Los roles por defecto no se encuentran en la base de datos. Ejecute db:install.');
                return api_response(false, 'Server configuration error: Default roles not found.', null, 500);
            }
            
            // Assign role using transaction
            $role_id_to_assign = Capsule::transaction(function () use ($admin_role, $user_role) {
                $is_admin_present = User::where('role_id', $admin_role->id)->lockForUpdate()->exists();
                return !$is_admin_present ? $admin_role->id : $user_role->id;
            });

            $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role_id' => $role_id_to_assign
            ]);

            $role_name = ($role_id_to_assign === $admin_role->id) ? AppConstants::ROLE_ADMIN : AppConstants::ROLE_USER;
            $this->logSuccess('auth', 'Nuevo usuario registrado', [
                'username' => $data['username'], 
                'user_id' => $user->id, 
                'role' => $role_name
            ]);

            // Dispatch new user event
            rabbit_event('user.registered', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role_name' => $role_name
            ]);

            return api_response(true, 'User registered successfully.');

        } catch (Throwable $e) {
            return $this->handleError($e, 'auth', 'Error durante el registro de usuario');
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
        $data = $request->post();
        
        // Validate required fields
        $validation_error = $this->validateRequiredFields($data, ['identifier', 'password']);
        if ($validation_error) {
            return $validation_error;
        }

        $user = User::with('role')
            ->where('username', $data['identifier'])
            ->orWhere('email', $data['identifier'])
            ->first();

        if (!$user || !password_verify($data['password'], $user->password)) {
            $this->logSecurityWarning('Intento de login fallido', ['identifier' => $data['identifier']]);
            return api_response(false, 'Invalid credentials.', null, 401);
        }

        try {
            $ttl = (int)env('JWT_TTL', AppConstants::DEFAULT_JWT_TTL);
            $payload = [
                'iss' => env('APP_URL'),
                'aud' => env('APP_URL'),
                'iat' => time(),
                'nbf' => time(),
                'exp' => time() + $ttl,
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role->name ?? null
                ]
            ];

            $jwt = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

            $this->logSuccess('auth', 'Usuario ha iniciado sesión', ['user_id' => $user->id]);

            // Dispatch login event
            rabbit_event('user.loggedin', ['user_id' => $user->id]);

            return api_response(true, 'Login successful.', [
                'token_type' => 'bearer',
                'access_token' => $jwt,
                'expires_in' => $ttl
            ]);
        } catch (Throwable $e) {
            return $this->handleError($e, 'auth', 'Error durante la generación de JWT');
        }
    }
}