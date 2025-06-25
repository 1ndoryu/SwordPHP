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
        // Cargar la configuración desde las variables de entorno
        $this->config = [
            'host'     => $_ENV['RABBITMQ_HOST'] ?? 'localhost',
            'port'     => $_ENV['RABBITMQ_PORT'] ?? 5672,
            'user'     => $_ENV['RABBITMQ_USER'] ?? 'guest',
            'password' => $_ENV['RABBITMQ_PASS'] ?? 'guest',
            'vhost'    => $_ENV['RABBITMQ_VHOST'] ?? '/',
            'queue'    => $_ENV['RABBITMQ_QUEUE_CASIEL'] ?? 'casiel_processing_queue',
        ];
    }

    /**
     * Notifica a Casiel que un nuevo sample ha sido creado y está listo para procesar.
     *
     * @param int $idSample El ID del contenido (sample) recién creado en la base de datos.
     * @return bool True si el mensaje fue enviado, False en caso de error.
     */
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

            // Declarar la cola (si no existe, se crea). Es 'durable' (true) para que no se pierda si RabbitMQ se reinicia.
            $channel->queue_declare($this->config['queue'], false, true, false, false);

            // Crear el payload del mensaje
            $payload = json_encode(['id_sample' => $idSample]);

            // Crear el mensaje. Es 'persistent' para que sobreviva a un reinicio de RabbitMQ.
            $message = new AMQPMessage($payload, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);

            // Publicar el mensaje en la cola
            $channel->basic_publish($message, '', $this->config['queue']);

            // Cerrar conexión
            $channel->close();
            $this->connection->close();

            // Aquí puedes registrar un log de éxito si lo deseas
            // log_info("Evento para Casiel publicado con éxito. Sample ID: $idSample");

            return true;
        } catch (Throwable $e) {
            // Es MUY IMPORTANTE registrar el error, pero no detener la ejecución.
            // La subida del sample fue exitosa para el usuario, aunque la notificación a la IA falló.
            // Podrás reenviar el evento manualmente o Casiel lo tomará en su siguiente ciclo de sondeo (si lo dejas como fallback).

            // log_error("Fallo al publicar evento para Casiel: " . $e->getMessage());

            return false;
        }
    }
}
