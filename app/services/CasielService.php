<?php
// app/services/CasielService.php

namespace app\services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use support\Log;
use Throwable;

/**
 * Service to publish messages for the Casiel worker.
 * This is now a regular, non-singleton class for stateless operation.
 */
class CasielService
{
    private string $exchangeName = 'casiel_main_exchange';
    private string $routingKey = 'casiel.process';
    private array $connectionConfig;

    /**
     * The constructor now simply loads the configuration.
     * No connection is made here.
     */
    public function __construct()
    {
        $this->connectionConfig = [
            'host' => env('RABBITMQ_HOST'),
            'port' => env('RABBITMQ_PORT'),
            'user' => env('RABBITMQ_USER'),
            'pass' => env('RABBITMQ_PASS'),
            'vhost' => env('RABBITMQ_VHOST'),
            'connection_timeout' => (int)env('RABBITMQ_CONNECTION_TIMEOUT', 5),
            'read_write_timeout' => 30, // Increased for stability
        ];
    }

    /**
     * Publishes a new audio processing job to Casiel's work queue.
     * It connects, publishes, and closes the connection in one go.
     *
     * @param integer $contentId The ID of the content entry.
     * @param integer $mediaId The ID of the associated media file.
     * @return void
     * @throws \Exception if the message cannot be sent.
     */
    public function notifyNewAudio(int $contentId, int $mediaId): void
    {
        $connection = null;
        try {
            // 1. Connect
            $connection = new AMQPStreamConnection(
                $this->connectionConfig['host'],
                $this->connectionConfig['port'],
                $this->connectionConfig['user'],
                $this->connectionConfig['pass'],
                $this->connectionConfig['vhost'],
                false,
                'AMQPLAIN',
                null,
                'en_US',
                $this->connectionConfig['connection_timeout'],
                $this->connectionConfig['read_write_timeout']
            );
            $channel = $connection->channel();

            // 2. Ensure exchange exists (idempotent)
            $channel->exchange_declare($this->exchangeName, 'direct', false, true, false);

            // 3. Prepare and publish message
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

            $channel->basic_publish($message, $this->exchangeName, $this->routingKey);

            Log::channel('content')->info('Notificación para Casiel publicada exitosamente.', [
                'exchange' => $this->exchangeName,
                'routing_key' => $this->routingKey,
                'payload' => $payload
            ]);
        } catch (Throwable $e) {
            Log::channel('master')->error('CasielService: Fallo CRÍTICO al publicar mensaje para Casiel.', [
                'error' => $e->getMessage(),
                'content_id' => $contentId
            ]);
            // Re-throw exception so the calling code knows something went wrong.
            throw new \Exception('Failed to publish message to Casiel: ' . $e->getMessage(), 0, $e);
        } finally {
            // 4. Always try to close the connection
            if ($connection) {
                try {
                    $connection->close();
                } catch (Throwable $e) {
                    // Ignore errors during close
                }
            }
        }
    }
}
