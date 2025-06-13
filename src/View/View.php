<?php

declare(strict_types=1);

namespace App\View;

use Psr\Http\Message\ResponseInterface as Response;

class View
{
    /**
     * Genera una página HTML completa y la escribe en la respuesta.
     *
     * @param Response $response   El objeto de respuesta PSR-7.
     * @param string   $titulo     El título para la etiqueta <title>.
     * @param string   $contenido  El HTML para el cuerpo de la página.
     * @return Response
     */
    public function render(Response $response, string $titulo, string $contenido): Response
    {
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$titulo}</title>
            <style>
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
                    background-color: #f0f2f5; 
                    color: #1c1e21; 
                    line-height: 1.6; 
                    margin: 0;
                    padding: 20px; 
                }
                .container { 
                    max-width: 800px; 
                    margin: 20px auto; 
                    background: #fff; 
                    padding: 2rem; 
                    border-radius: 8px; 
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                    border: 1px solid #dddfe2;
                }
                h1 {
                    color: #0d6efd;
                }
            </style>
        </head>
        <body>
            <div class="container">
                {$contenido}
            </div>
        </body>
        </html>
        HTML;

        $response->getBody()->write($html);
        return $response;
    }
}
