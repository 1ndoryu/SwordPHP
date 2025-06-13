<?
declare(strict_types=1);

namespace App\View;

use League\Plates\Engine;

class View
{
    private Engine $engine;

    public function __construct(string $path)
    {
        $this->engine = new Engine($path);
    }

    public function render(string $template, array $data = []): string
    {
        return $this->engine->render($template, $data);
    }
}