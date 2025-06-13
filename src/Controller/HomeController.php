<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Database;
use App\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController
{
    public function __construct(
        private View $view,
        private Database $database
    ) {}

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $pdo = $this->database->getConnection();
        $stmt = $pdo->query('SELECT version()');
        $dbVersion = $stmt->fetchColumn();

        return $this->view->render($response, 'home', [
            'dbVersion' => $dbVersion
        ]);
    }
}
