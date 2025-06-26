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
            // The ResetCommand requires a --force flag to run.
            return $this->runShellCommand('db:reset', ['--force']);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Executes a CLI command using shell_exec for proper decoupling.
     *
     * @param string $commandName The name of the command, e.g., 'db:install'.
     * @param array $options An array of options, e.g., ['--force'].
     * @return Response
     */
    private function runShellCommand(string $commandName, array $options = []): Response
    {
        // For security, only allow a whitelist of commands to be executed.
        $allowedCommands = ['db:install', 'db:reset'];
        if (!in_array($commandName, $allowedCommands)) {
            return json([
                'success' => false,
                'message' => 'Comando no permitido.',
                'output'  => "El comando '{$commandName}' no está en la lista blanca."
            ], 403);
        }

        // Wrap the PHP binary path in quotes to handle spaces in Windows paths.
        $phpBinary = '"' . PHP_BINARY . '"';

        // --- INICIO DE LA CORRECCIÓN ---
        // El script 'webman' es el punto de entrada correcto para los comandos de consola.
        // Es multiplataforma y gestiona el arranque necesario para los comandos.
        $scriptPath = escapeshellarg(base_path('webman'));
        // --- FIN DE LA CORRECCIÓN ---

        $commandNameArg = escapeshellarg($commandName);

        // Build the options string safely
        $optionString = '';
        if (!empty($options)) {
            $optionString = ' ' . implode(' ', array_map('escapeshellarg', $options));
        }

        // We redirect stderr to stdout (2>&1) to capture any potential errors from the script.
        $fullCommand = "{$phpBinary} {$scriptPath} {$commandNameArg}{$optionString} 2>&1";

        Log::channel('master')->info("Ejecutando comando de sistema", ['command' => $fullCommand]);

        // Execute the command
        $output = shell_exec($fullCommand);

        if ($output === null) {
            Log::channel('master')->error("Error al ejecutar shell_exec", [
                'command' => $fullCommand,
                'message' => 'shell_exec devolvió null. Verificar disable_functions en php.ini.'
            ]);
            return json([
                'success' => false,
                'message' => "Error al ejecutar el comando [{$commandName}].",
                'output'  => 'No se recibió ninguna salida. Verifique los logs y la configuración del servidor.'
            ], 500);
        }
        
        Log::channel('master')->info("Salida del comando de sistema", ['command' => $commandName, 'output' => $output]);

        return json([
            'success'  => true,
            'message'  => "Comando [$commandName] ejecutado.",
            'output'   => trim($output)
        ]);
    }

    /**
     * Creates a JSON response directly from an error, skipping the logging system.
     */
    private function handleException(Throwable $e): Response
    {
        $error_details = [
            'success' => false,
            'message' => 'Ha ocurrido un error crítico. Ver detalles.',
            'error_details' => [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]
        ];
        
        // Log the exception for record-keeping.
        Log::channel('master')->critical('Excepción no controlada en SystemController', $error_details);

        return new Response(500, [
            'Content-Type' => 'application/json'
        ], json_encode($error_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}