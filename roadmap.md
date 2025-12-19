# SwordPHP CMS - Roadmap de Desarrollo

> **Objetivo:** Transformar SwordPHP de un CMS headless a un CMS completo que compita con WordPress, manteniendo la filosofía de simplicidad y rendimiento.

---

## Decisiones de Arquitectura

| Aspecto                  | Decisión                                               |
| ------------------------ | ------------------------------------------------------ |
| **Panel Admin**          | **React + TypeScript (SPA)** servido por PHP           |
| **Build System**         | **Vite** (Salida estática a `public/admin/build`)      |
| **Motor de Plantillas**  | PHP para layout inicial + React para interactividad    |
| **Base de Datos**        | PostgreSQL con JSONB (existente)                       |
| **Autenticación**        | JWT / Session cookie (Híbrido)                         |
| **Estilos Admin**        | **CSS Nativo** (Reutilización 100% de clases actuales) |
| **Modo de Ejecución**    | **Híbrido** (Webman persistente + CGI tradicional)     |
| **Modos de Renderizado** | **3 modos:** PHP puro, SSG (estático), SSR (dinámico)  |

### Arquitectura Híbrida de Ejecución

> **Filosofía:** SwordPHP debe funcionar en cualquier hosting, desde shared hosting básico hasta infraestructura cloud moderna.

| Modo       | Cómo Funciona                                    | Compatibilidad                     |
| ---------- | ------------------------------------------------ | ---------------------------------- |
| **Webman** | Proceso PHP persistente, alto rendimiento        | VPS, Cloud, Docker                 |
| **CGI**    | Apache/Nginx ejecuta PHP por request (WordPress) | Shared hosting, cPanel, cualquiera |

El CMS detecta automáticamente el entorno y usa el modo apropiado.

#### Restricción: Modo Headless vs CMS Completo

| Modo de Uso        | Ejecución Requerida | Razón                                              |
| ------------------ | ------------------- | -------------------------------------------------- |
| **Headless (API)** | Webman obligatorio  | Alto tráfico de requests JSON, rendimiento crítico |
| **CMS Completo**   | CGI o Webman        | Flexibilidad según hosting del usuario             |

> **Nota:** El modo CGI está diseñado exclusivamente para servir el frontend público (temas). Las APIs headless siempre deben correr en modo Webman para garantizar rendimiento óptimo.

### Modos de Renderizado de Temas

| Modo         | Descripción                                    | Ideal Para                            |
| ------------ | ---------------------------------------------- | ------------------------------------- |
| **PHP Puro** | Renderizado tradicional en cada request        | Cualquier hosting, contenido dinámico |
| **SSG**      | Genera HTML estático al publicar contenido     | Blogs, sitios con poco cambio         |
| **SSR**      | Node.js renderiza con datos de PHP (React/Vue) | Apps modernas, SPAs públicas          |

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

### FASE 4.5: Modo CGI Tradicional
**Duración estimada:** 1 semana  
**Estado:** [x] Completado  
**Prioridad:** 🔴 Crítica (bloqueante para FASE 5)

#### Objetivo
Hacer que SwordPHP funcione en hosting tradicional sin necesidad de proceso persistente, igual que WordPress.

#### Contexto
Actualmente SwordPHP requiere ejecutar `php windows.php` o `php start.php` para funcionar (Webman/Workerman). Esto impide su uso en shared hosting. Esta fase crea un modo CGI alternativo.

#### Tareas Completadas

- [x] **4.5.1 Crear `public/index.php`**
  - Punto de entrada tradicional para Apache/Nginx
  - Detecta que NO está en modo Webman
  - Inicializa la aplicación en modo CGI
  - Sirve archivos estáticos directamente

- [x] **4.5.2 Router CGI**
  - Clase `app\support\CgiRouter` con soporte para parámetros regex
  - Registra rutas manualmente (admin y API)
  - Compatible con el sistema de middleware existente
  - Pipeline de middlewares funcional

- [x] **4.5.3 Abstracción Request/Response**
  - `CgiRequest`: Wrapper sobre `$_GET`, `$_POST`, `$_SERVER`, `$_FILES`
  - `CgiResponse`: Envío de respuestas con headers, cookies, redirecciones
  - `CgiSession`: Sesiones nativas de PHP con interfaz compatible
  - Conversión automática desde Response de Webman

- [x] **4.5.4 Detección Automática de Entorno**
  - Clase `Environment` con métodos `esWebman()` / `esCgi()`
  - Helpers globales `esWebman()` y `esCgi()`
  - Selección automática del modo apropiado

