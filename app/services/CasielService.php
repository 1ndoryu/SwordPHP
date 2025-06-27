<?php

namespace app\services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use support\Log;
use Throwable;

/**
 * Service dedicated to publishing messages for the Casiel worker.
 */
class CasielService
{
    private ?AMQPStreamConnection $connection = null;
    private ?\PhpAmqpLib\Channel\AMQPChannel $channel = null;
    private ?string $queueName;

    /**
     * Establishes the connection to RabbitMQ upon instantiation.
     */
    public function __construct()
    {
        $this->queueName = env('RABBITMQ_WORK_QUEUE');
        if (empty($this->queueName)) {
            Log::channel('master')->error('La cola de trabajos de Casiel (RABBITMQ_WORK_QUEUE) no está configurada en .env');
            // Prevent further execution if config is missing
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

            // Declare the queue to ensure it exists. It must be durable.
            $this->channel->queue_declare($this->queueName, false, true, false, false);

            // Gracefully close the connection on script shutdown.
            register_shutdown_function([$this, 'close']);
        } catch (Throwable $e) {
            Log::channel('master')->error('CasielService: No se pudo conectar con RabbitMQ', ['error' => $e->getMessage()]);
            $this->connection = null;
        }
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
            throw new \Exception('CasielService: El canal de RabbitMQ no está disponible. Revisa los logs de conexión.');
        }

        // Create the specific payload format that Casiel expects.
        $payload = [
            'data' => [
                'content_id' => $contentId,
                'media_id'   => $mediaId,
            ]
        ];
        $messageBody = json_encode($payload);

        $message = new AMQPMessage(
            $messageBody,
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT] // Make message persistent
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
        } catch (Throwable $e) {
            // Ignore exceptions on shutdown
        }
    }
}
