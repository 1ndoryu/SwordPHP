---
title: Documentación de la API (Kamples)
description: Documentación completa y detallada de la API RESTful para Kamples, el CMS Headless de SwordPHP, diseñada para ser robusta, predecible y de alto rendimiento.
---

# Documentación de la API de Kamples

## 1\. Introducción y Filosofía

Bienvenido a la documentación de la API RESTful de SwordPHP para Kamples. Esta API está diseñada siguiendo los principios de predictibilidad y facilidad de uso, permitiéndote interactuar con tu contenido, usuarios y configuraciones de forma programática y eficiente.

-   **URL Base:** Todas las rutas de la API están prefijadas con `/api/v1`. La URL completa sería `https://tu-dominio.com/api/v1`.
-   **Formato de Datos:** Todas las peticiones y respuestas utilizan el formato `JSON`. La cabecera `Content-Type: application/json` es requerida para las peticiones `POST` y `PUT` que envían datos en este formato.
-   **Convención de Nombres:** Todos los nombres de los campos (claves) en las peticiones y respuestas JSON utilizan `snake_case` para mantener la consistencia (ej: `nombre_usuario`, `created_at`).

## 2\. Autenticación

La mayoría de los endpoints requieren un **Bearer Token** para la autenticación.

### Flujo de Autenticación

1.  **Obtener un Token:** Envía las credenciales de un usuario al endpoint `POST /auth/token`.
2.  **Usar el Token:** Incluye el token recibido en la cabecera `Authorization` de cada petición a un endpoint protegido: `Authorization: Bearer <TU_TOKEN_DE_API>`.
3.  **Verificar Sesión:** Para obtener los datos del usuario actual y confirmar que tu token es válido, haz una petición a `GET /api/v1/users/me`.

### `POST /auth/token`

Intercambia credenciales de usuario por un token de API.

-   **Permisos:** Público.
-   **Cuerpo de la Petición:**
    ```json
    {
        "nombre_usuario": "autor_uno",
        "clave": "contraseña_segura"
    }
    ```
-   **Respuesta Exitosa (200 OK):**
    ```json
    {
        "data": {
            "token": "aqui_el_jwt_o_bearer_token_generado_muy_largo_y_seguro",
            "usuario": {
                "id": 2,
                "nombre_usuario": "autor_uno",
                "nombre_mostrado": "Autor Uno",
                "correo_electronico": "autor@kamples.com",
                "rol": "autor"
            }
        }
    }
    ```
-   **Respuestas de Error:** `401 Unauthorized` si las credenciales son incorrectas, `422 Unprocessable Entity` si faltan campos.

## 3\. Convenciones y Buenas Prácticas

### Estructura de Respuesta

-   **Para Respuestas Exitosas (2xx):**
    -   Si devuelve un solo recurso: `{ "data": { ...objeto_recurso } }`
    -   Si devuelve una lista de recursos: `{ "data": { "items": [ ... ], "pagination": { ... } } }`
-   **Para Respuestas de Error (4xx, 5xx):**
    -   Usa siempre esta estructura: `{ "error": { "code": <código_http>, "message": "<mensaje>", "details": [ ... ] } }`
    -   El campo `details` es opcional y sirve para desglosar errores, como fallos de validación.

### Códigos de Error HTTP

-   `400 Bad Request`: Petición malformada (ej. JSON inválido).
-   `401 Unauthorized`: Autenticación fallida. El token es inválido, ha expirado o no se incluyó.
-   `403 Forbidden`: Autenticado, pero sin los permisos necesarios para realizar la acción.
-   `404 Not Found`: El recurso solicitado (ej. un usuario con un ID específico) no existe.
-   `422 Unprocessable Entity`: La sintaxis de la petición es correcta, pero tiene errores de validación (ej. campo requerido ausente).
-   `429 Too Many Requests`: Se ha excedido el límite de peticiones.
-   `500 Internal Server Error`: Un error inesperado ocurrió en el servidor.

### Límites de la API (Rate Limiting)

Para garantizar la estabilidad y el uso justo, la API está protegida por un límite de peticiones (ej. 1000 peticiones por hora por usuario/IP). Si excedes este límite, recibirás una respuesta `429 Too Many Requests`. Las siguientes cabeceras HTTP se envían con cada respuesta para que puedas gestionar tu tasa de peticiones:

