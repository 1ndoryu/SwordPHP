# SwordPHP CMS - Roadmap de Desarrollo

> **Objetivo:** Transformar SwordPHP de un CMS headless a un CMS completo que compita con WordPress, manteniendo la filosofía de simplicidad y rendimiento.

---

## Decisiones de Arquitectura

| Aspecto                 | Decisión                                             |
| ----------------------- | ---------------------------------------------------- |
| **Panel Admin**         | PHP server-side rendered (SSR) con vistas nativas    |
| **Motor de Plantillas** | PHP puro (sin dependencias externas)                 |
| **Base de Datos**       | PostgreSQL con JSONB (existente)                     |
| **Autenticación**       | JWT / Session cookie para admin                      |
| **Estilos Admin**       | CSS centralizado, dark mode, Source Sans Pro 12-13px |

---

## Arquitectura de Directorios (Final)

```
SwordPHP/
├── app/                        # Backend API y Controladores
│   ├── controller/
│   │   ├── admin/              # Controladores del Panel Admin (NUEVO)
│   │   └── ...                 # Controladores de API existentes
│   ├── model/
│   ├── middleware/
│   ├── view/                   # Vistas (Plantillas PHP)
│   │   ├── admin/              # Vistas del Panel Admin
│   │   │   ├── layouts/        # Layout principal, header, sidebar
│   │   │   ├── pages/          # Páginas específicas (dashboard, posts, etc.)
│   │   │   └── components/     # Fragmentos reutilizables (forms, tables)
│   │   └── ...
│   └── ...
├── admin/                      # Assets estáticos del admin (CSS, JS, img)
│   ├── css/
│   ├── js/
│   └── img/
├── themes/                     # Temas para frontend público
│   └── developer/              # Tema base minimalista
│       ├── templates/
│       │   ├── index.php       # Página de inicio
│       │   ├── single.php      # Post individual
│       │   ├── page.php        # Página estática
│       │   ├── archive.php     # Listado de posts
│       │   ├── header.php      # Cabecera común
│       │   ├── footer.php      # Pie común
│       │   └── 404.php         # Página no encontrada
│       ├── assets/
│       │   ├── css/
│       │   ├── js/
│       │   └── images/
│       ├── functions.php       # Funciones del tema
│       └── theme.json          # Metadatos del tema
├── config/
├── public/                     # Archivos estáticos públicos
└── runtime/
```

---

## Especificaciones de Diseño UI (Panel Admin)

| Propiedad            | Valor                                        |
| -------------------- | -------------------------------------------- |
| **Modo de color**    | Dark mode exclusivo                          |
| **Fuente principal** | Source Sans Pro                              |
| **Tamaño de fuente** | 12-13px (compacto)                           |
| **Sombras**          | Ninguna (diseño flat)                        |
| **Bordes**           | Sutiles, 1px, colores oscuros                |
| **IDs HTML**         | camelCase obligatorio (ej: `tabInicio`)      |
| **Iconos**           | Lucide React (minimalistas, línea)           |
| **Espaciado**        | Compacto, alta densidad de información       |
| **Animaciones**      | Solo transiciones funcionales (150-200ms)    |
| **Color de acento**  | Por definir (sugerencia: azul o verde menta) |
| **Navegación Tabs**  | Cambio sin recarga de página (CSS/JS)        |

---

## Fases de Desarrollo

---

### FASE 1: Infraestructura del Panel Admin (PHP SSR)
**Duración estimada:** 1 semana  
**Estado:** [x] En Progreso

#### Objetivo
Tener el panel renderizado desde el servidor (PHP) con un sistema de layouts y autenticación basada en cookies/sesión.

#### Tareas

- [x] **1.1 Estructura de Vistas y Layouts**
  - Crear directorio `app/view/admin`
  - Implementar sistema de helper `view()` simple
  - Crear layout base `layout.php` (Header, Sidebar, Footer)
  - Configurar assets (CSS/JS) en `public/admin`

- [x] **1.2 Configuración de Rutas Admin**
  - Crear grupo de rutas `/admin` en `config/route/admin.php`
  - Controlador `Admin/DashboardController`
  - Controlador `Admin/AuthController`

- [x] **1.3 Sistema de Diseño (CSS Puro)**
  - Migrar y centralizar estilos en `public/admin/css/style.css`
  - Implementar `AssetManager` para versionado automático
  - Refactorizar nomenclaturas de clases a español
  - Componentes CSS puros (sin JS innecesario)
  - [x] Implementar tabs que cambien sin recargar la página

- [x] **1.4 Autenticación Admin**
  - [x] Login form (POST a `Admin/AuthController`)
  - [x] Middleware `AdminAuth` para proteger rutas `/admin`
  - [x] Uso de sesiones PHP nativas o cookies seguras
  - [x] Logout

