<?php

namespace app\command\rabbitmq;

use app\services\CasielService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class TestWorkerCommand extends Command
{
    protected static $defaultName = 'rabbitmq:test-worker';
    protected static $defaultDescription = 'Sends a test job to the Casiel worker queue (kamples_queue).';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Log: Iniciando prueba de publicación a la cola de trabajadores...</info>');

        $contentId = rand(1000, 9999);
        $mediaId = rand(1000, 9999);

        try {
            // Usamos el servicio de Casiel directamente
            $casielService = CasielService::getInstance();
            $casielService->notifyNewAudio($contentId, $mediaId);

            $queueName = env('RABBITMQ_WORK_QUEUE');
            $output->writeln("<info>Log: ¡Éxito! El trabajo de prueba para content_id:{$contentId} fue despachado a la cola '{$queueName}'.</info>");
            $output->writeln('<comment>Log: Verifica la consola de RabbitMQ para confirmar que el mensaje ha llegado.</comment>');
            
            // Cerrar la conexión explícitamente al final del script
            $casielService->close();
            
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln('<error>Error: Fallo al despachar el trabajo de prueba.</error>');
            $output->writeln('<error>Causa: ' . $e->getMessage() . '</error>');
            $output->writeln('<comment>Log: Revisa la conectividad y credenciales de RabbitMQ en tu .env.</comment>');
            return Command::FAILURE;
        }
    }
}