-   `X-RateLimit-Limit`: El número total de peticiones permitidas en la ventana de tiempo actual.
-   `X-RateLimit-Remaining`: El número de peticiones restantes en la ventana actual.
-   `X-RateLimit-Reset`: El tiempo (en segundos Unix) hasta que se reinicie el límite.

## 4\. Roles y Permisos

La API utiliza un sistema de **Roles y Capacidades** para gestionar el acceso a los recursos. Este diseño granular te permite un control total sobre lo que cada tipo de usuario puede hacer.

-   **Rol:** Es una etiqueta que se asigna a un usuario (ej. `editor`, `autor`). Define un conjunto de permisos.
-   **Capacidad:** Es un permiso específico para realizar una acción concreta (ej. `create_content`, `manage_users`).

Este sistema se configura íntegramente en el archivo `swordCore/config/permisos.php`. Puedes modificar este archivo para crear roles personalizados o ajustar los permisos de los roles existentes para que se adapten perfectamente a las necesidades de tu aplicación.

### Roles Predeterminados

A continuación se describen los roles que vienen por defecto con SwordPHP:

-   **`admin` (Administrador):** Tiene acceso a todas las funcionalidades sin restricciones. Puede gestionar contenido, usuarios, y configuraciones del sitio. Es el rol más alto.
-   **`editor` (Editor):** Puede crear, editar y eliminar cualquier contenido, incluyendo el de otros usuarios. No puede gestionar usuarios ni configuraciones del sitio.
-   **`autor` (Autor):** Puede crear, editar y eliminar su propio contenido. No puede modificar el contenido de otros usuarios.
-   **`colaborador` (Colaborador):** Puede crear y editar su propio contenido, pero no puede publicarlo ni eliminarlo. Un `editor` o `admin` debe revisar y aprobar sus cambios.
-   **`suscriptor` (Suscriptor):** Es un usuario registrado que puede interactuar con el contenido (ej. dar "like", comentar), pero no puede crear contenido.
-   **`anonimo` (Anónimo):** Representa a un visitante no autenticado. Por defecto, no tiene capacidades de escritura.

### Matriz de Capacidades por Rol

La siguiente tabla muestra las capacidades asignadas a cada rol por defecto. Una `✓` indica que el rol posee esa capacidad.

| Capacidad            | admin | editor | autor | colaborador | suscriptor |
| -------------------- | :---: | :----: | :---: | :---------: | :--------: |
| `manage_options`     |   x   |        |       |             |            |
| `manage_content`     |   x   |        |       |             |            |
| `manage_users`       |   x   |        |       |             |            |
| `create_content`     |   x   |   x    |   x   |      x      |            |
| `edit_own_content`   |   x   |   x    |   x   |      x      |            |
| `delete_own_content` |   x   |   x    |   x   |             |            |
| `like_content`       |   x   |   x    |   x   |      x      |     x      |
| `comment_content`    |   x   |   x    |   x   |      x      |     x      |

_Nota: El rol `admin` hereda implícitamente todas las capacidades._

### Permisos por Tipo de Contenido

Además de las capacidades generales, puedes definir qué roles pueden crear tipos de contenido específicos. Esta configuración se encuentra en la sección `tipos_contenido` del mismo archivo `config/permisos.php`.

Por ejemplo, la configuración por defecto permite a los `autores` crear `samples`, pero no `paginas`.

```php
// swordCore/config/permisos.php

'tipos_contenido' => [
    'admin' => ['pagina', 'sample', 'comentario'],
    'editor' => ['pagina', 'sample', 'comentario'],
    'autor' => ['sample'], // <-- Solo pueden crear 'sample'
    'colaborador' => ['sample'],
    'suscriptor' => [],
    'anonimo' => [],
],
```

Esta flexibilidad te permite, por ejemplo, crear un rol `gestor_eventos` que solo tenga permiso para crear y gestionar un tipo de contenido `evento`.

## 5\. Recursos de la API (Endpoints)

### Recurso: `/media`

#### `POST /media/upload`

