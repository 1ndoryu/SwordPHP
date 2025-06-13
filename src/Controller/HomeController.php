<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Database;
use App\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class HomeController
{
    public function __construct(
        private View $view,
        private Database $database,
        private readonly LoggerInterface $logger
    ) {}

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

        $this->logger->info('Acceso a la página de inicio.');
        
        $pdo = $this->database->getConnection();
        $stmt = $pdo->query('SELECT version()');
        $dbVersion = $stmt->fetchColumn();

        return $this->view->render($response, 'home', [
            'dbVersion' => $dbVersion
        ]);
    }
}
