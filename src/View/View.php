<?php

declare(strict_types=1);

namespace App\View;

use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;

class View
{
    private string $path;

    /**
     * El constructor ahora es inyectado explícitamente por la
     * configuración del contenedor en index.php.
     *
     * @param array $settings
     */
    public function __construct(private array $settings)
    {
        $this->path = $settings['templates']['path'];
    }

    /**
     * Renderiza una plantilla y la escribe en la respuesta.
     *
     * @param Response $response   El objeto de respuesta PSR-7.
     * @param string   $template   El nombre del archivo de la plantilla a renderizar.
     * @param array    $data       Datos para pasar a la plantilla.
     * @return Response
     */
    public function render(Response $response, string $template, array $data = []): Response
    {
        $templatePath = $this->path . '/' . $template;

        if (!file_exists($templatePath)) {
            throw new RuntimeException("La plantilla '{$templatePath}' no existe.");
        }

        // Extrae los datos del array a variables individuales (ej: $data['titulo'] se convierte en $titulo)
        extract($data);

        // Inicia el buffer de salida para capturar el HTML renderizado
        ob_start();
        
        // Incluye el archivo de la plantilla. Sus variables ahora son accesibles.
        include $templatePath;
        
        // Obtiene el contenido del buffer y lo limpia
        $output = ob_get_clean();

        $response->getBody()->write($output);
        return $response;
    }
}