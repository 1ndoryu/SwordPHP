<?php

namespace App\service;

use support\Db;
use support\Log;
use Throwable;

/**
 * Servicio para gestionar las operaciones relacionadas con la base de datos.
 */
class DatabaseService
{
    /**
     * Verifica el estado de la conexión a la base de datos por defecto.
     * Registra el resultado en el canal de log 'database'.
     *
     * @return bool True si la conexión es exitosa, false en caso contrario.
     */
    public function verificarConexion(): bool
    {
        try {
            // Intenta obtener el objeto PDO para forzar la conexión.
            // Usamos 'mysql' que es el nombre de tu conexión en config/database.php
            Db::connection('pgsql')->getPDO();

            Log::channel('database')->info('Verificación de conexión a BD exitosa.');

            return true;

        } catch (Throwable $e) {
            // Registra el error en el canal de log específico.
            Log::channel('database')->error(
                'Fallo en la conexión a la BD.',
                ['error' => $e->getMessage()]
            );

            return false;
        }
    }
}