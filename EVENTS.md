# Sword v2: Sistema de Eventos y Colas

Sword v2 utiliza RabbitMQ para gestionar dos flujos de comunicación asíncrona distintos: **Eventos Internos** (para webhooks) y **Trabajos para Workers Externos** (como el servicio Casiel).

Esto mantiene el núcleo de la API rápido y desacoplado, delegando tareas pesadas o notificaciones a otros procesos.

## 1. Eventos Internos (`sword_events_queue`)

-   **Cola Principal:** `sword_events_queue` (configurable en `.env` con `RABBITMQ_EVENTS_QUEUE`).
-   **Propósito:** Notificar a los suscriptores (webhooks) sobre acciones que ocurren dentro de Sword. Por ejemplo, `content.created`, `user.registered`, etc.
-   **Servicio Responsable:** `app\services\EventService.php`.
-   **Proceso Consumidor:** `app\process\WebhookListener.php`. Este proceso escucha en la cola y dispara las peticiones HTTP a las URLs de los webhooks configurados.

### ¿Cómo Despachar un Evento Interno?

Se utiliza la función helper global `dispatch_event()`:

```php
// Ejemplo en un controlador
dispatch_event('content.created', [
    'id' => $content->id,
    'slug' => $content->slug,
    'user_id' => $user->id
]);
```
