# SwordPHP CMS - Roadmap de Desarrollo

> **Objetivo:** Transformar SwordPHP de un CMS headless a un CMS completo que compita con WordPress, manteniendo la filosofía de simplicidad y rendimiento.

---

## Decisiones de Arquitectura

| Aspecto                 | Decisión                                               |
| ----------------------- | ------------------------------------------------------ |
| **Panel Admin**         | **React + TypeScript (SPA)** servido por PHP           |
| **Build System**        | **Vite** (Salida estática a `public/admin/build`)      |
| **Motor de Plantillas** | PHP para layout inicial + React para interactividad    |
| **Base de Datos**       | PostgreSQL con JSONB (existente)                       |
| **Autenticación**       | JWT / Session cookie (Híbrido)                         |
| **Estilos Admin**       | **CSS Nativo** (Reutilización 100% de clases actuales) |

---

## Reglas de Desarrollo (Agente IA)

Las siguientes acciones están **PROHIBIDAS** para el agente de IA:

| Acción Prohibida                                               | Razón                                         |
| -------------------------------------------------------------- | --------------------------------------------- |
| Ejecutar `php windows.php` o comandos de reinicio del servidor | El usuario maneja el servidor manualmente     |
| Usar herramientas de navegador (`browser_subagent`, etc.)      | El usuario prueba manualmente en el navegador |
| Ejecutar comandos que modifiquen el estado del servidor        | Control manual del entorno                    |

### Principios de Refactorización Pragmática

> **Regla:** Las refactorizaciones deben aportar beneficios reales, no cumplir métricas arbitrarias.

| Principio                          | Descripción                                                                         |
| ---------------------------------- | ----------------------------------------------------------------------------------- |
| **Beneficio real > métrica**       | No reducir líneas solo por cumplir un número; dividir solo si mejora mantenibilidad |
| **Contexto importa**               | Un controlador API con 400 líneas y 14 métodos distintos puede ser correcto         |
| **Extraer cuando hay duplicación** | Crear servicios cuando 2+ controladores comparten lógica                            |
| **No romper lo que funciona**      | Si un archivo es grande pero cohesivo, dejarlo así                                  |
| **Límites son guías, no dogmas**   | 300 líneas es orientativo; 350 con buena razón es aceptable                         |

### Comandos de Desarrollo Disponibles

El agente **SÍ PUEDE** ejecutar los siguientes comandos para depuración:

| Comando                               | Descripción                                             |
| ------------------------------------- | ------------------------------------------------------- |
| `php webman dev:contents`             | Lista contenidos en la BD (filtros: -t, -s)             |
| `php webman dev:contents --type=post` | Filtrar por tipo                                        |
| `php webman dev:contents --trashed`   | Ver contenidos en papelera                              |
| `php webman dev:post-types`           | Lista Post Types registrados (predefinidos + dinamicos) |
| `php webman db:install`               | Crear tablas de la BD                                   |

---

## Refactorización SOLID (Pendiente)

### Análisis de Duplicación de Controladores

Actualmente existen controladores duplicados entre API y Admin:

| Controlador API                    | Controlador Admin                        | Lógica Compartida          |
| ---------------------------------- | ---------------------------------------- | -------------------------- |
| `app\controller\ContentController` | `app\controller\Admin\ContentController` | CRUD de contenidos         |
| `app\controller\AuthController`    | `app\controller\Admin\AuthController`    | Validación de credenciales |

### Plan de Refactorización

1. **Crear `app\services\ContentService`**
   - Extraer lógica de negocio compartida
   - Métodos: `crear()`, `actualizar()`, `eliminar()`, `listar()`, `obtenerPorId()`
   - Los controladores solo manejan request/response

2. **Crear `app\services\AuthService`**
   - Extraer validación de credenciales
   - Métodos: `validarCredenciales()`, `generarJwtToken()`, `crearSesion()`

3. **Beneficios**
   - Single Responsibility: Controladores solo para HTTP, Servicios para lógica
   - DRY: Sin duplicación de código
   - Testeable: Servicios fáciles de probar unitariamente

**Estado:** [ ] Pendiente - Planificado para después de FASE 3

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

## ✅ FASES COMPLETADAS (Resumen)

Las siguientes fases han sido completadas exitosamente. Se mantiene el resumen para referencia histórica.

---

### PROYECTO: MIGRACIÓN A REACT + TYPESCRIPT ✅
**Estado:** Completado | **Estrategia:** "Strangler Fig"

**Arquitectura:** React 18 + TypeScript + Vite → `public/admin/build` | PHP sirve layout + `window.sword`