- [x] **4.5.5 Configuración de Servidor Web**
  - `.htaccess` raíz (redirección a public/)
  - `public/.htaccess` (front controller)
  - `nginx.conf.example` completo con SSL
  - Documentación `docs/CGI_MODE.md`

- [ ] **4.5.6 Verificación de Compatibilidad**
  - Pendiente: Probar todas las rutas del admin en modo CGI
  - Pendiente: Probar API en modo CGI
  - Pendiente: Documentar diferencias de rendimiento

#### Archivos Creados
```
app/support/
├── Environment.php         # Detección de entorno
├── CgiRequest.php          # Request wrapper
├── CgiResponse.php         # Response wrapper
├── CgiSession.php          # Session wrapper
├── CgiRouter.php           # Router CGI
├── CgiRouteShim.php        # Adaptador Webman\Route → CGI (MEJORA 4.5.7)
├── cgi_bootstrap.php       # Bootstrap CGI
└── cgi_helpers.php         # Helpers adicionales

app/middleware/
└── CgiAdminAuth.php        # Middleware admin CGI

public/
├── index.php               # Punto de entrada (v2.0 - unificado)
└── .htaccess               # Config Apache

.htaccess                   # Redirect a public/
nginx.conf.example          # Config Nginx
docs/CGI_MODE.md            # Documentación
```

#### Entregables
- ✅ SwordPHP funciona subiendo archivos a cualquier hosting PHP
- ✅ No requiere ejecutar comandos para iniciar
- ✅ Cambios en código se reflejan inmediatamente (sin reinicio)
- ✅ Compatible con cPanel, Plesk, y hostings básicos

---

### MEJORA 4.5.7: Unificación del Sistema de Rutas
**Estado:** [~] En Progreso (falta 4.5.7.4)  
**Prioridad:** 🟡 Alta (deuda técnica actual)

#### Problema Actual

Actualmente las rutas están **duplicadas** en dos lugares:

| Archivo                  | Usado por | Formato                                         |
| ------------------------ | --------- | ----------------------------------------------- |
| `config/route/admin.php` | Webman    | `Route::get('/path', [Controller, 'method'])`   |
| `config/route/api.php`   | Webman    | `Route::group()`, `Route::post()`, etc.         |
| `public/index.php`       | CGI       | `CgiRouter::agregarRuta('GET', '/path', [...])` |

**Problemas:**
- ❌ Dos fuentes de verdad para las mismas rutas
- ❌ Alto riesgo de inconsistencias
- ❌ Doble trabajo al añadir/modificar rutas
- ❌ Difícil de mantener

#### Solución Propuesta: Route Adapter

Crear un **adaptador** que intercepte las llamadas a `Webman\Route` en modo CGI y las registre en `CgiRouter`.

```
┌─────────────────────────────────────────────────────────┐
│                  config/route/*.php                     │
│       (ÚNICA fuente de verdad para rutas)               │
│                                                         │
│   Route::get('/admin', [DashboardController, 'index'])  │
│   Route::group('/api', function() { ... })              │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
         ┌──────────────────────┐
         │   ¿Modo Webman?      │
         └──────────┬───────────┘
                    │
          ┌─────────┴─────────┐
          │                   │
          ▼                   ▼
   ┌──────────────┐    ┌──────────────┐
   │ Webman\Route │    │ CgiRouteShim │
   │  (original)  │    │  (adaptador) │
   └──────────────┘    └──────┬───────┘
                              │
                              ▼
                       ┌──────────────┐
                       │  CgiRouter   │
                       └──────────────┘
```

#### Implementación Detallada

**Archivo: `app/support/CgiRouteShim.php`**

```php
namespace app\support;

/**
 * Adaptador que simula la API de Webman\Route para modo CGI.
 * Todas las llamadas se redirigen a CgiRouter.
 */
class CgiRouteShim
{
    protected static string $prefixActual = '';
    protected static array $middlewareActual = [];

    public static function get(string $path, $handler): self
    {
        return self::agregarRuta('GET', $path, $handler);
    }

    public static function post(string $path, $handler): self
    {
        return self::agregarRuta('POST', $path, $handler);
    }

    public static function put(string $path, $handler): self
    {
        return self::agregarRuta('PUT', $path, $handler);
    }

    public static function delete(string $path, $handler): self
    {
        return self::agregarRuta('DELETE', $path, $handler);
    }

    public static function group($prefix, $callback = null): self
    {
        // Manejar group('/prefix', callback) y group(callback)
        if (is_callable($prefix)) {
            $callback = $prefix;
            $prefix = '';
        }

        $prefixAnterior = self::$prefixActual;
        self::$prefixActual .= $prefix;

        $callback();

        self::$prefixActual = $prefixAnterior;

        return new self();
    }

    public function middleware($middleware): self
    {
        // Registrar middleware para las rutas del grupo
        return $this;
    }

    protected static function agregarRuta(string $metodo, string $path, $handler): self
    {
        $rutaCompleta = self::$prefixActual . $path;
        CgiRouter::agregarRuta($metodo, $rutaCompleta, $handler, self::$middlewareActual);
        return new self();
    }
}
```