#### Entregables
- Panel accesible en `http://localhost:8787/admin` sin necesidad de build steps (npm)
- Login funcional
- Layout responsivo con Sidebar

---

### FASE 2: Gestion de Contenidos
**Duracion estimada:** 1-2 semanas  
**Estado:** [x] En Progreso

#### Objetivo
CRUD completo de contenidos desde el panel admin.

#### Tareas

- [x] **2.1 Listado de contenidos**
  - Tabla con columnas: titulo, tipo, estado, autor, fecha
  - Paginacion
  - Filtros por tipo y estado
  - Busqueda por titulo
  - Acciones rapidas (editar, eliminar, ver)

- [x] **2.2 Editor de contenido**
  - Formulario de creacion/edicion
  - Campo de titulo
  - Editor de contenido (textarea)
  - Selector de slug (autocompletado desde titulo)
  - Selector de estado (borrador, publicado)

- [x] **2.3 Panel lateral del editor**
  - Selector de estado y visibilidad
  - Informacion del contenido (tipo, fechas, ID)
  - Campos personalizados/Metadatos (implementado con content_data JSONB)
  - Vista de JSON crudo para depuracion

- [x] **2.4 Acciones del editor**
  - Guardar como borrador
  - Publicar
  - Actualizar
  - Eliminar (con confirmacion via modal)

- [x] **2.5 Vista previa**
  - Boton para previsualizar contenido
  - Abrir en nueva pestana

- [ ] **2.6 Imagenes del contenido** (PENDIENTE)
  - Imagen de portada/destacada
  - Galeria de imagenes adjuntas
  - Selector de medios integrado (depende de FASE 4)

- [x] **2.7 Sistema de Papelera**
  - Soft delete en lugar de eliminacion permanente
  - Vista de contenidos en papelera
  - Restaurar contenidos
  - Vaciar papelera (eliminar permanentemente)

#### Entregables
- Crear, editar, eliminar y listar posts desde el panel
- Cambiar estado de publicacion

---

### FASE 3: Sistema de Post Types
**Duración estimada:** 2 semanas  
**Estado:** [ ] Pendiente

#### Objetivo
Post Types dinámicos con campos personalizados (estilo ACF).

#### Tareas

- [ ] **3.1 Modelo de datos para Post Types**
  - Nueva tabla `post_types`:
    ```sql
    CREATE TABLE post_types (
        id SERIAL PRIMARY KEY,
        slug VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        icon VARCHAR(50),
        supports JSONB DEFAULT '["title", "editor", "thumbnail"]',
        fields JSONB DEFAULT '[]',
        is_system BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ```
  - Post Types predeterminados: `post`, `page`

- [ ] **3.2 API de Post Types**
  - `GET /admin/post-types` - Listar todos
  - `GET /admin/post-types/{slug}` - Obtener uno
  - `POST /admin/post-types` - Crear
  - `PUT /admin/post-types/{slug}` - Actualizar
  - `DELETE /admin/post-types/{slug}` - Eliminar (solo si no es system)

- [ ] **3.3 Sistema de campos personalizados**
  - Tipos de campos soportados:
    - `text` - Texto corto
    - `textarea` - Texto largo
    - `richtext` - Editor WYSIWYG
    - `number` - Número
    - `email` - Email
    - `url` - URL
    - `date` - Fecha
    - `datetime` - Fecha y hora
    - `select` - Selección única
    - `multiselect` - Selección múltiple
    - `checkbox` - Casilla
    - `radio` - Opciones radio
    - `image` - Selector de imagen
    - `gallery` - Galería de imágenes
    - `file` - Archivo
    - `relation` - Relación con otro contenido
    - `repeater` - Grupo repetible de campos

- [ ] **3.4 UI de gestión de Post Types**
  - Página de listado de Post Types
  - Editor de Post Type
  - Constructor de campos drag & drop
  - Configuración de cada campo (label, placeholder, requerido, etc.)

- [ ] **3.5 Integración con editor de contenidos**
  - Renderizado dinámico de campos según Post Type
  - Validación de campos requeridos
  - Guardar datos en `content_data` JSONB

- [ ] **3.6 Navegación dinámica**
  - Sidebar muestra Post Types registrados
  - Cada Post Type tiene su propia sección

#### Entregables
- Crear Post Types personalizados desde el panel
- Definir campos personalizados para cada tipo
- Formularios dinámicos según el Post Type

---

### FASE 4: Sistema de Medios
**Duración estimada:** 1 semana  
**Estado:** [ ] Pendiente

#### Objetivo
Librería de medios completa estilo WordPress.

#### Tareas

- [ ] **4.1 Vista de librería de medios**
  - Vista de grilla con miniaturas
  - Vista de lista con detalles
  - Toggle entre vistas

