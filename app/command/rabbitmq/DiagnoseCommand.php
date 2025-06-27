<?php

namespace app\command\rabbitmq;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class DiagnoseCommand extends Command
{
    protected static $defaultName = 'rabbitmq:diagnose';
    protected static $defaultDescription = 'Diagnoses the connection to RabbitMQ using .env credentials.';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Log: Iniciando diagnóstico de conexión con RabbitMQ...</info>');

        $connection_timeout = (int)env('RABBITMQ_CONNECTION_TIMEOUT', 5); // Leemos el nuevo timeout

        $config = [
            'host' => env('RABBITMQ_HOST'),
            'port' => env('RABBITMQ_PORT'),
            'user' => env('RABBITMQ_USER'),
            'pass' => env('RABBITMQ_PASS'),
            'vhost' => env('RABBITMQ_VHOST'),
            'work_queue' => env('RABBITMQ_WORK_QUEUE'),
            'events_queue' => env('RABBITMQ_EVENTS_QUEUE'),
        ];

        $output->writeln('<comment>Log: Usando la siguiente configuración (cargada desde .env):</comment>');
        $output->writeln([
            "  - HOST: <fg=cyan>{$config['host']}</>",
            "  - PORT: <fg=cyan>{$config['port']}</>",
            "  - USER: <fg=cyan>{$config['user']}</>",
            "  - VHOST: <fg=cyan>{$config['vhost']}</>",
            "  - TIMEOUT: <fg=cyan>{$connection_timeout} segundos</>", // Mostramos el timeout
        ]);
        $output->writeln('');

        if (!$config['host'] || !$config['port'] || !$config['user'] || !$config['pass']) {
            $output->writeln('<error>Error: Faltan una o más variables de entorno requeridas (RABBITMQ_HOST, PORT, USER, PASS).</error>');
            return Command::FAILURE;
        }

        try {
            $output->writeln('Log: Intentando conectar...');
            $connection = new AMQPStreamConnection(
                $config['host'],
                $config['port'],
                $config['user'],
                $config['pass'],
                $config['vhost'],
                false,              // insist
                'AMQPLAIN',         // login_method
                null,               // login_response
                'en_US',            // locale
                $connection_timeout, // connection_timeout <-- USAMOS EL VALOR
                $connection_timeout,  // read_write_timeout <-- USAMOS EL VALOR
                null,
                false,
                0
            );

            if ($connection->isConnected()) {
                $output->writeln('<info>===========================================</info>');
                $output->writeln('<info>¡ÉXITO! La conexión con RabbitMQ se estableció correctamente.</info>');
                $output->writeln('<info>===========================================</info>');
                $output->writeln('Log: Cerrando conexión de prueba.');
                $connection->close();
                return Command::SUCCESS;
            }

            $output->writeln('<error>Error: La conexión no se pudo establecer por una razón desconocida (isConnected() devolvió false).</error>');
            return Command::FAILURE;

        } catch (Throwable $e) {
            $output->writeln('<error>===============================================================</error>');
            $output->writeln('<error>¡FALLO! No se pudo conectar a RabbitMQ. Causa del error:</error>');
            $output->writeln("<error>{$e->getMessage()}</error>");
            $output->writeln('<error>===============================================================</error>');
            return Command::FAILURE;
        }
    }
}