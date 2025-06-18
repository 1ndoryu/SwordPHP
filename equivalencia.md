# Equivalencias de Funciones: SwordPHP vs. WordPress

Este documento sirve como una hoja de referencia rápida para desarrolladores familiarizados con WordPress, detallando las funciones y conceptos equivalentes en SwordPHP en un formato de tablas comparativas. Tambien hay que hacer un sistema para que si se usa wp_enqueue_style por ejemplo, tambien funcione todas la funciones equivalentes de wordpress, se intentara que los temas sean compatibles.

---

## ✅ Equivalencias Directas y Conceptuales

### Tabla 1: Manejo de Assets (CSS/JS)

| Función / Método SwordPHP              | Equivalente WordPress                 |
| :------------------------------------- | :------------------------------------ |
| `encolarEstilo('id', 'ruta')`          | `wp_enqueue_style('handle', 'src')`   |
| `encolarScript('id', 'ruta')`          | `wp_enqueue_script('handle', 'src')`  |
| `assetService()->localizarScript(...)` | `wp_localize_script(...)`             |
| `rutaTema('path/recurso.css')`         | `get_theme_file_uri('path/file.css')` |

### Tabla 2: Manejo de AJAX

| Función SwordPHP            | Equivalente WordPress               |
| :-------------------------- | :---------------------------------- |
| `ajaxAccion('nombre', $cb)` | `add_action('wp_ajax_nombre', $cb)` |

### Tabla 3: Hooks (Acciones y Filtros)

| Función SwordPHP                          | Equivalente WordPress                   |
| :---------------------------------------- | :-------------------------------------- |
| `agregarAccion('hook', $cb, $p, $a)`      | `add_action('hook', $cb, $p, $a)`       |
| `hacerAccion('hook', ...$args)`           | `do_action('hook', ...$args)`           |
| `agregarFiltro('hook', $cb, $p, $a)`      | `add_filter('hook', $cb, $p, $a)`       |
| `aplicarFiltro('hook', $valor, ...$args)` | `apply_filters('hook', $val, ...$args)` |

### Tabla 4: Datos de Usuario

| Función / Lógica SwordPHP   | Equivalente WordPress   |
| :-------------------------- | :---------------------- |
| `usuarioActual()`           | `wp_get_current_user()` |
| `idUsuarioActual()`         | `get_current_user_id()` |
| `!is_null(usuarioActual())` | `is_user_logged_in()`   |

### Tabla 5: Metadatos de Usuario (User Meta)

| Función / Lógica SwordPHP | Equivalente WordPress                    |
| :------------------------ | :--------------------------------------- |
| `obtenerMetaUsuario()`    | `get_user_meta()`                        |
| `guardarMetaUser()`       | `update_user_meta()` / `add_user_meta()` |
| `eliminarMetaUser()`      | `delete_user_meta()`                     |

### Tabla 6: Metadatos de Contenido (Post Meta) #Esto hay que ajustarlo para que sean funciones globales y no metodos.

| Método SwordPHP (`$pagina->...`)  | Equivalente WordPress                |
| :-------------------------------- | :----------------------------------- |
| `->guardarMeta('clave', 'valor')` | `update_post_meta($post_id, ...)`    |
| `->obtenerMeta('clave')`          | `get_post_meta($post_id, ..., true)` |
| `->eliminarMeta('clave')`         | `delete_post_meta($post_id, ...)`    |

### Tabla 7: Opciones del Sitio #Esto hay que ajustarlo para que sean funciones globales y no metodos.

| Método SwordPHP (`$opcionService->...`) | Equivalente WordPress            |
| :-------------------------------------- | :------------------------------- |
| `->guardarOpcion('nombre', 'valor')`    | `update_option('name', 'value')` |
| `->obtenerOpcion('nombre')`             | `get_option('name')`             |

### Tabla 8: El Loop y Plantillas (Theming)

| Función SwordPHP                           | Equivalente WordPress          |
| :----------------------------------------- | :----------------------------- |
| `new SwordQuery($args)`                    | `new WP_Query($args)`          |
| `hayEntradas()`                            | `have_posts()`                 |
| `laEntrada()`                              | `the_post()`                   |
| `elTitulo()`                               | `the_title()`                  |
| `elContenido()`                            | `the_content()`                |
| `elEnlacePermanente()`                     | `the_permalink()`              |
| `obtenerEnlacePermanente()`                | `get_permalink()` (en el loop) |
| `obtenerEnlacePermanenteEntrada($entrada)` | `get_permalink($post)`         |
| `getHeader()`                              | `get_header()`                 |
| `getFooter()`                              | `get_footer()`                 |

---

## ❌ Funcionalidades Faltantes

La siguiente tabla muestra funciones clave de WordPress que aún no tienen un equivalente global directo en SwordPHP.

# Identificadas, si falta, agregar

| Función SwordPHP | Equivalente WordPress           |
| :--------------- | :------------------------------ |
| (No existe)      | `get_the_author()`              |
| (No existe)      | `get_the_date()`                |
| (No existe)      | `the_excerpt()`                 |
| (No existe)      | `get_post_thumbnail_id()`       |
| (No existe)      | `wp_get_attachment_image_src()` |
| (No existe)      | `register_nav_menus()`          |
| (No existe)      | `wp_nav_menu()`                 |
