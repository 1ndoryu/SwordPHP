<?php
declare(strict_types=1);

namespace App\Service;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class Database
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly Config $config,
        private readonly LoggerInterface $logger
    ) {
        try {
            $dbConfig = $this->config->get('db');
            $dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
            
            $this->pdo = new PDO(
                $dsn,
                $dbConfig['username'],
                $dbConfig['password']
            );
            
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->critical('Error de conexión a la base de datos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            die('Error de aplicación crítico. Contacte al administrador.');
        }
    }

    public function getConnection(): ?PDO
    {
        return $this->pdo;
    }
}