Sube un archivo (imagen, audio, etc.) o importa un archivo desde una URL externa. Tras una subida exitosa, la API crea un registro en la base de datos y devuelve el objeto `Media` completo. Este objeto contiene el `id` que luego puedes usar para asociar el archivo a cualquier pieza de contenido.

-   **Permisos:** Cualquier usuario autenticado.

-   **Cuerpo de la Petición:** Se aceptan dos modos:

    -   **Subida Local (multipart/form-data):**
        -   `file` (file, **obligatorio**): El archivo a subir.
        -   `titulo` (string, opcional): Un título para el archivo. Si no se proporciona, se usará el nombre original del archivo.
        -   `storage_provider` (string, opcional): `local` o `casiel`. Por defecto es `local`.
    -   **Importación desde URL (application/json):**
        -   `storage_provider` (string, **obligatorio**): Debe ser `external`.
        -   `url` (string, **obligatorio**): La URL pública del archivo a importar.
        -   `titulo` (string, opcional): Un título para el archivo. Si no se proporciona, se extraerá de la URL.

-   **Ejemplo de Petición (subida local):**

    ```bash
    curl -X POST \
      https://tu-dominio.com/api/v1/media/upload \
      -H "Authorization: Bearer <TU_TOKEN>" \
      -F "file=@/ruta/a/tu/archivo.jpg" \
      -F "titulo=Mi Foto de Verano"
    ```

-   **Ejemplo de Petición (importación desde URL):**

    ```bash
    curl -X POST \
      https://tu-dominio.com/api/v1/media/upload \
      -H "Authorization: Bearer <TU_TOKEN>" \
      -H "Content-Type: application/json" \
      -d '{
            "storage_provider": "external",
            "url": "https://ejemplo.com/imagen.png",
            "titulo": "Logo de la Empresa"
          }'
    ```

-   **Respuesta Exitosa (201 Created):** Devuelve el objeto `Media` completo recién creado.

    ```json
    {
        "data": {
            "id": 123,
            "idautor": 1,
            "titulo": "mi_sample.mp3",
            "leyenda": null,
            "textoalternativo": null,
            "descripcion": null,
            "rutaarchivo": "202506/667b5e4c0a1b2.mp3",
            "tipomime": "audio/mpeg",
            "metadata": {
                "provider": "local",
                "url": "https://tu-dominio.com/swordContent/media/202506/667b5e4c0a1b2.mp3",
                "size": 4500000,
                "nombre_original": "mi_sample.mp3"
            },
            "created_at": "2025-06-25T21:45:16.000000Z",
            "updated_at": "2025-06-25T21:45:16.000000Z",
            "url_publica": "https://tu-dominio.com/swordContent/media/202506/667b5e4c0a1b2.mp3"
        }
    }
    ```

#### `GET /media/{id}/download`

Descarga el archivo original asociado a un registro de media. El comportamiento exacto depende del proveedor de almacenamiento (`storageService`) configurado en el backend:

-   **Si el proveedor es un servicio en la nube (ej. S3, Casiel):** La API devolverá una redirección (`302 Found`) a una URL de descarga segura y pre-firmada. El cliente debe seguir esta redirección para obtener el archivo.

-   **Si el proveedor es el almacenamiento local:** La API devolverá el archivo directamente en el cuerpo de la respuesta con las cabeceras `Content-Disposition` apropiadas para iniciar la descarga en el navegador.

-   **Permisos:** Requiere autenticación. La lógica de negocio puede aplicar permisos adicionales (ej. solo el propietario puede descargar).

-   **Respuesta Exitosa:**

    -   `302 Found` con una cabecera `Location` apuntando a la URL de descarga.
    -   `200 OK` con el contenido del archivo.

-   **Respuestas de Error:**

    -   `404 Not Found`: Si el `media` o el archivo físico no existen.
    -   `501 Not Implemented`: Si el `storageService` configurado no soporta descargas directas.

### Recurso: `/content`

Endpoint genérico para gestionar todos los tipos de contenido (`sample`, `comment`, `page`, etc.).

#### `GET /content`

Recupera una lista paginada de contenidos con capacidades avanzadas de consulta.

