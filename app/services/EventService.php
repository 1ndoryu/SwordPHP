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
        $this->connect();

        // Register a shutdown function to gracefully close the connection.
        register_shutdown_function([$this, 'close']);
    }

    /**
     * Establishes a fresh connection and channel to RabbitMQ.
     * Any previous connection will be closed beforehand.
     *
     * @return void
     */
    private function connect(): void
    {
        // Cierra cualquier conexión previa por seguridad
        $this->close();

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

            // Declare the queue as durable to persist messages across restarts
            $this->channel->queue_declare($config['queue'], false, true, false, false);

            Log::channel('events')->info('EventService: Conexión con RabbitMQ establecida.');
        } catch (Throwable $e) {
            Log::channel('events')->critical('EventService: No se pudo conectar a RabbitMQ.', [
                'error' => $e->getMessage()
            ]);
            $this->connection = null;
            $this->channel = null;
        }
    }

    /**
     * Checks if both the connection and channel are open.
     */
    private function isConnected(): bool
    {
        return $this->connection && $this->connection->isConnected() && $this->channel && $this->channel->is_open();
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
        // Reconecta si es necesario
        if (!$this->isConnected()) {
            Log::channel('events')->warning('EventService: Conexión perdida. Intentando reconectar...');
            $this->connect();

            // Si aún no hay conexión tras el intento, abortar silenciosamente para no romper el flujo principal
            if (!$this->isConnected()) {
                Log::channel('events')->error("EventService: Sin conexión a RabbitMQ. Evento descartado: {$eventName}");
                return;
            }
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

        try {
            $this->channel->basic_publish($message, '', $queueName);
            Log::channel('events')->info("EventService: Evento despachado: {$eventName}");
        } catch (Throwable $e) {
            // Intentar una reconexión rápida y un segundo intento
            Log::channel('events')->warning('EventService: Error al publicar, intentando reintentar...', [
                'error' => $e->getMessage()
            ]);

            $this->connect();

            if ($this->isConnected()) {
                try {
                    $this->channel->basic_publish($message, '', $queueName);
                    Log::channel('events')->info("EventService: Evento despachado tras reconexión: {$eventName}");
                } catch (Throwable $e2) {
                    // Falla definitiva
                    Log::channel('events')->error("EventService: Fallo definitivo al despachar evento: {$eventName}", [
                        'error' => $e2->getMessage(),
                        'payload' => $payload
                    ]);
                }
            } else {
                Log::channel('events')->error("EventService: No se pudo reconectar para despachar evento: {$eventName}");
            }
        }
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
