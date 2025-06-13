<?php

declare(strict_types=1);

namespace App\Controller;

use App\View\View;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController
{
    /**
     * El contenedor de dependencias inyectará automáticamente
     * una instancia de View aquí.
     */
    public function __construct(private View $vista) {}
    /**
     * Utiliza la instancia de View inyectada para renderizar la página.
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        // La plantilla 'home.php' se encargará de todo.
        return $this->vista->render($response, 'home.php');
    }
}
