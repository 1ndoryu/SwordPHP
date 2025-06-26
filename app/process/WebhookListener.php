<?php

namespace app\process;

use app\model\Webhook;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use support\Log;
use Throwable;
use Workerman\Http\Client;
use Workerman\Worker;

class WebhookListener
{
    private ?AMQPStreamConnection $connection = null;
    private ?\PhpAmqpLib\Channel\AMQPChannel $channel = null;

    public function onWorkerStart(Worker $worker)
    {
        // El bootstrap de la base de datos es necesario para que Eloquent funcione en este proceso.
        \app\bootstrap\Database::start(null);

        $this->connect();

        // Si la conexión falla, intenta reconectar cada 5 segundos.
        if (!$this->connection) {
            \Workerman\Timer::add(5, fn() => $this->connect());
            return;
        }

        $this->setupConsumer();
    }

    private function connect()
    {
        try {
            $config = config('event.rabbitmq');
            $this->connection = new AMQPStreamConnection(
                $config['host'],
                $config['port'],
                $config['user'],
                $config['password'],
                $config['vhost']
            );
            $this->channel = $this->connection->channel();
            Log::channel('webhooks')->info('Proceso WebhookListener conectado a RabbitMQ.');
        } catch (Throwable $e) {
            Log::channel('webhooks')->error('Fallo en la conexión del WebhookListener con RabbitMQ', ['error' => $e->getMessage()]);
            $this->connection = null;
            $this->channel = null;
        }
    }

    private function setupConsumer()
    {
        $queueName = config('event.queue');
        $this->channel->queue_declare($queueName, false, true, false, false);

        $this->channel->basic_qos(null, 1, null); // Procesar un mensaje a la vez
        $this->channel->basic_consume($queueName, '', false, false, false, false, [$this, 'processMessage']);

        // Workerman necesita que el bucle de eventos se mantenga activo.
        // Se añade un temporizador para procesar callbacks de RabbitMQ.
        \Workerman\Timer::add(0.1, function () {
            try {
                if ($this->channel && $this->channel->is_consuming()) {
                    $this->channel->wait(null, true); // true para non-blocking
                }
            } catch (Throwable $e) {
                // Si la conexión se pierde, intenta reconectar.
                Log::channel('webhooks')->error('Conexión perdida en WebhookListener, intentando reconectar.', ['error' => $e->getMessage()]);
                $this->closeConnection();
                $this->connect();
                if ($this->connection) {
                    $this->setupConsumer();
                }
            }
        });
    }

    public function processMessage(AMQPMessage $msg)
    {
        $eventData = json_decode($msg->body, true);
        $eventName = $eventData['event_name'] ?? null;

        if (!$eventName) {
            Log::channel('webhooks')->warning('Mensaje recibido sin event_name', ['body' => $msg->body]);
            $msg->ack();
            return;
        }

        try {
            $webhooks = Webhook::where('event_name', $eventName)->where('is_active', true)->get();

            if ($webhooks->isEmpty()) {
                $msg->ack(); // No hay webhooks, confirmar mensaje y terminar.
                return;
            }

            Log::channel('webhooks')->info("Evento '{$eventName}' recibido. Encontrados " . $webhooks->count() . " webhooks activos.");

            foreach ($webhooks as $webhook) {
                $this->dispatchWebhook($webhook, $eventData);
            }
        } catch (Throwable $e) {
            Log::channel('webhooks')->error('Error al procesar mensaje de evento', [
                'error' => $e->getMessage(),
                'event' => $eventName
            ]);
        } finally {
            $msg->ack(); // Asegurarse siempre de confirmar el mensaje.
        }
    }

    private function dispatchWebhook(Webhook $webhook, array $payload)
    {
        try {
            $http = new Client();
            $options = [
                'method' => 'POST',
                'data' => json_encode($payload),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Sword-Event' => $payload['event_name'] ?? 'unknown'
                ],
                'timeout' => 10 // 10 segundos de timeout
            ];

            // Si hay un secret, firmamos el payload.
            if (!empty($webhook->secret)) {
                $signature = hash_hmac('sha256', $options['data'], $webhook->secret);
                $options['headers']['X-Sword-Signature'] = $signature;
            }

            $http->request($webhook->target_url, $options, function ($response) use ($webhook) {
                if ($response->getStatusCode() >= 300) {
                    Log::channel('webhooks')->warning('Webhook fallido', [
                        'id' => $webhook->id,
                        'url' => $webhook->target_url,
                        'status' => $response->getStatusCode(),
                        'body' => (string)$response->getBody()
                    ]);
                } else {
                    Log::channel('webhooks')->info('Webhook enviado exitosamente', [
                        'id' => $webhook->id,
                        'url' => $webhook->target_url,
                        'status' => $response->getStatusCode()
                    ]);
                }
            }, function ($exception) use ($webhook) {
                Log::channel('webhooks')->error('Error en la petición del Webhook', [
                    'id' => $webhook->id,
                    'url' => $webhook->target_url,
                    'error' => $exception->getMessage()
                ]);
            });
        } catch (Throwable $e) {
            Log::channel('webhooks')->error('Excepción al despachar Webhook', [
                'id' => $webhook->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function closeConnection()
    {
        try {
            if ($this->channel) $this->channel->close();
            if ($this->connection) $this->connection->close();
        } catch (Throwable $e) {
            // Ignorar errores al cerrar
        }
        $this->channel = null;
        $this->connection = null;
        Log::channel('webhooks')->info('Conexión de WebhookListener cerrada.');
    }

    public function onWorkerStop(Worker $worker)
    {
        $this->closeConnection();
    }
}