-   **Permisos:** Público.
-   **Parámetros de Consulta:**
    -   `type` (string, **obligatorio**): Slug del tipo de contenido. Ej: `?type=sample`.
    -   `per_page` (int, opcional): Número de ítems por página. Por defecto: `10`.
    -   `page` (int, opcional): El número de la página a obtener. Por defecto: `1`.
    -   **Búsqueda:**
        -   `q` (string, opcional): Término de búsqueda de texto libre en títulos y contenido. Ej: `?q=guitarra acustica`.
    -   **Filtrado:**
        -   `id_autor` (int, opcional): Filtra los contenidos que pertenecen a un autor específico. Ej: `?id_autor=2`.
        -   `metadata[clave]` (string, opcional): Filtra por un valor exacto en un campo de la metadata JSON. Ej: `?metadata[tonalidad]=Cm`.
    -   **Ordenamiento:**
        -   `sort_by` (string, opcional): Campo por el cual ordenar. Por defecto: `created_at`. Valores permitidos: `created_at`, `updated_at`, `titulo`.
        -   `order` (string, opcional): Dirección del ordenamiento. Por defecto: `desc`. Valores permitidos: `asc`, `desc`.
    -   **Optimización (Eager Loading):**
        -   `include` (string, opcional): Carga relaciones para evitar peticiones N+1. La única relación permitida actualmente es `autor`. Ej: `?include=autor`.

#### `GET /content/{id}`

Recupera una única pieza de contenido por su ID.

-   **Permisos:** Público (si el contenido tiene estado `publicado`).
-   **Parámetros de Consulta:** También soporta el parámetro `include=autor`.

#### `POST /content`

Crea una nueva pieza de contenido. Para asociar un archivo (como un audio a un `sample`), primero debes subirlo usando `POST /media/upload` para obtener el `id` del medio.

-   **Permisos:** Requiere la capacidad `create_content` para el tipo de contenido especificado.
-   **Nota sobre Metadata:** El campo `metadata` es un objeto JSON flexible. Para asociar un archivo subido, incluye la clave `media_id` con el `id` obtenido del endpoint de subida.
-   **Cuerpo de la Petición (ejemplo para un `sample`):**
    ```json
    {
        "titulo": "Mi nuevo Sample de Guitarra",
        "tipocontenido": "sample",
        "estado": "publicado",
        "metadata": {
            "media_id": 123,
            "bpm": 120,
            "tonalidad": "Cm"
        }
    }
    ```
-   **Respuesta Exitosa (201 Created):** Devuelve el objeto completo del contenido recién creado.

#### `PUT /content/{id}`

Actualiza un contenido existente. Solo necesitas enviar los campos que deseas cambiar.

-   **Permisos:** Requiere la capacidad `edit_own_content` o `edit_others_content` para el tipo de contenido especificado.
-   **Respuesta Exitosa (200 OK):** Devuelve el objeto completo del contenido actualizado.

#### `DELETE /content/{id}`

Elimina un contenido.

-   **Permisos:** Requiere la capacidad `delete_own_content` o `delete_others_content` para el tipo de contenido especificado.
-   **Respuesta Exitosa (204 No Content):** No devuelve cuerpo en la respuesta.

#### `POST /content/{id}/like`

Marca un contenido (como un `sample`) como favorito para el usuario actual.

-   **Permisos:** Cualquier usuario autenticado.
-   **Respuesta Exitosa:** `204 No Content`.

#### `DELETE /content/{id}/like`

Quita el "like" o favorito de un contenido.

-   **Permisos:** Cualquier usuario autenticado.
-   **Respuesta Exitosa:** `204 No Content`.

#### `POST /content/{id}/comments`

Añade un comentario a un contenido específico.

-   **Permisos:** Cualquier usuario autenticado.
-   **Cuerpo de la Petición:**
    ```json
    {
        "contenido": "¡Gran sonido! Me encanta la textura."
    }
    ```
-   **Respuesta Exitosa (201 Created):** Devuelve el objeto del comentario recién creado.

#### `GET /content/{id}/comments`

Lista todos los comentarios públicos de un contenido. Acepta los parámetros de paginación `page` y `per_page`.

