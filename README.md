# ✨ Sword v2: Blazing-Fast Headless CMS

**Sword v2** is a minimalist, high-performance headless CMS built from the ground up on **Workerman**. It is designed for developers who need extreme speed and a simple, maintainable core. By leveraging Workerman's asynchronous, event-driven architecture, Sword v2 avoids the traditional overhead of frameworks like Laravel or Symfony, resulting in exceptionally low latency and high throughput.

It operates as a pure headless API, completely decoupling the backend logic from any frontend client or administration panel. Its architecture relies on a simple database schema (`contents`, `users`, `media`, `options`, `comments`, `likes`) that uses `JSONB` to provide maximum flexibility and performance. The goal is to create a powerful core that never becomes complex again.

## 🚀 Performance

Sword v2's primary advantage is its speed. Built on Workerman, it operates as a long-running process in memory, eliminating the need to bootstrap the entire framework on every single request. This results in:

-   **Minimal Latency:** API responses are served in just a few milliseconds.
-   **High Throughput:** Capable of handling a large number of concurrent connections with minimal resource consumption.
-   **Low Memory Footprint:** Efficient memory management allows it to run on modest hardware, making it ideal for a wide range of applications.

## 📜 Core Principles

1.  **Extreme Simplification:** Code must be simple, readable, and professional. Use the least amount of code possible and never repeat it (DRY principle).
2.  **Code Standards:** All code is written in **English** using the `snake_case` convention. Variable and function names should be self-explanatory but concise.
3.  **Pure & Decoupled API:** The CMS is exclusively an API responsible for content, authentication, and files. The admin panel or any client is a completely separate project.
4.  **Mandatory Testing:** All core functionalities are validated through a dedicated End-to-End (E2E) testing suite to ensure robustness.
5.  **Clean Code:** Code should be self-explanatory, minimizing the need for comments. If a comment is required, it should explain the "why," not the "what."
6.  **Channel-based Logging:** Each main feature (auth, content, etc.) has its own separate log file for easier debugging, with a `master.log` capturing all activity.
7.  **Clean Architecture:** The file structure is organized and granular, avoiding large files with multiple responsibilities.

---

## 💾 Installation

### Prerequisites

-   PHP \>= 8.1
-   Composer
-   PostgreSQL
-   Redis (Recommended for caching)
-   RabbitMQ (Required for the Event System)

### 1\. Local Setup

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/1ndoryu/SwordPHP
    cd sword-v2
    ```

2.  **Install PHP dependencies:**

    ```bash
    composer install
    ```

3.  **Configure your environment:**

    -   Copy the example environment file: `cp .env.example .env`
    -   Edit the `.env` file with your database credentials, RabbitMQ connection details, and generate a unique `JWT_SECRET`:
        ```bash
        # You can generate a secret with:
        # openssl rand -base64 32
        JWT_SECRET="YOUR_SUPER_SECRET_KEY_HERE"
        ```

4.  **Set up the database:**
    Run the installation command to create all necessary tables and default roles.

    ```bash
    php webman db:install
    ```

5.  **Start the server:**

    ```bash
    php start.php start
    ```

    The API will be running at `http://127.0.0.1:8787`.

### 2\. Docker Setup

A `Dockerfile` is included for containerized deployments.

1.  **Build the image:**

    ```bash
    docker build -t sword-v2 .
    ```

2.  **Run the container:**
    Make sure your `.env` file is configured, as it will be used by the container. You'll need to link it to your PostgreSQL and RabbitMQ services.

    ```bash
    docker run -p 8787:8787 --env-file .env --name sword-api sword-v2
    ```

---

## ⚡ Event System & Webhooks

Sword v2 includes a robust event system built on **RabbitMQ** to decouple application logic and notify external services via webhooks.

### Overview

When a key action occurs in the CMS (like creating content), an event is dispatched to a RabbitMQ queue. A dedicated background process, `WebhookListener`, consumes messages from this queue. For each event, it finds all active webhooks registered for that event and sends them an asynchronous HTTP POST request.

This ensures that external integrations (like a static site generator, a notification service, or a search indexer) are notified immediately without impacting the API's response time.

### Available Events

The following events are currently dispatched:

