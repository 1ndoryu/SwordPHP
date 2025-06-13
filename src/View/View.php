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
        $content = $this->engine->render($template, $data);
        $response->getBody()->write($content);
        return $response;
    }
}