-   **Permisos:** Público.
-   **Respuesta Exitosa (200 OK):** Devuelve una lista paginada de objetos de comentario.

### Recurso: `/users`

#### `GET /users/me`

Devuelve la información completa del usuario autenticado actualmente con el token. Es el método recomendado para verificar una sesión activa.

-   **Permisos:** Cualquier usuario autenticado.
-   **Respuesta Exitosa (200 OK):**
    ```json
    {
        "data": {
            "id": 2,
            "nombre_usuario": "usuario_ejemplo",
            "nombre_mostrado": "Usuario de Ejemplo",
            "correo_electronico": "usuario@ejemplo.com",
            "rol": "editor",
            "metadata": null,
            "created_at": "2025-06-22T10:00:00.000000Z",
            "updated_at": "2025-06-22T12:00:00.000000Z"
        }
    }
    ```

#### `PUT /users/{id}`

Actualiza el perfil de un usuario. Un usuario solo puede actualizar su propio perfil a menos que sea un `admin`.

-   **Permisos**: Propietario del perfil o `admin`.
-   **Cuerpo de la Petición (ejemplo)**:
    ```json
    {
        "nombre_mostrado": "El Artista Renovado",
        "correo_electronico": "nuevo_email@kamples.com",
        "clave": "nueva_contraseña_fuerte",
        "clave_confirmation": "nueva_contraseña_fuerte"
    }
    ```
-   **Nota**: El campo `rol` solo puede ser modificado por un `admin`. Si un usuario no-admin lo incluye, será ignorado.

#### `GET /users/{id}/profile-picture`

Recupera la imagen de perfil de un usuario.

-   **Permisos**: Público.
-   **Respuesta Exitosa (200 OK)**: Devuelve la imagen directamente con el `Content-Type` apropiado (ej. `image/jpeg`).
-   **Respuestas de Error**:
    -   `404 Not Found`: Si el usuario no existe o no tiene una imagen de perfil asignada.

_Para los demás endpoints (`GET /users`, `POST /users`, `DELETE /users/{id}`), consulta la matriz de permisos. Requieren rol de `admin` o ser el propietario del perfil._

### Recurso: `/permisos`

Este recurso permite gestionar dinámicamente el sistema de roles y capacidades de la API.

#### `GET /permisos`

Recupera el objeto completo de la configuración de permisos activa en el sistema. Si nunca se ha modificado a través de la API, devolverá la configuración por defecto del archivo `config/permisos.php`.

-   **Permisos:** Requiere la capacidad `manage_options` (solo para `admin` por defecto).
-   **Respuesta Exitosa (200 OK):**
    ```json
    {
        "data": {
            "api": {
                "admin": ["manage_options", "manage_content", "manage_users"],
                "editor": ["create_content", "edit_own_content", "delete_own_content"],
                "suscriptor": ["like_content", "comment_content"]
            },
            "tipos_contenido": {
                "admin": ["pagina", "sample"],
                "editor": ["pagina", "sample"],
                "autor": ["sample"]
            }
        }
    }
    ```

#### `PUT /permisos`

Actualiza y persiste la configuración de roles y capacidades. El cuerpo de la petición debe ser un objeto JSON completo que reemplace toda la configuración anterior.

-   **Permisos:** Requiere la capacidad `manage_options` (solo para `admin` por defecto).
-   **Cuerpo de la Petición:**
    ```json
    {
        "api": {
            "admin": ["manage_options", "manage_content", "manage_users"],
            "editor": ["create_content", "edit_own_content"],
            "gestor_eventos": ["create_content", "edit_own_content", "delete_own_content"]
        },
        "tipos_contenido": {
            "admin": ["pagina", "sample", "evento"],
            "editor": ["pagina", "sample"],
            "gestor_eventos": ["evento"]
        }
    }
    ```
-   **Respuesta Exitosa (200 OK):** Devuelve la nueva configuración de permisos guardada.
-   **Respuestas de Error:**
    -   `400 Bad Request`: Si el cuerpo de la petición está vacío o el JSON es inválido.

### Recurso: `/options`

#### `GET /options/{key}` y `POST /options`

Lee o escribe en el sistema de opciones clave-valor del sitio.

-   **Permisos:** Solo `admin`.
