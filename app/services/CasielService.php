<?php

namespace app\services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use support\Log;
use Throwable;

/**
 * Service dedicated to publishing messages for the Casiel worker.
 * Implemented as a Singleton to ensure a persistent connection.
 */
class CasielService
{
    private static ?self $instance = null;
    private ?AMQPStreamConnection $connection = null;
    private ?\PhpAmqpLib\Channel\AMQPChannel $channel = null;
    private ?string $queueName;

    /**
     * Private constructor to prevent direct instantiation and establish the connection.
     */
    private function __construct()
    {
        $this->queueName = env('RABBITMQ_WORK_QUEUE');
        if (empty($this->queueName)) {
            Log::channel('master')->error('La cola de trabajos de Casiel (RABBITMQ_WORK_QUEUE) no está configurada en .env');
            $this->connection = null;
            return;
        }

        try {
            $this->connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST'),
                env('RABBITMQ_PORT'),
                env('RABBITMQ_USER'),
                env('RABBITMQ_PASS'),
                env('RABBITMQ_VHOST')
            );
            $this->channel = $this->connection->channel();
            $this->channel->queue_declare($this->queueName, false, true, false, false);

            Log::channel('master')->info('CasielService: Conexión con RabbitMQ establecida y canal abierto.');

            // This is handled by Workerman's lifecycle now, not shutdown function.
        } catch (Throwable $e) {
            Log::channel('master')->error('CasielService: No se pudo conectar con RabbitMQ', ['error' => $e->getMessage()]);
            $this->connection = null;
        }
    }

    /**
     * Gets the singleton instance of the CasielService.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Publishes a new audio processing job to Casiel's work queue.
     *
     * @param integer $contentId The ID of the content entry.
     * @param integer $mediaId The ID of the associated media file.
     * @return void
     * @throws \Exception if the RabbitMQ channel is not available.
     */
    public function notifyNewAudio(int $contentId, int $mediaId): void
    {
        if (!$this->channel) {
            // Attempt to reconnect if connection was lost.
            Log::channel('master')->warning('CasielService: El canal de RabbitMQ no estaba disponible. Intentando reconectar...');
            $this->__construct(); // Recal lthe constructor to re-initialize
            if (!$this->channel) {
                 throw new \Exception('CasielService: El canal de RabbitMQ no está disponible. Revisa los logs de conexión.');
            }
        }

        $payload = [
            'data' => [
                'content_id' => $contentId,
                'media_id'   => $mediaId,
            ]
        ];
        $messageBody = json_encode($payload);

        $message = new AMQPMessage(
            $messageBody,
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        $this->channel->basic_publish($message, '', $this->queueName);
        Log::channel('content')->info('Notificación para Casiel publicada en la cola.', [
            'queue' => $this->queueName,
            'payload' => $payload
        ]);
    }

    /**
     * Gracefully closes the channel and connection.
     */
    public function close(): void
    {
        try {
            if ($this->channel) $this->channel->close();
            if ($this->connection) $this->connection->close();
            $this->channel = null;
            $this->connection = null;
            Log::channel('master')->info('CasielService: Conexión con RabbitMQ cerrada.');
        } catch (Throwable $e) {
            // Ignore exceptions on shutdown
        }
    }
    
    /**
     * Make clone private to prevent cloning the instance.
     */
    private function __clone() {}

    /**
     * Make wakeup private to prevent unserializing the instance.
     */
    public function __wakeup() {}
}