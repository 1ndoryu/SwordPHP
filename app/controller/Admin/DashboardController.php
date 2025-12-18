<?php

namespace app\controller\Admin;

use support\Request;
use support\Response;

class DashboardController
{
    public function index(Request $request)
    {
        if ($request->header('accept') === 'application/json') {
            return json([
                'title' => 'Dashboard',
                'widgets' => [] // Placeholder for widgets
            ]);
        }

        $content = render_view('admin/pages/dashboard');
        return render_view('admin/layouts/layout', [
            'title' => 'Dashboard',
            'user' => $request->session()->get('admin_username') ?? 'Admin',
            'content' => $content
        ]);
    }
}