| Event Name        | Triggered When...                       | Payload Includes                                   |
| ----------------- | --------------------------------------- | -------------------------------------------------- |
| `user.registered` | A new user successfully registers.      | `user_id`, `username`, `email`, `role_name`        |
| `user.loggedin`   | A user successfully logs in.            | `user_id`                                          |
| `content.created` | A new piece of content is created.      | `id`, `slug`, `type`, `status`, `user_id`, `title` |
| `content.updated` | A piece of content is updated.          | `id`, `user_id`, `changes` (the updated data)      |
| `content.deleted` | A piece of content is deleted.          | `id`, `user_id`                                    |
| `content.liked`   | A user likes a piece of content.        | `content_id`, `user_id`                            |
| `content.unliked` | A user removes their like from content. | `content_id`, `user_id`                            |

### Webhook Security

To verify that a webhook request originated from your Sword v2 instance, you can configure a `secret` when creating a webhook. If a secret is present, all outgoing requests will include an `X-Sword-Signature` header containing a `sha256` HMAC hash of the request body, signed with your secret.

---

## 🛡️ Security Considerations (Pentesting)

Security is a core consideration. Here are the key areas to focus on during security analysis:

-   **Authentication & Authorization:** Access is controlled by JWT bearer tokens. All protected endpoints require a valid token. Authorization is granular, based on permissions assigned to roles (e.g., `content.create`, `admin.users.list`). The `admin` role has wildcard `*` access, while other roles must have permissions explicitly granted.
-   **Input Validation:** All user-supplied data must be rigorously validated and sanitized to prevent common vulnerabilities like XSS, SQL injection, and command injection.
-   **System Endpoints:** The dangerous `/system/install` and `/system/reset` endpoints are disabled by default when `APP_ENV` is set to `production`. They should never be exposed on a live server.
-   **Dependency Management:** Regularly update dependencies with `composer update` to patch known vulnerabilities in third-party packages.
-   **Environment Security:** Never commit your `.env` file to version control. Ensure secrets like `JWT_SECRET`, database credentials, and other API keys are managed securely.
-   **File Uploads:** Files are uploaded with unique, randomly generated names to prevent path traversal attacks. However, further security measures like strict file type and size validation, and scanning uploaded files for malware, are recommended.
-   **Webhook Integrity:** Use the `secret` field when creating webhooks to generate a signature. Your webhook consumer should validate this signature to ensure the payload has not been tampered with and originated from your CMS.

---

## API Documentation (v1.0.0)

This documentation provides a detailed overview of all available endpoints.

### **Base URL**

The API root is the base URL of your application. Examples use `http://127.0.0.1:8787`.

### **🛡️ Authentication & Authorization**

Most endpoints require a `Bearer Token` for authentication. First, register a user and then use the `/auth/login` endpoint to obtain a JWT.

**Header Format:** `Authorization: Bearer <YOUR_JWT_TOKEN>`

Authorization is managed via **permissions**. Each authenticated user has a role, and each role has an array of permissions (e.g., `content.create`, `admin.users.list`). Protected routes require the user to have all the necessary permissions. The `admin` role, by default, has a wildcard permission `*`.

### **General Responses**

All API responses follow a standard JSON format:

```json
{
    "success": true,
    "message": "Descriptive message",
    "data": {}
}
```

### ⚙️ 1. System Endpoints

Endpoints for installing and resetting the database. Intended for development and testing environments. **Disabled in production.**

#### **`POST /system/install`**

Initializes the database by creating all the necessary tables and default roles (`admin`, `user`).

-   **Authentication:** None
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Comando [db:install] ejecutado.",
        "data": {
            "output": "Log: Iniciando instalación de la base de datos...\nLog: Tabla \"roles\" creada correctamente.\n..."
        }
    }
    ```

#### **`POST /system/reset`**

Drops all application tables from the database. **Use with extreme caution.**

-   **Authentication:** None
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Comando [db:reset] ejecutado.",
        "data": {
            "output": "Log: Iniciando el reseteo de la base de datos...\nLog: Tabla \"likes\" eliminada.\n..."
        }
    }
    ```

### 🔑 2. Authentication Endpoints

#### **`POST /auth/register`**

Registers a new user. The first user registered is automatically assigned the `admin` role. Subsequent users will have the `user` role.

