<?php

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AdminAuth implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $session = $request->session();

        if (!$session->get('admin_logged_in') && $request->path() !== '/admin/login') {
            return redirect('/admin/login');
        }

        return $handler($request);
    }
}
