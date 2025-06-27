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
    private static ?object $instance = null; // Acepta cualquier objeto para permitir mocks
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
        $this->connect();
    }

    /**
     * Establishes the connection to RabbitMQ.
     */
    private function connect(): void
    {
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
     * Checks if the RabbitMQ connection is active.
     */
    private function isConnected(): bool
    {
        return $this->connection && $this->connection->isConnected() && $this->channel && $this->channel->is_open();
    }

    /**
     * Publishes a new audio processing job to Casiel's work queue.
     *
     * @param integer $contentId The ID of the content entry.
     * @param integer $mediaId The ID of the associated media file.
     * @return void
     * @throws \Exception if the RabbitMQ channel is not available after attempting to reconnect.
     */
    public function notifyNewAudio(int $contentId, int $mediaId): void
    {
        if (!$this->isConnected()) {
            Log::channel('master')->warning('CasielService: Conexión con RabbitMQ perdida. Intentando reconectar...');
            $this->close(); 
            $this->connect();

            if (!$this->isConnected()) {
                throw new \Exception('CasielService: No se pudo restablecer la conexión con RabbitMQ. El mensaje no se enviará.');
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
            if ($this->channel && $this->channel->is_open()) $this->channel->close();
            if ($this->connection && $this->connection->isConnected()) $this->connection->close();
        } catch (Throwable $e) {
            // Ignore exceptions on shutdown
        } finally {
            $this->channel = null;
            $this->connection = null;
        }
    }
    
    /**
     * Allows replacing the singleton instance with a mock object for testing.
     * WARNING: This should ONLY be used in a test environment.
     *
     * @param object|null $instance The mock instance or null to reset.
     */
    public static function setInstanceForTesting(?object $instance): void
    {
        self::$instance = $instance;
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