-   **Authentication:** None
-   **Request Body:**
    ```json
    {
        "username": "newuser",
        "email": "user@example.com",
        "password": "strongpassword123"
    }
    ```
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "User registered successfully."
    }
    ```

#### **`POST /auth/login`**

Authenticates a user and returns a JWT access token.

-   **Authentication:** None
-   **Request Body:**
    ```json
    {
        "identifier": "user@example.com",
        "password": "strongpassword123"
    }
    ```
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Login successful.",
        "data": {
            "token_type": "bearer",
            "access_token": "ey...your...jwt...here...",
            "expires_in": 3600
        }
    }
    ```

### 👤 3. User Endpoints

Endpoints for managing user-specific data.

#### **`GET /user/profile`**

Retrieves the profile information of the currently authenticated user.

-   **Authentication:** Bearer Token
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "user": {
            "id": 2,
            "username": "newuser",
            "email": "user@example.com",
            "role": {
                "id": 2,
                "name": "user",
                "description": "Standard user...",
                "permissions": ["content.create", "content.update.own"],
                "created_at": "...",
                "updated_at": "..."
            },
            "profile_data": {
                "avatar_url": "/uploads/media/some_image.jpg"
            },
            "created_at": "2025-06-26T15:30:00.000000Z"
        }
    }
    ```

#### **`POST /user/profile`**

Updates the `profile_data` JSON object for the authenticated user.

-   **Authentication:** Bearer Token
-   **Request Body:**
    ```json
    {
        "profile_data": {
            "display_name": "New User Name",
            "bio": "A short bio here.",
            "avatar_url": "/uploads/media/new_avatar.png"
        }
    }
    ```
-   **Success Response (200 OK):** (Returns the updated user object)

#### **Example: Changing a Profile Picture**

1.  **Step 1: Upload the image.** Send a `POST` request to `/media` (multipart/form-data) with your new image.
2.  **Step 2: Get the path.** The API will respond with the media object, which contains a `path` (e.g., `uploads/media/abcdef123456.jpg`).
3.  **Step 3: Update the profile.** Send a `POST` request to `/user/profile` with the path from Step 2:
    ```json
    {
        "profile_data": {
            "avatar_url": "uploads/media/abcdef123456.jpg"
        }
    }
    ```

#### **`GET /user/likes`**

Retrieves a paginated list of content that the authenticated user has liked.

-   **Authentication:** Bearer Token
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Liked content retrieved successfully.",
        "data": {
            "current_page": 1,
            "data": [
                {
                    "id": 1,
                    "slug": "my-first-post",
                    "type": "post",
                    "status": "published"
                }
            ],
            "first_page_url": "http://.../?page=1",
            "last_page": 1
        }
    }
    ```

---

### 📄 4. Content Endpoints

Endpoints for managing content (posts, pages, etc.).

#### **`GET /contents`**

Retrieves a paginated list of `published` content.

-   **Authentication:** None

#### **`GET /contents/{slug}`**

Retrieves a single piece of `published` content by its slug.

-   **Authentication:** None
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Content retrieved successfully.",
        "data": {
            "id": 1,
            "slug": "my-first-post",
            "type": "post",
            "status": "published",
            "user_id": 1,
            "content_data": {
                "title": "My First Post",
                "body": "This is the content of the post."
            },
            "created_at": "...",
            "updated_at": "..."
        }
    }
    ```

#### **`POST /contents`**

Creates new content. Requires `content.create` permission.

-   **Authentication:** Bearer Token

#### **`POST /contents/{id}`**

Updates existing content. Requires `content.update.own` permission, or `content.update.all` for other users' content.

-   **Authentication:** Bearer Token

#### **`DELETE /contents/{id}`**

Deletes content. Requires `content.delete.own` permission, or `content.delete.all` for other users' content.

-   **Authentication:** Bearer Token

#### **`POST /contents/{id}/like`**

Toggles a "like" on content. Requires authentication.

-   **Authentication:** Bearer Token
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Like added successfully.",
        "data": {
            "like_count": 15
        }
    }
    ```

---

### 💬 5. Comments Endpoints

#### **`POST /comments/{content_id}`**

Posts a new comment. Requires `comment.create` permission.