**Modificación: `public/index.php`**

```php
// ANTES (duplicación de rutas)
registrarRutasAdmin();
registrarRutasApi();

// DESPUÉS (una sola fuente)
if (!class_exists('Webman\\Route')) {
    class_alias('app\\support\\CgiRouteShim', 'Webman\\Route');
}
require BASE_PATH . '/config/route/admin.php';
require BASE_PATH . '/config/route/api.php';
```

#### Tareas

- [x] **4.5.7.1 Crear CgiRouteShim**
  - Implementar todos los métodos de `Webman\Route`
  - `get()`, `post()`, `put()`, `delete()`, `patch()`, `any()`
  - `group()` con soporte para prefijos anidados
  - `middleware()` para registrar middlewares

- [x] **4.5.7.2 Adaptar manejo de middlewares**
  - Mapear middlewares de Webman a versiones CGI
  - Ejemplo: `AdminAuth` → `CgiAdminAuth`
  - Crear registro de aliases de middlewares

- [x] **4.5.7.3 Modificar public/index.php**
  - Eliminar funciones `registrarRutasAdmin()` y `registrarRutasApi()`
  - Usar autoloader interceptor para inyectar el shim antes de Composer
  - Cargar directamente `config/route/*.php`
  - Reducción: 508 → 179 líneas (~330 líneas eliminadas)

- [~] **4.5.7.4 Probar compatibilidad**
  - ✅ Interceptación de `Webman\Route` → `CgiRouteShim` funcionando
  - ✅ Interceptación de `support\Request` → `CgiRequest` funcionando
  - ✅ Rutas CGI se cargan desde `config/route/*.php`
  - ✅ Base de datos Eloquent inicializada en bootstrap CGI
  - ✅ Vistas PHP de login creadas (`layouts/auth.php`, `pages/login.php`)
  - ✅ Login de admin funcional en modo CGI
  - ✅ Carga de panel React en modo CGI + Vite Dev ("Hot Reload")
  - ✅ Solucionado conflicto de rutas: `public/admin` → `public/static-admin`
  - ✅ Solucionado: Fetching de contenidos en React daba 404 (error por sidebar generando links numéricos)
  - ✅ Solucionado: Nombres de items en Sidebar faltantes (error en estructura de datos window.sword)
  - ✅ Verificado: Webman sigue funcionando sin cambios

#### Archivos Creados/Modificados en 4.5.7.4

```
app/view/admin/
├── layouts/
│   ├── auth.php       # Layout de autenticación (CSS inline)
│   └── layout.php     # Layout principal que carga React (Fixed: estructura de menú)
└── pages/
    ├── login.php      # Formulario de login
    └── dashboard.php  # Placeholder para React

app/support/
├── cgi_bootstrap.php  # Añadida inicialización de Database
└── CgiRequest.php     # Ahora es clase independiente (sin extends)

public/index.php       # Intercepta support\Request → CgiRequest
```

#### Beneficios

| Antes                  | Después                   |
| ---------------------- | ------------------------- |
| 2 archivos de rutas    | 1 archivo de rutas        |
| ~200 líneas duplicadas | 0 líneas duplicadas       |
| Propenso a errores     | Una sola fuente de verdad |
| Difícil de mantener    | Fácil de mantener         |

#### Consideraciones

1.  **Middlewares**: Los middlewares de Webman usan `Webman\Http\Request`. Necesitamos:
    - Opción A: Crear versiones CGI de cada middleware
    - Opción B: Adaptar `CgiRequest` para que pase el duck typing
    - **Recomendación**: Opción B (menos código, más DRY)

2.  **Closures en rutas**: Algunas rutas usan closures que esperan `$request`. Debemos asegurar que `CgiRequest` sea compatible.

3.  **class_alias timing**: El alias debe crearse ANTES de que PHP parsee los archivos de rutas.

---

### FASE 5: Sistema de Temas y Plantillas
**Duración estimada:** 2-3 semanas  
**Estado:** [ ] Pendiente

#### Objetivo
Renderizar páginas públicas con temas intercambiables, soportando 3 modos de renderizado para máxima compatibilidad.

#### Modos de Renderizado

