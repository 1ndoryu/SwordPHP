<?php

namespace app\controller\Admin;

use support\Request;
use support\Response;

class AuthController
{
    public function login(Request $request)
    {
        if ($request->method() === 'POST') {
            // TODO: Implementar lógica de login real
            return redirect('/admin');
        }

        $content = render_view('admin/pages/login');
        return render_view('admin/layouts/auth', [
            'title' => 'Iniciar Sesión',
            'content' => $content
        ]);
    }
}