-   **Authentication:** Bearer Token
-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "message": "Comment posted successfully.",
        "data": {
            "id": 123,
            "content_id": 1,
            "user_id": 2,
            "body": "This is a great post!",
            "user": {
                "id": 2,
                "username": "newuser"
            }
        }
    }
    ```

#### **`DELETE /comments/{comment_id}`**

Deletes a comment. Requires `comment.delete.own` permission or `comment.delete.all` for other users' comments.

-   **Authentication:** Bearer Token

---

### 🖼️ 6. Media Endpoints

#### **`POST /media`**

Uploads a new file. Requires `media.upload` permission.

-   **Authentication:** Bearer Token
-   **Request Body:** `multipart/form-data` with a file field named `file`.
-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "message": "File uploaded successfully.",
        "data": {
            "id": 5,
            "path": "uploads/media/abcdef123456.jpg",
            "mime_type": "image/jpeg",
            "user_id": 1,
            "metadata": {
                "original_name": "my-vacation.jpg",
                "size_bytes": 123456
            }
        }
    }
    ```

#### **`GET /media/{id}`**

Retrieves a single media file object by its ID. This is useful for clients like Casiel that need to resolve a media path from an ID stored in a content object.

-   **Authentication:** None
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the media file.
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Media retrieved successfully.",
        "data": {
            "id": 5,
            "path": "uploads/media/abcdef123456.jpg",
            "mime_type": "image/jpeg",
            "user_id": 1,
            "metadata": {
                "original_name": "my-vacation.jpg",
                "size_bytes": 123456
            },
            "created_at": "2025-06-26T20:15:00.000000Z",
            "updated_at": "2025-06-26T20:15:00.000000Z"
        }
    }
    ```
-   **Error Response (404 Not Found):**
    ```json
    {
        "success": false,
        "message": "Media not found."
    }
    ```

---

### 🌐 7. Options Endpoints

Manage global site settings.

#### **`GET /options`**

Retrieves all global options.

-   **Authentication:** None
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Options retrieved successfully.",
        "data": {
            "site_title": "My Awesome Blog",
            "maintenance_mode": "false",
            "welcome_message": "Hello World!"
        }
    }
    ```

---

### 👑 8. Admin Endpoints

These endpoints require specific `admin.*` permissions.

#### **`GET /admin/contents`**

Retrieves all content, regardless of status. (Requires `admin.content.list`)

#### **`GET /admin/contents/{id}`**

Retrieves a single content item by its ID, regardless of status. (Requires `admin.content.view`)

  - **URL Parameters:**
      - `id` (integer, required): The ID of the content to retrieve.
  - **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Admin content retrieved successfully.",
        "data": {
            "id": 42,
            "slug": "draft-post",
            "type": "post",
            "status": "draft",
            "user_id": 3,
            "content_data": {
                "title": "Draft Post",
                "body": "This post is still being edited."
            },
            "created_at": "...",
            "updated_at": "..."
        }
    }
    ```
  - **Error Response (404 Not Found):**
    ```json
    {
        "success": false,
        "message": "Content not found."
    }
    ```

#### **`GET /admin/media`**

Retrieves all media files. (Requires `admin.media.list`)

#### **`DELETE /admin/media/{id}`**

Permanently deletes a media file. (Requires `admin.media.delete`)

#### **`POST /admin/users/{id}/role`**

Changes a user's role. (Requires `admin.user.role.change`)

```json
{
    "role_id": 2
}
```

#### **`POST /admin/options`**

Batch updates global options. (Requires `admin.options.update`)

#### **`GET /admin/contents/by-hash/{hash}`**

Busca contenidos que tengan un `audio_hash` específico en su campo `content_data`. Es una herramienta clave para la detección de duplicados, como archivos de audio. (Requiere `admin.content.list`)

  - **URL Parameters:**
      - `hash` (string, required): El hash perceptual del archivo a buscar.
  - **Success Response (Found, 200 OK):** Devuelve un array con todos los contenidos que coinciden con el hash.
    ```json
    {
        "success": true,
        "message": "Content with specified hash found.",
        "data": [
            {
                "id": 101,
                "slug": "existing-audio-sample",
                "content_data": {
                    "title": "Existing Audio",
                    "audio_hash": "a1b2c3d4e5f6"
                }
            }
        ]
    }
    ```
  - **Success Response (Not Found, 200 OK):** Crucialmente, devuelve `200 OK` con `data` como `null` para indicar que la búsqueda se completó sin encontrar duplicados.
    ```json
    {
        "success": true,
        "message": "Content with specified hash not found.",
        "data": null
    }
    ```

#### **`GET /admin/contents/filter-by-data`**

Realiza una búsqueda genérica y paginada de contenidos, filtrando por un par clave-valor arbitrario dentro del campo JSON `content_data`. (Requiere `admin.content.list`)

  - **Query Parameters:**
      - `key` (string, required): La clave dentro del objeto `content_data` por la que se desea filtrar.
      - `value` (string, required): El valor que debe coincidir con la clave especificada.
      - `per_page` (integer, optional): Número de resultados por página.
  - **Example Request:**
    ```
    GET /admin/contents/filter-by-data?key=source_id&value=ext-54321
    ```
  - **Success Response (200 OK):** Devuelve una lista paginada de los contenidos que coinciden con el filtro.
    ```json
    {
        "success": true,
        "message": "Contents filtered successfully.",
        "data": {
            "current_page": 1,
            "data": [
                {
                    "id": 105,
                    "slug": "imported-item-54321",
                    "content_data": {
                        "title": "Imported Item",
                        "source_id": "ext-54321"
                    }
                }
            ],
            "total": 1
        }
    }
    ```

---

### 🛂 9. Role Management Endpoints (Admin)

Base Path: `/admin/roles`. These endpoints require `admin.roles.*` permissions.

#### **`GET /admin/roles`**

Retrieves a list of all roles. (Requires `admin.roles.list`)

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Roles retrieved successfully.",
        "data": [
            {
                "id": 1,
                "name": "admin",
                "description": "Super Administrator...",
                "permissions": ["*"],
                "created_at": "...",
                "updated_at": "..."
            }
        ]
    }
    ```

