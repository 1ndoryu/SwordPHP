<?php
// NUEVO ARCHIVO: app/services/JophielService.php

namespace app\services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use support\Log;
use Throwable;

/**
 * Service dedicated to publishing events to the Jophiel-compatible topic exchange.
 * Implemented as a Singleton.
 */
class JophielService
{
    private static ?self $instance = null;
    private ?AMQPStreamConnection $connection = null;
    private ?\PhpAmqpLib\Channel\AMQPChannel $channel = null;
    private array $config;

    private function __construct()
    {
        $this->config = config('jophiel');
        $this->connect();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void
    {
        try {
            $rabbitmqConfig = config('event.rabbitmq');
            $this->connection = new AMQPStreamConnection(
                $rabbitmqConfig['host'],
                $rabbitmqConfig['port'],
                $rabbitmqConfig['user'],
                $rabbitmqConfig['password'],
                $rabbitmqConfig['vhost']
            );
            $this->channel = $this->connection->channel();
            $this->channel->exchange_declare(
                $this->config['exchange']['name'],
                $this->config['exchange']['type'],
                false, // passive
                true,  // durable
                false  // auto_delete
            );
            Log::channel('events')->info('JophielService: Conexión con RabbitMQ y exchange declarado.', ['exchange' => $this->config['exchange']['name']]);
        } catch (Throwable $e) {
            Log::channel('events')->critical('JophielService: No se pudo conectar a RabbitMQ o declarar el exchange.', ['error' => $e->getMessage()]);
            $this->connection = null;
        }
    }

    private function isConnected(): bool
    {
        return $this->connection && $this->connection->isConnected() && $this->channel && $this->channel->is_open();
    }

    /**
     * Dispatches an event according to the Jophiel integration contract.
     *
     * @param string $eventName The routing key (e.g., "user.interaction.like").
     * @param array $payload The specific data for the event.
     * @return void
     */
    public function dispatch(string $eventName, array $payload): void
    {
        if (!$this->isConnected()) {
            Log::channel('events')->warning('JophielService: Conexión perdida. Intentando reconectar...');
            $this->close();
            $this->connect();
            if (!$this->isConnected()) {
                Log::channel('events')->error("Fallo al despachar evento a Jophiel (sin conexión): {$eventName}");
                return; // Fail silently to not break the main application flow.
            }
        }

        try {
            $messageBody = json_encode([
                "event_name" => $eventName,
                "event_id" => bin2hex(random_bytes(16)), // uuid-v4-like
                "event_timestamp" => date('Y-m-d\TH:i:s\Z'),
                "source" => "sword.v2",
                "payload" => $payload
            ]);

            $message = new AMQPMessage($messageBody, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);

            $this->channel->basic_publish($message, $this->config['exchange']['name'], $eventName);
            Log::channel('events')->info("Evento para Jophiel despachado: {$eventName}", ['payload' => $payload]);
        } catch (Throwable $e) {
            Log::channel('events')->error("Fallo al despachar evento a Jophiel: {$eventName}", [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
        }
    }

    public function close(): void
    {
        try {
            if ($this->channel) $this->channel->close();
            if ($this->connection) $this->connection->close();
        } catch (Throwable $e) {
            // Ignore
        } finally {
            $this->channel = null;
            $this->connection = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
