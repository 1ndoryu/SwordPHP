<?php

namespace app\controller\Admin;

use support\Request;
use support\Response;
use Illuminate\Database\Capsule\Manager as Capsule;
use Throwable;

class AuthController
{
    /**
     * Verifica el estado de la conexión a la base de datos.
     * 
     * @return array ['conectado' => bool, 'mensaje' => string]
     */
    private function verificarEstadoBaseDatos(): array
    {
        try {
            $pdo = Capsule::connection()->getPdo();
            if ($pdo) {
                $driver = config('database.default', 'pgsql');
                return [
                    'conectado' => true,
                    'mensaje' => "Conectado ({$driver})"
                ];
            }
            return [
                'conectado' => false,
                'mensaje' => 'Sin conexión'
            ];
        } catch (Throwable $e) {
            return [
                'conectado' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function login(Request $request)
    {
        if ($request->method() === 'POST') {
            $identificador = $request->post('email'); // Puede ser email o username
            $password = $request->post('password');

            if (!$identificador || !$password) {
                return render_view('admin/layouts/auth', [
                    'title' => 'Iniciar Sesión',
                    'content' => render_view('admin/pages/login', ['error' => 'Por favor complete todos los campos'])
                ]);
            }

            // Buscar por email O por username
            $user = \app\model\User::where('email', $identificador)
                ->orWhere('username', $identificador)
                ->first();

            if (!$user || !password_verify($password, $user->password)) {
                return render_view('admin/layouts/auth', [
                    'title' => 'Iniciar Sesión',
                    'content' => render_view('admin/pages/login', ['error' => 'Credenciales inválidas'])
                ]);
            }

            // Verificar permisos básicos (si tiene rol)
            if (!$user->role) {
                return render_view('admin/layouts/auth', [
                    'title' => 'Iniciar Sesión',
                    'content' => render_view('admin/pages/login', ['error' => 'No tienes permisos de acceso'])
                ]);
            }

            // Guardar en sesión
            $session = $request->session();
            $session->set('admin_logged_in', true);
            $session->set('admin_user_id', $user->id);
            $session->set('admin_username', $user->username);

            return redirect('/admin');
        }

        // Si ya está logueado, redirigir al dashboard
        if ($request->session()->get('admin_logged_in')) {
            return redirect('/admin');
        }

        $estadoBd = $this->verificarEstadoBaseDatos();
        $content = render_view('admin/pages/login', ['estadoBd' => $estadoBd]);
        return render_view('admin/layouts/auth', [
            'title' => 'Iniciar Sesión',
            'content' => $content
        ]);
    }

    public function logout(Request $request)
    {
        $session = $request->session();
        $session->delete('admin_logged_in');
        $session->delete('admin_user_id');
        $session->delete('admin_username');

        // Opsional: destruir la sesión completamente si el driver lo soporta
        // $request->session()->destroy(); 

        return redirect('/admin/login');
    }
}