#### **`POST /admin/roles`**

Creates a new role. (Requires `admin.roles.create`)

-   **Request Body:**
    ```json
    {
        "name": "editor",
        "description": "Can edit all content.",
        "permissions": ["content.create", "content.update.all", "media.upload"]
    }
    ```

#### **`POST /admin/roles/{id}`**

Updates an existing role. (Requires `admin.roles.update`)

-   **Request Body:**
    ```json
    {
        "description": "Can now also delete content.",
        "permissions": ["content.create", "content.update.all", "content.delete.all", "media.upload"]
    }
    ```

#### **`DELETE /admin/roles/{id}`**

Deletes a role. (Requires `admin.roles.delete`)

---

### 🎣 10. Webhook Management Endpoints (Admin)

Base Path: `/admin/webhooks`. These endpoints require `admin.webhooks.*` permissions.

#### **`GET /admin/webhooks`**

Retrieves a list of all webhooks. (Requires `admin.webhooks.list`)

#### **`POST /admin/webhooks`**

Creates a new webhook. (Requires `admin.webhooks.create`)

-   **Request Body:**
    ```json
    {
        "event_name": "content.created",
        "target_url": "https://my-service.com/webhook-receiver",
        "secret": "a-very-strong-secret-to-validate-payloads",
        "is_active": true
    }
    ```

#### **`POST /admin/webhooks/{id}`**

Updates an existing webhook. (Requires `admin.webhooks.update`)

#### **`DELETE /admin/webhooks/{id}`**

Deletes a webhook. (Requires `admin.webhooks.delete`)

---

### 📡 11. Feed Endpoint

Endpoint that returns a personalized recommendation feed powered by **Jophiel** for the authenticated user.

#### **`GET /feed`**

Returns an ordered list of recommended content (`audio_sample` type) for the current user.

  - **Authentication:** Bearer Token
  - **Query Parameters:**
      - `per_page` (integer, optional): Page size used for the fallback feed when Jophiel is unavailable. Defaults to `20`.
  - **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Feed retrieved successfully.",
        "data": {
            "current_page": 1,
            "data": [
                {
                    "id": 101,
                    "slug": "lofi-beat-001",
                    "type": "audio_sample",
                    "status": "published",
                    "user_id": 5,
                    "content_data": {
                        "title": "Lofi Beat #1",
                        "duration": 31,
                        "audio_hash": "a1b2c3d4"
                    },
                    "created_at": "...",
                    "updated_at": "..."
                }
            ],
            "first_page_url": null,
            "last_page": 1,
            "last_page_url": null,
            "next_page_url": null,
            "path": "/feed",
            "per_page": 1,
            "prev_page_url": null,
            "to": 1,
            "total": 1
        }
    }
    ```
  - **Fallback Response (200 OK):** When Jophiel is unreachable, the CMS sends the latest published content with the message `Jophiel unavailable. Sending latest content feed.` The structure is identical to the success response.
  - **Error Response (500 Internal Server Error):**
    ```json
    {
        "success": false,
        "message": "An internal error occurred."
    }
    ```

