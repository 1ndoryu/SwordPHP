<?php

declare(strict_types=1);

namespace App\Service;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;

    public function __construct(Config $config)
    {
        $dbConfig = $config->get('db');

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s',
            $dbConfig['driver'],
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database']
        );

        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO(
                $dsn,
                $dbConfig['username'],
                $dbConfig['password'],
                $defaultOptions
            );
        } catch (PDOException $e) {
            // En un futuro, aquí podríamos loguear el error.
            // Por ahora, relanzamos la excepción para detener la ejecución.
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
