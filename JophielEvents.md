# Informe de Eventos de Jophiel

Este documento detalla todos los eventos despachados para el sistema Jophiel, su propósito, y la estructura de datos (payload) que conllevan. Estos eventos se despachan a través de la función helper `jophielEvento()`.

---

## Eventos de Ciclo de Vida de Samples (`sample.lifecycle`)

Estos eventos se relacionan con la creación, actualización y eliminación de contenidos de tipo `audio_sample`.

### 1. `sample.lifecycle.created`

-   **Descripción:** Se dispara cuando el servicio externo **Casiel** ha terminado de procesar un `audio_sample` y ha devuelto los metadatos. Este evento notifica a Jophiel que un nuevo sample está completamente listo y disponible.
-   **Localización:** `app/controller/WebhookController.php` en el método `handleCasielProcessed`.
-   **Payload:**
    ```json
    {
      "sample_id": "integer",
      "creator_id": "integer",
      "metadata": "object"
    }
    ```
    -   `sample_id`: ID del `Content` que representa el sample.
    -   `creator_id`: ID del `User` que subió el sample.
    -   `metadata`: Objeto JSON con los `content_data` completos del sample, incluyendo la información añadida por Casiel.

### 2. `sample.lifecycle.updated`

-   **Descripción:** Se dispara cuando se actualizan los metadatos (`content_data`) de un `audio_sample` existente.
-   **Localización:** `app/controller/ContentController.php` en el método `update`.
-   **Payload:**
    ```json
    {
      "sample_id": "integer",
      "creator_id": "integer",
      "metadata": "object"
    }
    ```
    -   `sample_id`: ID del `Content` actualizado.
    -   `creator_id`: ID del `User` propietario.
    -   `metadata`: Objeto JSON con los `content_data` que fueron actualizados.

### 3. `sample.lifecycle.deleted`

-   **Descripción:** Se dispara cuando un `audio_sample` es eliminado del sistema.
-   **Localización:** `app/controller/ContentController.php` en el método `destroy`.
-   **Payload:**
    ```json
    {
      "sample_id": "integer"
    }
    ```
    -   `sample_id`: ID del `Content` que fue eliminado.

---

## Eventos de Interacción de Usuario (`user.interaction`)

Estos eventos se relacionan con las acciones que los usuarios realizan sobre los contenidos o entre ellos.

### 1. `user.interaction.like`

-   **Descripción:** Un usuario ha dado "like" a un `audio_sample`.
-   **Localización:** `app/controller/ContentController.php` en el método `toggleLike`.
-   **Payload:**
    ```json
    {
      "user_id": "integer",
      "sample_id": "integer"
    }
    ```
    -   `user_id`: ID del `User` que dio el like.
    -   `sample_id`: ID del `Content` que recibió el like.

### 2. `user.interaction.unlike`

-   **Descripción:** Un usuario ha quitado su "like" de un `audio_sample`.
-   **Localización:** `app/controller/ContentController.php` en el método `toggleLike`.
-   **Payload:**
    ```json
    {
      "user_id": "integer",
      "sample_id": "integer"
    }
    ```
    -   `user_id`: ID del `User` que quitó el like.
    -   `sample_id`: ID del `Content` afectado.

### 3. `user.interaction.comment`

-   **Descripción:** Un usuario ha publicado un comentario en un `audio_sample`.
-   **Localización:** `app/controller/CommentController.php` en el método `store`.
-   **Payload:**
    ```json
    {
      "user_id": "integer",
      "sample_id": "integer"
    }
    ```
    -   `user_id`: ID del `User` que comentó.
    -   `sample_id`: ID del `Content` comentado.

### 4. `user.interaction.follow`

-   **Descripción:** Un usuario ha comenzado a seguir a otro usuario.
-   **Localización:** `app/controller/UserController.php` en el método `follow`.
-   **Payload:**
    ```json
    {
      "user_id": "integer",
      "followed_user_id": "integer"
    }
    ```
    -   `user_id`: ID del `User` que inició la acción (el seguidor).
    -   `followed_user_id`: ID del `User` que ahora es seguido.

### 5. `user.interaction.unfollow`

-   **Descripción:** Un usuario ha dejado de seguir a otro usuario.
-   **Localización:** `app/controller/UserController.php` en el método `unfollow`.
-   **Payload:**
    ```json
    {
      "user_id": "integer",
      "unfollowed_user_id": "integer"
    }
    ```
    -   `user_id`: ID del `User` que dejó de seguir.
    -   `unfollowed_user_id`: ID del `User` que fue dejado de seguir. 