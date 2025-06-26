<?php

namespace app\services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use support\Log;
use Throwable;

class EventService
{
    private static ?self $instance = null;
    private ?AMQPStreamConnection $connection = null;
    private ?\PhpAmqpLib\Channel\AMQPChannel $channel = null;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
        try {
            $config = config('event');
            $this->connection = new AMQPStreamConnection(
                $config['rabbitmq']['host'],
                $config['rabbitmq']['port'],
                $config['rabbitmq']['user'],
                $config['rabbitmq']['password'],
                $config['rabbitmq']['vhost']
            );
            $this->channel = $this->connection->channel();

            // Declare a durable queue to make sure messages are not lost if RabbitMQ restarts.
            $this->channel->queue_declare($config['queue'], false, true, false, false);

            Log::channel('events')->info('Conexión con RabbitMQ establecida y canal abierto.');

            // Register a shutdown function to gracefully close the connection.
            register_shutdown_function([$this, 'close']);
        } catch (Throwable $e) {
            Log::channel('events')->critical('No se pudo establecer la conexión con RabbitMQ', [
                'error' => $e->getMessage()
            ]);
            // Set connection to null so we know it failed.
            $this->connection = null;
        }
    }

    /**
     * Gets the singleton instance of the EventService.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Publishes an event to the configured queue.
     *
     * @param string $eventName
     * @param array $payload
     * @return void
     * @throws \Exception if the connection is not available.
     */
    public function dispatch(string $eventName, array $payload): void
    {
        if (!$this->connection) {
            throw new \Exception('La conexión con RabbitMQ no está disponible.');
        }

        $messageBody = json_encode([
            'event_name' => $eventName,
            'payload' => $payload,
            'timestamp' => time(),
            'source' => 'sword-v2'
        ]);

        $message = new AMQPMessage(
            $messageBody,
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT] // Make message persistent
        );

        $queueName = config('event.queue');
        $this->channel->basic_publish($message, '', $queueName);
    }

    /**
     * Gracefully closes the channel and connection.
     */
    public function close(): void
    {
        if ($this->channel) {
            $this->channel->close();
            $this->channel = null;
        }
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
            Log::channel('events')->info('Conexión con RabbitMQ cerrada.');
        }
    }

    /**
     * Called on bootstrap to initialize the service.
     */
    public static function start($worker): void
    {
        if ($worker) {
            self::getInstance();
        }
    }
}
