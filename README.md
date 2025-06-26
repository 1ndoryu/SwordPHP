# Sword v2: Minimalist Headless CMS

**Sword v2** is a complete rewrite of a CMS, built from the ground up on **Workerman** to be extremely fast, lightweight, and easy to maintain.

It operates as a pure headless API, completely decoupling the logic from the administration panel. Its architecture relies on a simple database schema (`contents`, `users`, `media`, `options`, `comments`, `likes`) that uses `JSONB` to provide maximum flexibility and performance. The goal is to create a powerful core that never becomes complex again.

## Core Principles

1.  **Extreme Simplification:** Code must be simple, readable, and professional.
2.  **Pure & Decoupled API:** The CMS is exclusively an API responsible for content, authentication, and files.
3.  **Mandatory Testing:** 100% of the code must be testable.
4.  **Clean Code:** Code should be self-explanatory, minimizing the need for comments.
5.  **Concise Documentation:** A single, clear document explaining the API usage.

---

## API Documentation (v0.9.7)

This documentation provides a detailed overview of all available endpoints.

### **Base URL**

The API root is the base URL of your application. Examples use `http://127.0.0.1:8787`.

### **Authentication**

Most endpoints require a `Bearer Token` for authentication. First, register a user and then use the `/auth/login` endpoint to obtain a JWT. Include this token in the `Authorization` header for protected requests.

**Header Format:** `Authorization: Bearer <YOUR_JWT_TOKEN>`

### **General Responses**

All API responses follow a standard JSON format:

```json
{
    "success": true, // boolean
    "message": "Descriptive message", // string
    "data": {} // object or null
}
```

### 1\. System Endpoints

Endpoints for installing and resetting the database. Intended for development and testing environments.

#### **`POST /system/install`**

Initializes the database by creating all the necessary tables (`users`, `contents`, `media`, etc.).

-   **Authentication:** None
-   **Request Body:** None
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Comando [db:install] ejecutado.",
        "data": {
            "output": "Log: Iniciando instalación de la base de datos...\nLog: Tabla \"users\" creada correctamente.\n..."
        }
    }
    ```

#### **`POST /system/reset`**

Drops all application tables from the database. **Use with extreme caution.**

-   **Authentication:** None
-   **Request Body:** None
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

---

### 2\. Authentication Endpoints

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
-   **Error Response (409 Conflict):**
    ```json
    {
        "success": false,
        "message": "User already exists."
    }
    ```

#### **`POST /auth/login`**

Authenticates a user and returns a JWT access token.

-   **Authentication:** None
-   **Request Body:**
    ```json
    {
        "identifier": "user@example.com", // Can be username or email
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
-   **Error Response (401 Unauthorized):**
    ```json
    {
        "success": false,
        "message": "Invalid credentials."
    }
    ```

---

### 3\. User Endpoints

Endpoints for retrieving authenticated user information.

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
            "role": "user",
            "created_at": "2025-06-26T15:30:00.000000Z"
        }
    }
    ```

#### **`GET /user/likes`**

Retrieves a paginated list of content that the authenticated user has liked.

-   **Authentication:** Bearer Token
-   **Query Parameters:**
    -   `per_page` (integer, optional, default: 15): Number of items per page.
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
                    // ... other content fields
                }
            ],
            "first_page_url": "http://.../?page=1",
            "last_page": 1
            // ... pagination fields
        }
    }
    ```

---

### 4\. Content Endpoints

Endpoints for managing content (posts, pages, etc.).

#### **`GET /contents`**

Retrieves a paginated list of `published` content.

-   **Authentication:** None
-   **Query Parameters:**
    -   `per_page` (integer, optional, default: 15): Number of items per page.
-   **Success Response (200 OK):** (Structure is similar to `/user/likes`)

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

Creates a new piece of content.

-   **Authentication:** Bearer Token
-   **Request Body:**
    ```json
    {
        "type": "post",
        "status": "draft", // or "published"
        "content_data": {
            "title": "A New Post Title",
            "body": "Some content here.",
            "featured_image": "/uploads/media/image.jpg"
        }
    }
    ```
