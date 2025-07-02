<?php
// app/command/dev/RunJophielSyncCommand.php

namespace app\command\dev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use support\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use app\process\JophielSyncProcess;
use app\bootstrap\Database;
use ReflectionClass;
use Throwable;

class RunJophielSyncCommand extends Command
{
    // Nombre del comando disponible en la CLI
    protected static $defaultName        = 'dev:jophiel-sync';
    protected static $defaultDescription = 'Ejecuta manualmente JophielSyncProcess y muestra logs detallados en consola.';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Log: Iniciando ejecución manual de JophielSyncProcess...</info>');

        /* ------------------------------------------------------------------
         * 1) Redirigir los logs del canal "master" a la consola para
         *    tener feedback en tiempo real durante la prueba.
         * ------------------------------------------------------------------*/
        try {
            $consoleHandler = new StreamHandler('php://stdout', Logger::DEBUG);
            Log::channel('master')->pushHandler($consoleHandler);
        } catch (Throwable $e) {
            $output->writeln('<comment>Advertencia: No fue posible adjuntar el handler de consola al canal de logs. Mensaje: ' . $e->getMessage() . '</comment>');
        }

        /* ------------------------------------------------------------------
         * 2) Asegurar que la base de datos esté inicializada, pues el proceso
         *    la usará a través del modelo Content.
         * ------------------------------------------------------------------*/
        Database::start(null);

        /* ------------------------------------------------------------------
         * 3) Instanciar el proceso y configurar sus dependencias privadas
         *    (principalmente la configuración de la API de Jophiel) usando
         *    reflexión, ya que onWorkerStart() no será invocado aquí.
         * ------------------------------------------------------------------*/
        $syncProcess = new JophielSyncProcess();

        try {
            // Inyectar configuraciones privadas mediante Reflection.
            $reflection = new ReflectionClass($syncProcess);

            if ($reflection->hasProperty('jophielApiConfig')) {
                $prop = $reflection->getProperty('jophielApiConfig');
                $prop->setAccessible(true);
                $prop->setValue($syncProcess, config('jophiel.api'));
            }
        } catch (Throwable $e) {
            $output->writeln('<comment>Advertencia: No se pudo asignar configuración a la propiedad privada jophielApiConfig. Se utilizarán valores por defecto. Mensaje: ' . $e->getMessage() . '</comment>');
        }

        /* ------------------------------------------------------------------
         * 4) Ejecutar la sincronización y capturar cualquier excepción.
         * ------------------------------------------------------------------*/
        try {
            $syncProcess->runSync();
            $output->writeln('<info>Log: Sincronización completada. Revisa la salida arriba para más detalles.</info>');
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln('<error>Error crítico durante la sincronización.</error>');
            $output->writeln('Mensaje: ' . $e->getMessage());
            $output->writeln('Archivo: ' . $e->getFile() . ' en la línea ' . $e->getLine());
            return Command::FAILURE;
        }
    }
} 