- [ ] **4.2 Upload de archivos**
  - Zona de drag & drop
  - Botón de selección de archivos
  - Upload múltiple
  - Barra de progreso
  - Validación de tipos y tamaños

- [ ] **4.3 Modal selector de medios**
  - Componente reutilizable para seleccionar medios
  - Integración con editor de contenidos
  - Filtros por tipo (imagen, video, documento)

- [ ] **4.4 Detalles de medio**
  - Vista/edición de metadatos
  - Alt text, título, descripción
  - Información del archivo (tamaño, dimensiones, tipo)
  - URL del archivo

- [ ] **4.5 Acciones sobre medios**
  - Eliminar (con confirmación)
  - Copiar URL
  - Descargar

#### Entregables
- Galería de medios funcional
- Upload con drag & drop
- Selector de medios integrable en cualquier formulario

---

### FASE 5: Sistema de Temas y Plantillas
**Duración estimada:** 2-3 semanas  
**Estado:** [ ] Pendiente

#### Objetivo
Renderizar páginas públicas con temas PHP.

#### Tareas

- [ ] **5.1 Estructura de un tema**
  - Definir estructura de carpetas
  - Archivo `theme.json` con metadatos:
    ```json
    {
        "name": "Developer Theme",
        "version": "1.0.0",
        "author": "SwordPHP",
        "description": "Tema minimalista para desarrolladores",
        "screenshot": "screenshot.png"
    }
    ```
  - Archivo `functions.php` para funciones del tema

- [ ] **5.2 Motor de renderizado PHP**
  - Clase `ThemeEngine` para cargar y renderizar plantillas
  - Sistema de variables disponibles en plantillas
  - Funciones helper: `get_header()`, `get_footer()`, `the_title()`, etc.
  - Inclusión de parciales

- [ ] **5.3 Jerarquía de plantillas**
  - Similar a WordPress:
    ```
    Página individual:
    1. page-{slug}.php
    2. page-{id}.php
    3. page.php
    4. single.php
    5. index.php
    
    Post individual:
    1. single-{post_type}-{slug}.php
    2. single-{post_type}.php
    3. single.php
    4. index.php
    
    Archivo/Listado:
    1. archive-{post_type}.php
    2. archive.php
    3. index.php
    ```

- [ ] **5.4 Rutas públicas**
  - Controlador `FrontendController`
  - Rutas dinámicas para contenidos:
    - `/` - Página de inicio
    - `/{slug}` - Página o post por slug
    - `/blog` - Archivo de posts
    - `/categoria/{slug}` - Archivo por categoría (futuro)

- [ ] **5.5 Funciones de tema (Template Tags)**
  - `get_header()` / `get_footer()`
  - `the_title()` / `get_the_title()`
  - `the_content()` / `get_the_content()`
  - `the_permalink()` / `get_permalink()`
  - `the_thumbnail()` / `get_thumbnail_url()`
  - `get_posts()` - Obtener lista de posts
  - `get_option()` - Ya existe
  - `site_url()` / `home_url()`
  - `asset_url()` - URL de assets del tema

- [ ] **5.6 Panel de temas**
  - Página de listado de temas instalados
  - Preview de tema
  - Activar tema

- [ ] **5.7 Tema "Developer" (default)**
  - Diseño minimalista
  - Dark mode
  - Tipografía limpia
  - Plantillas básicas funcionales

#### Entregables
- URLs públicas renderizan HTML completo
- Sistema de temas intercambiables
- Tema por defecto funcional

---

### FASE 6: Páginas y Menús
**Duración estimada:** 1 semana  
**Estado:** [ ] Pendiente

#### Objetivo
Gestión de páginas estáticas y sistema de navegación.

#### Tareas

- [ ] **6.1 Diferenciación Páginas vs Posts**
  - Sección separada en sidebar para Páginas
  - Páginas no tienen fecha de publicación
  - Páginas pueden ser jerárquicas (padre/hijo)

- [ ] **6.2 Sistema de menús**
  - Nueva tabla `menus`:
    ```sql
    CREATE TABLE menus (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        location VARCHAR(50),
        items JSONB DEFAULT '[]',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ```
  - Estructura de items:
    ```json
    [
        {
            "id": "uuid",
            "type": "page|post|custom|category",
            "object_id": 123,
            "title": "Inicio",
            "url": "/",
            "target": "_self",
            "children": []
        }
    ]
    ```

- [ ] **6.3 API de menús**
  - `GET /admin/menus` - Listar
  - `POST /admin/menus` - Crear
  - `PUT /admin/menus/{id}` - Actualizar
  - `DELETE /admin/menus/{id}` - Eliminar
  - `GET /menus/{location}` - Obtener menú público por ubicación

