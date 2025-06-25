<?php

namespace App\Services; // Asegúrate de que el namespace coincida con tu estructura

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class CasielEventPublisher
{
    private ?AMQPStreamConnection $connection = null;
    private array $config;

    public function __construct()
    {
        // Cargar la configuración desde las variables de entorno de SwordPHP
        $this->config = [
            'host' => $_ENV['RABBITMQ_HOST'] ?? 'localhost',
            'port' => $_ENV['RABBITMQ_PORT'] ?? 5672,
            'user' => $_ENV['RABBITMQ_USER'] ?? 'guest',
            'password' => $_ENV['RABBITMQ_PASS'] ?? 'guest',
            'vhost' => $_ENV['RABBITMQ_VHOST'] ?? '/',
            'queue' => 'casiel_processing_queue',
            'dlx_exchange' => 'casiel_dlx', // Dead Letter Exchange
            'dlq_queue' => 'casiel_dead_letter_queue' // Dead Letter Queue
        ];
    }

    public function publicarNuevoSample(int $idSample): bool
    {
        try {
            $this->connection = new AMQPStreamConnection(
                $this->config['host'],
                $this->config['port'],
                $this->config['user'],
                $this->config['password'],
                $this->config['vhost']
            );

            $channel = $this->connection->channel();

            // 1. Declara el Dead Letter Exchange (DLX) y la Dead Letter Queue (DLQ)
            $channel->exchange_declare($this->config['dlx_exchange'], 'direct', false, true, false);
            $channel->queue_declare($this->config['dlq_queue'], false, true, false, false);
            $channel->queue_bind($this->config['dlq_queue'], $this->config['dlx_exchange'], $this->config['queue']);

            // 2. Declara la cola principal y la asocia con el DLX
            $queue_args = new \PhpAmqpLib\Wire\AMQPTable([
                'x-dead-letter-exchange' => $this->config['dlx_exchange'],
                'x-dead-letter-routing-key' => $this->config['queue']
            ]);
            $channel->queue_declare($this->config['queue'], false, true, false, false, false, $queue_args);

            $payload = json_encode(['id_sample' => $idSample]);
            $message = new AMQPMessage($payload, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

            $channel->basic_publish($message, '', $this->config['queue']);

            $channel->close();
            $this->connection->close();

            \support\Log::info("Evento para Casiel publicado con éxito. Sample ID: $idSample");
            return true;
        } catch (Throwable $e) {
            \support\Log::error("Fallo al publicar evento RabbitMQ para Casiel: " . $e->getMessage());
            return false;
        }
    }
}
