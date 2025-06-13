<?php

declare(strict_types=1);

namespace App\View;

use Psr\Http\Message\ResponseInterface;
use League\Plates\Engine;

class View
{
    private Engine $engine;

    public function __construct(string $path)
    {
        $this->engine = new Engine($path);
    }

    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        // Comparte los datos con todas las plantillas (incluyendo layouts) para esta renderización.
        $this->engine->addData($data);

        $content = $this->engine->render($template);
        $response->getBody()->write($content);
        return $response;
    }
}