- [ ] **6.4 Editor de menús**
  - UI drag & drop para ordenar items
  - Añadir páginas, posts, links personalizados
  - Items anidados (submenús)
  - Asignar menú a ubicación (header, footer)

- [ ] **6.5 Funciones de menú para temas**
  - `get_menu($location)` - Obtener items del menú
  - `render_menu($location)` - Renderizar HTML del menú

#### Entregables
- Gestión de páginas jerárquicas
- Sistema de menús con drag & drop
- Menús renderizables en temas

---

### FASE 7: Usuarios y Permisos
**Duración estimada:** 1 semana  
**Estado:** [ ] Pendiente

#### Objetivo
Gestión completa de usuarios desde el panel.

#### Tareas

- [ ] **7.1 Listado de usuarios**
  - Tabla con: nombre, email, rol, fecha de registro
  - Búsqueda
  - Filtro por rol
  - Paginación

- [ ] **7.2 Editor de usuario**
  - Editar información básica
  - Cambiar rol
  - Resetear contraseña
  - Desactivar/activar cuenta

- [ ] **7.3 Gestión de roles**
  - Listado de roles
  - Crear rol personalizado
  - Editor de permisos por rol
  - Permisos granulares

- [ ] **7.4 Perfil del administrador**
  - Editar perfil propio
  - Cambiar contraseña
  - Avatar

#### Entregables
- CRUD de usuarios desde el panel
- Gestión de roles y permisos

---

### FASE 8: Configuración y Opciones
**Duración estimada:** 1 semana  
**Estado:** [ ] Pendiente

#### Objetivo
Panel de ajustes del sitio.

#### Tareas

- [ ] **8.1 Ajustes generales**
  - Título del sitio
  - Descripción/tagline
  - Logo
  - Favicon
  - Zona horaria
  - Formato de fecha

- [ ] **8.2 Ajustes de lectura**
  - Página de inicio (últimos posts o página estática)
  - Posts por página
  - Página de blog

- [ ] **8.3 Ajustes de permalinks**
  - Estructura de URLs
  - Prefijos por Post Type

- [ ] **8.4 Ajustes de medios**
  - Tamaños de imagen
  - Límite de subida

#### Entregables
- Panel de configuración completo
- Opciones persistidas en base de datos

---

## Fases Futuras (Post-MVP)

### FASE 9: Taxonomías (Categorías y Etiquetas)
- Categorías jerárquicas
- Etiquetas
- Taxonomías personalizadas por Post Type

### FASE 10: Widgets y Áreas de Widgets
- Sistema de widgets
- Áreas de widgets definidas por tema
- Widgets predeterminados (búsqueda, posts recientes, etc.)

### FASE 11: SEO
- Meta títulos y descripciones por contenido
- Open Graph
- Sitemap XML
- Schema markup

### FASE 12: Plugins
- Sistema de plugins
- Hooks y filtros
- API de plugins

### FASE 13: Editor de Bloques
- Editor moderno tipo Gutenberg
- Bloques reutilizables
- Patrones

---

## Notas Técnicas

### Renderizado de Vistas PHP

Las vistas se renderizarán usando `ob_start()` y `ob_get_clean()` para capturar el HTML.
Se usará una función helper `render_view($template, $data = [])` que:
1. Extrae las variables `$data`.
2. Incluye el archivo de vista `app/view/$template.php`.
3. Retorna el string HTML capturado.


### Campos Personalizados - Estructura JSONB

Los campos personalizados se almacenan en `content_data`:

```json
{
    "title": "Mi Post",
    "content": "<p>Contenido aquí</p>",
    "custom_fields": {
        "precio": 99.99,
        "galeria": [1, 2, 3],
        "autor_relacionado": 45
    }
}
```

### Definición de Campos en Post Type

```json
{
    "fields": [
        {
            "key": "precio",
            "label": "Precio",
            "type": "number",
            "required": true,
            "placeholder": "0.00",
            "min": 0,
            "step": 0.01
        },
        {
            "key": "galeria",
            "label": "Galería de Imágenes",
            "type": "gallery",
            "required": false
        }
    ]
}
```

---

## Métricas de Éxito

- [ ] Panel de admin carga en < 2 segundos
- [ ] Crear un post toma < 30 segundos
- [ ] Páginas públicas renderizan en < 100ms
- [ ] Soporte para > 10,000 contenidos sin degradación
- [ ] 0 dependencias externas en el motor de plantillas

---

## Changelog

| Fecha      | Versión | Cambios                       |
| ---------- | ------- | ----------------------------- |
| 2024-12-14 | 0.1.0   | Documento inicial del roadmap |

---

> **Próximo paso:** Iniciar Fase 1 - Infraestructura del Panel Admin
