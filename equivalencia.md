# Equivalencias de Funciones: SwordPHP vs. WordPress

Este documento sirve como una hoja de referencia rápida para desarrolladores familiarizados con WordPress, detallando las funciones y conceptos equivalentes en SwordPHP en un formato de tablas comparativas. Tambien hay que hacer un sistema para que si se usa wp_enqueue_style por ejemplo, tambien funcione todas la funciones equivalentes de wordpress, se intentara que los temas sean compatibles.

---

## ✅ Equivalencias Directas y Conceptuales

### Tabla 1: Manejo de Assets (CSS/JS)

| Función / Método SwordPHP              | Equivalente WordPress                 |
| :------------------------------------- | :------------------------------------ |
| `encolarEstilo('id', 'ruta')`          | `wp_enqueue_style('handle', 'src')`   |
| `encolarScript('id', 'ruta')`          | `wp_enqueue_script('handle', 'src')`  |
| `assetService()->localizarScript(...)` | `wp_localize_script(...)`             |
| `rutaTema('path/recurso.css')`         | `get_theme_file_uri('path/file.css')` |

### Tabla 2: Manejo de AJAX

| Función SwordPHP            | Equivalente WordPress               |
| :-------------------------- | :---------------------------------- |
| `ajaxAccion('nombre', $cb)` | `add_action('wp_ajax_nombre', $cb)` |

### Tabla 3: Datos de Usuario

| Función / Lógica SwordPHP   | Equivalente WordPress   |
| :-------------------------- | :---------------------- |
| `usuarioActual()`           | `wp_get_current_user()` |
| `idUsuarioActual()`         | `get_current_user_id()` |
| `!is_null(usuarioActual())` | `is_user_logged_in()`   |

### Tabla 4: Metadatos de Usuario (User Meta)

| Función / Lógica SwordPHP | Equivalente WordPress                    |
| :------------------------ | :--------------------------------------- |
| `obtenerMetaUser()`       | `get_user_meta()`                        |
| `guardarMetaUser()`       | `update_user_meta()` / `add_user_meta()` |
| `eliminarMetaUser()`      | `delete_user_meta()`                     |

### Tabla 5: Metadatos de Páginas (Post Meta)

| Método SwordPHP (`$pagina->...`)  | Equivalente WordPress                |
| :-------------------------------- | :----------------------------------- |
| `->guardarMeta('clave', 'valor')` | `update_post_meta($post_id, ...)`    |
| `->obtenerMeta('clave')`          | `get_post_meta($post_id, ..., true)` |
| `->eliminarMeta('clave')`         | `delete_post_meta($post_id, ...)`    |

### Tabla 6: Opciones del Sitio

| Método SwordPHP (`$opcionService->...`) | Equivalente WordPress            |
| :-------------------------------------- | :------------------------------- |
| `->guardarOpcion('nombre', 'valor')`    | `update_option('name', 'value')` |
| `->obtenerOpcion('nombre')`             | `get_option('name')`             |

### Tabla 7: Plantillas (Theming)

| Función SwordPHP | Equivalente WordPress |
| :--------------- | :-------------------- |
| `getHeader()`    | `get_header()`        |
| `getFooter()`    | `get_footer()`        |

---

## ❌ Funcionalidades Faltantes

La siguiente tabla muestra funciones clave de WordPress que aún no tienen un equivalente global directo en SwordPHP.

# Identificadas, si falta, agregar

| Función SwordPHP | Equivalente WordPress |
| :--------------- | :-------------------- |
| `(No existe)`    | `add_action()`        |
| `(No existe)`    | `do_action()`         |
| `(No existe)`    | `add_filter()`        |
| `(No existe)`    | `apply_filters()`     |
| `(No existe)`    | `get_permalink()`     |
| `(No existe)`    | `the_title()`         |
| `(No existe)`    | `the_content()`       |
| `(No existe)`    | `have_posts()`        |
| `(No existe)`    | `the_post()`          |
| `(No existe)`    | `new WP_Query()`      |