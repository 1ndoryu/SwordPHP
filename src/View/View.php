<?php
declare(strict_types=1);

namespace App\View;

use Psr\Http\Message\ResponseInterface;

class View
{
    protected string $path;

    public function __construct(string $path)
    {
        $this->path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        ob_start();

        // Extrae las variables para que estén disponibles en la plantilla
        extract($data, EXTR_SKIP);

        // Incluye la plantilla
        require $this->path . $template;

        $output = ob_get_clean();
        $response->getBody()->write($output);

        return $response;
    }
}