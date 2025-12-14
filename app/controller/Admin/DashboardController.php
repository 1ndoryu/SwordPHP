<?php

namespace app\controller\Admin;

use support\Request;
use support\Response;

class DashboardController
{
    public function index(Request $request)
    {
        $content = render_view('admin/pages/dashboard');
        return render_view('admin/layouts/layout', [
            'title' => 'Dashboard',
            'user' => $request->session()->get('admin_username') ?? 'Admin',
            'content' => $content
        ]);
    }
}
