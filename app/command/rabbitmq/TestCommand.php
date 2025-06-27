<?php
// ARCHIVO NUEVO: app/command/rabbitmq/TestCommand.php

namespace app\command\rabbitmq;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class TestCommand extends Command
{
    protected static $defaultName = 'rabbitmq:test';
    protected static $defaultDescription = 'Sends a test event to RabbitMQ to verify the connection and publishing.';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Log: Iniciando prueba de publicación de evento en RabbitMQ...</info>');

        $eventName = 'test.event.' . time();
        $payload = [
            'message' => 'This is a test message from Sword.',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        try {
            // Usamos la función global que es la que se usa en la aplicación
            dispatch_event($eventName, $payload);

            $queueName = config('event.queue');
            $output->writeln("<info>Log: ¡Éxito! El evento de prueba '{$eventName}' fue despachado a la cola '{$queueName}'.</info>");
            $output->writeln('<comment>Log: Por favor, verifica la consola de administración de RabbitMQ para confirmar que el mensaje ha llegado.</comment>');
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln('<error>Error: Fallo al despachar el evento de prueba.</error>');
            $output->writeln('<error>Causa: ' . $e->getMessage() . '</error>');
            $output->writeln('<comment>Log: Revisa los siguientes puntos:');
            $output->writeln('  1. ¿Está RabbitMQ corriendo y accesible desde la aplicación?');
            $output->writeln('  2. ¿Son correctas las credenciales en tu archivo .env (RABBITMQ_HOST, PORT, USER, PASS, VHOST)?');
            $output->writeln('  3. ¿Existe el virtual host especificado en RabbitMQ?');
            $output->writeln('  4. Revisa los logs en `runtime/logs/events.log` y `runtime/logs/master.log` para más detalles.</comment>');
            return Command::FAILURE;
        }
    }
}