-   **Success Response (201 Created):** (Returns the created content object)

#### **`POST /contents/{id}`**

Updates an existing piece of content. The user must be the owner or an admin.

-   **Authentication:** Bearer Token
-   **Request Body:** (Include only the fields you want to update)
    ```json
    {
        "status": "published",
        "content_data": {
            "title": "An Updated Title"
        }
    }
    ```
-   **Success Response (200 OK):** (Returns the updated content object)

#### **`DELETE /contents/{id}`**

Deletes a piece of content. The user must be the owner or an admin.

-   **Authentication:** Bearer Token
-   **Success Response (204 No Content):** An empty response.

#### **`POST /contents/{id}/like`**

Toggles a "like" on a piece of content for the authenticated user.

-   **Authentication:** Bearer Token
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "message": "Like added successfully.", // or "Like removed successfully."
        "data": {
            "like_count": 15
        }
    }
    ```

---

### 5\. Comments Endpoints

#### **`POST /comments/{content_id}`**

Posts a new comment on a piece of content. The content must be `published`.

-   **Authentication:** Bearer Token
-   **Request Body:**
    ```json
    {
        "body": "This is a great post!"
    }
    ```
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
            // ...
        }
    }
    ```

#### **`DELETE /comments/{comment_id}`**

Deletes a comment. The user must be the owner of the comment or an admin.

-   **Authentication:** Bearer Token
-   **Success Response (204 No Content):** An empty response.

---

### 6\. Media Endpoints

#### **`POST /media`**

Uploads a new file.

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
            // ...
        }
    }
    ```

---

### 7\. Options Endpoints

Manage global site settings.

#### **`GET /options`**

Retrieves all global options as a single key-value object.

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

### 8\. Admin Endpoints

These endpoints require `admin` role.

#### **`GET /admin/contents`**

Retrieves a paginated list of **all** content, regardless of status.
 \* **Authentication:** Bearer Token (Admin)

#### **`GET /admin/media`**

Retrieves a paginated list of all uploaded media files.
 \* **Authentication:** Bearer Token (Admin)

#### **`DELETE /admin/media/{id}`**

Permanently deletes a media file from the server and the database.
 \* **Authentication:** Bearer Token (Admin)

#### **`POST /admin/users/{id}/role`**

Changes the role of a specific user by assigning a `role_id`.

_ **Authentication:** Bearer Token (Admin)
 _ **Request Body:**
   `json
   {
       "role_id": 2 
   }
   `
 \* **Success Response (200 OK):** (Returns the user object with the nested role)

#### **`POST /admin/options`**

Batch updates global options.
 \* **Authentication:** Bearer Token (Admin)

---

### 9. Role Management Endpoints (Admin Only)

Endpoints for creating, reading, updating, and deleting roles. Permissions are assigned as an array of strings.

**Base Path:** `/admin/roles`

#### **`GET /admin/roles`**

Retrieves a list of all roles.

-   **Authentication:** Bearer Token (Admin)
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

Creates a new role.

-   **Authentication:** Bearer Token (Admin)
-   **Request Body:**
    ```json
    {
        "name": "editor",
        "description": "Can edit all content.",
        "permissions": ["content.create", "content.update", "content.delete"]
    }
    ```
-   **Success Response (201 Created):** (Returns the created role object)

#### **`POST /admin/roles/{id}`**

Updates an existing role's details and/or permissions.

-   **Authentication:** Bearer Token (Admin)
-   **Request Body:** (Include only fields to update)
    ```json
    {
        "description": "Can create and edit all content.",
        "permissions": ["content.create", "content.update"]
    }
    ```
-   **Success Response (200 OK):** (Returns the updated role object)

#### **`DELETE /admin/roles/{id}`**

Deletes a role. Default 'admin' and 'user' roles cannot be deleted. A role cannot be deleted if it is assigned to any users.

-   **Authentication:** Bearer Token (Admin)
-   **Success Response (204 No Content):** An empty response.