**Logros:**
- M1. Infraestructura Base (Vite, `Vite.php`, `layout.php`, `window.sword`)
- M2. Biblioteca UI (Button, Panel, Badge, Alert, Input, Textarea, Select, Toolbar)
- M3. Páginas (Dashboard, Listado de Contenidos, Editor con campos JSONB)
- M4. Gestión de Medios (MediaLibrary, MediaSelector modal)
- M5. Rutas y API (JSON responses, React Router)
- M6. Limpieza Legacy JS (spa.js, tabs.js, editor.js, medios.js, selectorMedios.js eliminados)

---

### FASE 1: Infraestructura del Panel Admin ✅
**Logros:** Layout, rutas `/admin`, sistema CSS, autenticación (login/logout/middleware)

### FASE 2: Gestión de Contenidos ✅
**Logros:** CRUD completo, editor con panel lateral, papelera soft-delete, imagen destacada

**Pendiente menor:** Galería de imágenes adjuntas

### FASE 3: Sistema de Post Types ✅
**Logros:** PostTypeRegistry híbrido (código + BD), sidebar dinámico, rutas comodín

**Pendiente opcional:** UI de gestión, campos personalizados drag & drop

### FASE 4: Sistema de Medios ✅
**Logros:** Librería con grilla/lista, upload drag & drop, selector modal, metadatos

**Pendiente menor:** Barra de progreso, validación tipos/tamaños, autor, contenidos adjuntos

### REVISIÓN PRE-FASE 5: Refactorización PHP ✅
**Logros:**
- R1. `media/index.php` (523 → 205 líneas)
- R2. `Admin/ContentController.php` (544 → ~300 líneas) + `ContentService.php`
- R3. `contents/editor.php` (456 → 230 líneas)
- R4. `ContentController.php` API evaluado como estructuralmente correcto
- R5. `medios.css` dividido en `mediosGrilla.css` + `mediosDetalles.css`
- R6. Componentes PHP reutilizables (formularios, UI base, estructura)

---

## 🔄 PRÓXIMAS FASES

---

### REVISIÓN REACT: Refactorización de Componentes
**Estado:** [x] Completado  
**Prioridad:** Alta (bloqueante para FASE 5)

#### Objetivo
Revisar y refactorizar los componentes React existentes para asegurar calidad, mantenibilidad y cumplimiento de principios SOLID antes de continuar con nuevas funcionalidades.

#### Tareas Completadas

- [x] **RC1. Eliminación de Estilos Inline**
  - Dashboard.tsx: 4 estilos inline → clases CSS (grillaDashboard, grupoInfoSistema, etc.)
  - Modal.tsx: z-index inline → CSS
  - MediaSelector.tsx: flex container → contenedorFlexModal
  - Editor.tsx: display:none → clase .oculto

- [x] **RC2. Refactorización de Editor.tsx**
  - De 371 líneas → ~140 líneas
  - De 15 useState → 1 useState local + hooks
  - Creados hooks:
    - `useEditorForm` (manejo de formulario, guardado, metadatos)
    - `useContentFetch` (fetch de contenido)
  - Creados componentes:
    - `MetadataEditor` (UI de metadatos)
    - `EditorSidebar` (panel lateral)

- [x] **RC3. Refactorización de MediaLibrary.tsx**
  - De 294 líneas → ~170 líneas
  - De 10 useState → 3 useState locales + hooks
  - Creados hooks:
    - `useMediaFetch` (fetch con filtros/paginación)
    - `useFileUpload` (drag & drop + upload)
  - Creado componente:
    - `MediaDetailsPanel` (panel de detalles)

- [x] **RC4. Correcciones Adicionales**
  - Contents.tsx: Corregida ruta de enlace (/${item.type} → /admin/${item.type})
  - Todas las clases CSS en archivos centralizados

#### Estructura de Hooks Creada
```
app/view/admin/ui/hooks/
├── index.ts
├── useEditorForm.ts
├── useContentFetch.ts
├── useMediaFetch.ts
└── useFileUpload.ts
```

#### Componentes Extraídos
```
app/view/admin/ui/components/
├── editor/
│   ├── index.ts
│   ├── MetadataEditor.tsx
│   └── EditorSidebar.tsx
└── media/
    ├── MediaLibrary.tsx (refactorizado)
    ├── MediaSelector.tsx
    └── MediaDetailsPanel.tsx (nuevo)
```

#### Entregables
- ✅ Componentes React refactorizados y documentados
- ✅ Hooks personalizados para lógica reutilizable
- ✅ Código TypeScript sin estilos inline
- ✅ Panel admin React 100% funcional

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

- [ ] **5.5 Funciones de tema (Template Tags)** (DEBEN SER CAMELCASE; ESPAÑOL)
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

> **Próximo paso:** Iniciar Fase 5 - Sistema de Temas y Plantillas (Frontend)
