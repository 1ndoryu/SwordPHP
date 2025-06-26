# ✨ Sword v2: Minimalist Headless CMS

**Sword v2** is a complete rewrite of a CMS, built from the ground up on **Workerman** to be extremely fast, lightweight, and easy to maintain.

It operates as a pure headless API, completely decoupling the logic from the administration panel. Its architecture relies on a simple database schema (`contents`, `users`, `media`, `options`, `comments`, `likes`) that uses `JSONB` to provide maximum flexibility and performance. The goal is to create a powerful core that never becomes complex again.

## 🚀 Core Principles

1.  **Extreme Simplification:** Code must be simple, readable, and professional.
2.  **Pure & Decoupled API:** The CMS is exclusively an API responsible for content, authentication, and files.
3.  **Mandatory Testing:** All core functionalities are validated through a dedicated E2E testing suite.
4.  **Clean Code:** Code should be self-explanatory, minimizing the need for comments.
5.  **Granular Security:** Access control is managed by specific permissions, not just broad roles.

---

##  API Documentation (v0.9.9)

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

Endpoints for installing and resetting the database. Intended for development and testing environments.

#### **`POST /system/install`**

Initializes the database by creating all the necessary tables and default roles (`admin`, `user`).

  - **Authentication:** None
  - **Success Response (200 OK):**
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

  - **Authentication:** None
  - **Success Response (200 OK):**
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

  - **Authentication:** None
  - **Request Body:**
    ```json
    {
        "username": "newuser",
        "email": "user@example.com",
        "password": "strongpassword123"
    }
    ```
  - **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "User registered successfully."
    }
    ```

#### **`POST /auth/login`**

Authenticates a user and returns a JWT access token.

  - **Authentication:** None
  - **Request Body:**
    ```json
    {
        "identifier": "user@example.com",
        "password": "strongpassword123"
    }
    ```
  - **Success Response (200 OK):**
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

  - **Authentication:** Bearer Token
  - **Success Response (200 OK):**
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

  - **Authentication:** Bearer Token
  - **Request Body:**
    ```json
    {
        "profile_data": {
            "display_name": "New User Name",
            "bio": "A short bio here.",
            "avatar_url": "/uploads/media/new_avatar.png"
        }
    }
    ```
  - **Success Response (200 OK):** (Returns the updated user object)

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

  - **Authentication:** Bearer Token
  - **Success Response (200 OK):**
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

-----

### 📄 4. Content Endpoints

Endpoints for managing content (posts, pages, etc.).

#### **`GET /contents`**

Retrieves a paginated list of `published` content.

  - **Authentication:** None

#### **`GET /contents/{slug}`**

Retrieves a single piece of `published` content by its slug.

  - **Authentication:** None
  - **Success Response (200 OK):**
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

  - **Authentication:** Bearer Token

#### **`POST /contents/{id}`**

Updates existing content. Requires `content.update.own` permission, or `content.update.all` for an admin.

  - **Authentication:** Bearer Token

#### **`DELETE /contents/{id}`**

Deletes content. Requires `content.delete.own` permission, or `content.delete.all` for an admin.

  - **Authentication:** Bearer Token

#### **`POST /contents/{id}/like`**

Toggles a "like" on content. Requires authentication.

  - **Authentication:** Bearer Token
  - **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Like added successfully.",
        "data": {
            "like_count": 15
        }
    }
    ```

-----

### 💬 5. Comments Endpoints

#### **`POST /comments/{content_id}`**

Posts a new comment. Requires `comment.create` permission.

  - **Authentication:** Bearer Token
  - **Success Response (201 Created):**
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

Deletes a comment. Requires `comment.delete.own` permission or `comment.delete.all` for an admin.

  - **Authentication:** Bearer Token

-----

### 🖼️ 6. Media Endpoints

#### **`POST /media`**

Uploads a new file. Requires `media.upload` permission.

  - **Authentication:** Bearer Token
  - **Request Body:** `multipart/form-data` with a file field named `file`.
  - **Success Response (201 Created):**
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

-----

### 🌐 7. Options Endpoints

Manage global site settings.

#### **`GET /options`**

Retrieves all global options.

  - **Authentication:** None
  - **Success Response (200 OK):**
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

-----

### 👑 8. Admin Endpoints

These endpoints require specific `admin.*` permissions.

#### **`GET /admin/contents`**

Retrieves all content, regardless of status. (Requires `admin.content.list`)

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

-----

### 🛂 9. Role Management Endpoints (Admin)

Base Path: `/admin/roles`. These endpoints require `admin.roles.*` permissions.

#### **`GET /admin/roles`**

Retrieves a list of all roles. (Requires `admin.roles.list`)

  - **Success Response (200 OK):**
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

  - **Request Body:**
    ```json
    {
        "name": "editor",
        "description": "Can edit all content.",
        "permissions": ["content.create", "content.update.all", "media.upload"]
    }
    ```

#### **`POST /admin/roles/{id}`**

Updates an existing role. (Requires `admin.roles.update`)

  - **Request Body:**
    ```json
    {
        "description": "Can now also delete content.",
        "permissions": ["content.create", "content.update.all", "content.delete.all", "media.upload"]
    }
    ```

#### **`DELETE /admin/roles/{id}`**

Deletes a role. (Requires `admin.roles.delete`)

