<?php

namespace app\command\rabbitmq;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class TestCommand extends Command
{
    protected static $defaultName = 'rabbitmq:test';
    protected static $defaultDescription = 'Sends a test event to the internal events queue to verify connection.';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Log: Iniciando prueba de publicación a la cola de eventos internos...</info>');

        $eventName = 'test.event.' . time();
        $payload = [
            'message' => 'This is a test event from Sword.',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        try {
            // Usamos la función global que es la que se usa en la aplicación
            rabbit_event($eventName, $payload);

            $queueName = config('event.queue');
            $output->writeln("<info>Log: ¡Éxito! El evento de prueba '{$eventName}' fue despachado a la cola '{$queueName}'.</info>");
            $output->writeln('<comment>Log: Verifica la consola de RabbitMQ para confirmar que el mensaje ha llegado.</comment>');
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln('<error>Error: Fallo al despachar el evento de prueba.</error>');
            $output->writeln('<error>Causa: ' . $e->getMessage() . '</error>');
            $output->writeln('<comment>Log: Revisa la configuración de RabbitMQ en .env y config/event.php.</comment>');
            return Command::FAILURE;
        }
    }
}