| Modo         | `theme.json` mode | Requisitos del Hosting         |
| ------------ | ----------------- | ------------------------------ |
| **PHP Puro** | `"mode": "php"`   | Solo PHP (cualquier hosting)   |
| **SSG**      | `"mode": "ssg"`   | PHP + capacidad de build       |
| **SSR**      | `"mode": "ssr"`   | PHP + Node.js en segundo plano |

#### Tareas

- [ ] **5.1 Estructura de un tema**
  - Definir estructura de carpetas
  - Archivo `theme.json` con metadatos y modo:
    ```json
    {
        "name": "Developer Theme",
        "version": "1.0.0",
        "author": "SwordPHP",
        "description": "Tema minimalista para desarrolladores",
        "screenshot": "screenshot.png",
        "mode": "php",
        "buildCommand": null,
        "ssrPort": null
    }
    ```
  - Archivo `functions.php` para funciones del tema

- [ ] **5.2 Motor de renderizado PHP (Modo PHP Puro)**
  - Clase `ThemeEngine` para cargar y renderizar plantillas
  - Sistema de variables disponibles en plantillas
  - Funciones helper: `obtenerCabecera()`, `obtenerPie()`, `elTitulo()`, etc.
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

- [ ] **5.5 Funciones de tema (Template Tags)** (camelCase, español)
  - `obtenerCabecera()` / `obtenerPie()`
  - `elTitulo()` / `obtenerTitulo()`
  - `elContenido()` / `obtenerContenido()`
  - `elEnlace()` / `obtenerEnlace()`
  - `laImagen()` / `obtenerUrlImagen()`
  - `obtenerPosts()` - Obtener lista de posts
  - `obtenerOpcion()` - Ya existe como `get_option()`
  - `urlSitio()` / `urlInicio()`
  - `urlAsset()` - URL de assets del tema

- [ ] **5.6 Motor SSG (Static Site Generation)**
  - Comando `php webman theme:build`
  - Genera HTML estático en `public/static/`
  - Hook post-publicación que regenera páginas afectadas
  - Ideal para blogs y sitios con contenido estable

- [ ] **5.7 Motor SSR (Server Side Rendering)**
  - Integración con Node.js (React/Vue/Svelte)
  - PHP pasa datos JSON al servidor Node
  - Node renderiza y devuelve HTML
  - Configuración de proxy en `theme.json`

- [ ] **5.8 Panel de temas**
  - Página de listado de temas instalados
  - Preview de tema
  - Activar tema
  - Indicador de modo y compatibilidad del hosting

- [ ] **5.9 Temas de demostración (3 temas base)**
  - `developer` - Modo PHP puro, minimalista
  - `developer-ssg` - Modo SSG, genera estáticos
  - `developer-ssr` - Modo SSR con React

#### Estructura de Carpetas por Modo

```
themes/
├── developer/              # Modo PHP Puro
│   ├── templates/
│   │   ├── index.php
│   │   ├── single.php
│   │   └── ...
│   ├── assets/
│   ├── functions.php
│   └── theme.json          → { "mode": "php" }
│
├── developer-ssg/          # Modo SSG
│   ├── templates/
│   ├── build.js            # Script de generación
│   ├── dist/               # HTML generado
│   └── theme.json          → { "mode": "ssg", "buildCommand": "node build.js" }
│
└── developer-ssr/          # Modo SSR
    ├── src/                # Código React/Vue
    ├── server.js           # Servidor Node SSR
    └── theme.json          → { "mode": "ssr", "ssrPort": 3000 }
```

#### Entregables
- URLs públicas renderizan HTML completo
- Sistema de temas intercambiables con 3 modos
- 3 temas de demostración funcionales
- Documentación de cómo crear temas en cada modo

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
1.  Extrae las variables `$data`.
2.  Incluye el archivo de vista `app/view/$template.php`.
3.  Retorna el string HTML capturado.


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

| Fecha      | Versión | Cambios                                                     |
| ---------- | ------- | ----------------------------------------------------------- |
| 2024-12-14 | 0.1.0   | Documento inicial del roadmap                               |
| 2024-12-18 | 0.2.0   | FASE 4.5 Modo CGI Tradicional completado                    |
| 2024-12-18 | 0.2.1   | 4.5.7.4 Pruebas CGI: login funcional, layout React en curso |
| 2024-12-18 | 0.2.2   | Fix critical: React 404s, Sidebar items y estructura JSON   |

---

> **Próximo paso:** Completar verificación del modo CGI (corregir layout React, probar panel completo), luego iniciar Fase 5 - Sistema de Temas y Plantillas (Frontend)

# Notas del usuario (No borrar)

Con este comando corro el cgi 'php -S localhost:8080 -t public'
y con este el deamon php windows.php, los corro al mismo tiempo para chequear que todo sea consistente.
