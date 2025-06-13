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
    public function __construct(private View $vista)
    {
    }

    /**
     * Utiliza la instancia de View inyectada para renderizar la página.
     */
    public function home(Request $request, Response $response, array $args): Response
    {
        // Preparamos los datos
        $tituloPagina = '¡Inyección de Dependencias!';
        $contenidoHtml = '<h1>¡Éxito!</h1><p>Esta página fue renderizada por una instancia de <code>View</code> que fue inyectada automáticamente por el contenedor en nuestro <code>HomeController</code>.</p>';

        // Usamos la vista que recibimos en el constructor
        return $this->vista->render($response, $tituloPagina, $contenidoHtml);
    }
}
