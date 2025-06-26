<?php

namespace app\controller;

use support\Request;
use support\Response;
use support\Log;
use Throwable;

class SystemController
{
    /**
     * Handles the request to install the database.
     */
    public function install(Request $request): Response
    {
        try {
            return $this->runShellCommand('db:install');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Handles the request to reset the database.
     */
    public function reset(Request $request): Response
    {
        try {
            return $this->runShellCommand('db:reset', ['--force']);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Executes a CLI command using shell_exec for proper decoupling.
     */
    private function runShellCommand(string $commandName, array $options = []): Response
    {
        $allowedCommands = ['db:install', 'db:reset'];
        if (!in_array($commandName, $allowedCommands)) {
            return api_response(
                false,
                'Comando no permitido.',
                ['output' => "El comando '{$commandName}' no está en la lista blanca."],
                403
            );
        }

        $phpBinary = '"' . PHP_BINARY . '"';
        $scriptPath = escapeshellarg(base_path('webman'));
        $commandNameArg = escapeshellarg($commandName);
        $optionString = !empty($options) ? ' ' . implode(' ', array_map('escapeshellarg', $options)) : '';
        $fullCommand = "{$phpBinary} {$scriptPath} {$commandNameArg}{$optionString} 2>&1";

        Log::channel('master')->info("Ejecutando comando de sistema", ['command' => $fullCommand]);
        $output = shell_exec($fullCommand);

        if ($output === null) {
            Log::channel('master')->error("Error al ejecutar shell_exec", [
                'command' => $fullCommand,
                'message' => 'shell_exec devolvió null. Verificar disable_functions en php.ini.'
            ]);
            return api_response(
                false,
                "Error al ejecutar el comando [{$commandName}].",
                ['output' => 'No se recibió ninguna salida. Verifique los logs y la configuración del servidor.'],
                500
            );
        }

        Log::channel('master')->info("Salida del comando de sistema", ['command' => $commandName, 'output' => $output]);

        return api_response(
            true,
            "Comando [$commandName] ejecutado.",
            ['output' => trim($output)]
        );
    }

    /**
     * Creates a JSON response directly from an error.
     */
    private function handleException(Throwable $e): Response
    {
        $error_details = [
            'class'   => get_class($e),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ];

        Log::channel('master')->critical('Excepción no controlada en SystemController', $error_details);

        return api_response(false, 'Ha ocurrido un error crítico.', $error_details, 500);
    }
}
