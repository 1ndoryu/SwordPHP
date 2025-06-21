# Sword ⚔️ - Un CMS Minimalista y Rápido basado en PHP

![Project Status](https://img.shields.io/badge/status-alpha-red.svg)
![PHP Version](https://img.shields.io/badge/php-%3E=8.0-8892BF.svg)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

**Sword** es un sistema de gestión de contenidos (CMS) y framework de desarrollo en fase **alpha**, diseñado desde cero para ser una alternativa a WordPress. Su enfoque principal es ser **minimalista, modular, increíblemente rápido** y seguir las mejores prácticas de desarrollo para ser fácilmente mantenible y escalable.

---

## ⚠️ Estado del Proyecto: Versión Alpha

**¡Atención!** Sword se encuentra en una fase muy temprana de desarrollo. **No es apto para entornos de producción.** Muchas funcionalidades están en progreso, y la estructura del código está sujeta a cambios constantes sin previo aviso.

Este repositorio es ideal para desarrolladores interesados en contribuir, seguir el progreso del proyecto o experimentar en un entorno local.

---

## 📜 Filosofía y Principios

El objetivo de Sword es combinar la simplicidad y extensibilidad que los desarrolladores aman de WordPress con las prácticas modernas de desarrollo de PHP.

-   **Rendimiento Extremo:** Construido sobre [Webman](https://www.workerman.net/webman), un framework de alto rendimiento que mantiene la aplicación en memoria para reducir latencias.
-   **Separación de Código y Contenido:** Una estricta separación entre el núcleo del sistema (`swordCore`) y el contenido del usuario (`swordContent`), incluyendo temas, plugins y archivos multimedia.
-   **Facilidad para Desarrolladores de Temas:** Los desarrolladores de temas pueden añadir lógica y funcionalidades usando un archivo `functions.php` y helpers globales, sin necesidad de entender la arquitectura interna del núcleo.
-   **Sin Frameworks CSS/JS Opinados:** El núcleo del panel de administración no depende de frameworks como Bootstrap o Tailwind, ofreciendo un lienzo limpio y ligero.
-   **Instalación Sencilla:** Inspirado en WordPress, la instalación se realiza a través de un intuitivo instalador web que configura la base de datos y los ajustes iniciales del sitio.
-   **Modularidad:** La arquitectura está diseñada para ser extendida a través de un sistema de plugins y hooks (acciones y filtros), manteniendo el núcleo lo más ligero posible.

---

## 🚀 Pila Tecnológica

-   **Basado principalmente en:** [Webman](https://www.workerman.net/webman)
-   **ORM de Base de Datos:** [Illuminate Database (Eloquent)](https://laravel.com/docs/11.x/eloquent)
-   **Motor de Plantillas:** PHP Nativo (para máxima velocidad y flexibilidad)
-   **Gestor de Dependencias:** [Composer](https://getcomposer.org/)

---

## ✨ Características Actuales

-   **Instalador Web:** Proceso de instalación guiado desde el navegador para configurar la base de datos y el sitio por primera vez.
-   **Núcleo del Sistema:** Basado en Webman para un alto rendimiento.
-   **Autenticación:** Sistema completo de registro, inicio y cierre de sesión.
-   **Panel de Administración:** Interfaz de administración segura con roles de usuario (admin, suscriptor) y widgets en el dashboard.
-   **Gestión de Contenidos (CRUD):**
    -   **Páginas:** Creación, edición y eliminación de páginas.
    -   **Tipos de Contenido Personalizados (Post Types):** Sistema para registrar y gestionar tipos de contenido genéricos (ej: proyectos, noticias).
    -   **Metadatos:** Sistema de campos personalizados para páginas y usuarios, similar a `post_meta` y `user_meta`.
-   **Gestor de Medios:** Subida de archivos con organización por fecha (`año/mes`) y gestión desde el panel.
-   **Gestión de Usuarios (CRUD):** Creación, edición y eliminación de usuarios desde el panel.
-   **Sistema de Temas:**
    -   Arquitectura de temas que separa la lógica del núcleo.
    -   Carga de `functions.php` y plantillas de página (`Template Name: ...`) del tema activo.
    -   Helpers globales como `getHeader()`, `getFooter()` para la construcción de plantillas.
-   **Sistema de Plugins:**
    -   Activación y desactivación de plugins desde el panel.
    -   Los plugins pueden añadir menús y páginas de ajustes al panel de administración.
-   **Hooks (Acciones y Filtros):** Sistema completo para extender funcionalidades del núcleo y de otros plugins.
-   **Theming con "The Loop":** Funciones de plantilla globales (`havePost`, `thePost`, `theTitle`, `theContent`, etc.) para facilitar la creación de temas de forma similar a WordPress.
-   **Gestor de Assets:** Funciones globales (`encolarEstilo`, `encolarScript`) para añadir CSS y JS desde los temas y plugins.
-   **Sistema de Shortcodes:** API para registrar y procesar shortcodes en el contenido.
-   **Ajustes del Sitio:** Panel de control para configurar los ajustes generales (título, descripción), de lectura (página de inicio) y enlaces permanentes.

---

## 🆚 Equivalencias con WordPress

Para facilitar la transición a los desarrolladores de WordPress, Sword ofrece una serie de funciones y conceptos equivalentes.

### ✅ Equivalencias Directas y Conceptuales

| Función / Método Sword                   | Equivalente WordPress                     |
| :--------------------------------------- | :---------------------------------------- |
| `addAction('hook', $cb)`             | `add_action('hook', $cb)`                 |
| `doAction('hook', ...$args)`          | `do_action('hook', ...$args)`             |
| `addFilter('hook', $cb)`             | `add_filter('hook', $cb)`                 |
| `applyFilters('hook', $val, ...$args)`  | `apply_filters('hook', $val, ...$args)`  |
| `encolarEstilo('id', 'ruta')`            | `wp_enqueue_style('handle', 'src')`       |
| `encolarScript('id', 'ruta')`            | `wp_enqueue_script('handle', 'src')`      |
| `new SwordQuery($args)`                  | `new WP_Query($args)`                     |
| `havePost()`                          | `have_posts()`                            |
| `thePost()`                            | `the_post()`                              |
| `theTitle()`                             | `the_title()`                             |
| `theContent()`                          | `the_content()`                           |
| `getPermalink()`                   | `the_permalink()`                         |
| `getPermalinkPost($e)`     | `get_permalink($post)`                    |
| `getHeader()`                            | `get_header()`                            |
| `getFooter()`                            | `get_footer()`                            |
| `usuarioActual()`                        | `wp_get_current_user()`                   |
| `idUsuarioActual()`                      | `get_current_user_id()`                   |
| `guardarOpcion('nombre', 'valor')`       | `update_option('name', 'value')`          |
| `obtenerOpcion('nombre')`                | `get_option('name')`                      |

### ❌ Funcionalidades Faltantes (Próximamente)

| Función Sword | Equivalente WordPress           |
| :------------ | :------------------------------ |
| (No existe)   | `get_the_author()`              |
| (No existe)   | `get_the_date()`                |
| (No existe)   | `the_excerpt()`                 |
| (No existe)   | `get_post_thumbnail_id()`       |
| (No existe)   | `wp_get_attachment_image_src()` |
| (No existe)   | `register_nav_menus()`          |
| (No existe)   | `wp_nav_menu()`                 |

---

## 🛠️ Instalación y Puesta en Marcha (Local)

1.  **Clonar el repositorio:**
    ```bash
    git clone [https://github.com/1ndoryu/SwordPHP.git](https://github.com/1ndoryu/SwordPHP.git)
    cd SwordPHP
    ```

2.  **Instalar dependencias de Composer:**
    ```bash
    # Navega al directorio del núcleo
    cd swordCore

    # Instala las dependencias
    composer install
    ```

3.  **Preparar la Base de Datos:**
    -   Asegúrate de tener un servidor de base de datos PostgreSQL en funcionamiento.
    -   Crea una base de datos vacía para el proyecto (por ejemplo, `swordphp`).

4.  **Iniciar el servidor:**
    ```bash
    # Desde el directorio swordCore
    php start.php start
    ```
    También puedes usar `windows.bat` si estás en Windows. El servidor estará disponible en `http://127.0.0.1:8787`.

5.  **Ejecutar el Instalador Web:**
    -   Abre tu navegador y ve a `http://127.0.0.1:8787`.
    -   Serás redirigido automáticamente al instalador.
    -   Sigue los pasos para conectar la base de datos, configurar tu sitio y crear el usuario administrador.

---

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Si deseas ayudar, por favor:

1.  Haz un **Fork** de este repositorio.
2.  Crea una nueva **rama** para tu funcionalidad (`git checkout -b feature/nombre-funcionalidad`).
3.  Haz **Commit** de tus cambios (`git commit -am 'Añade nueva funcionalidad'`).
4.  Haz **Push** a la rama (`git push origin feature/nombre-funcionalidad`).
5.  Abre un nuevo **Pull Request**.

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Consulta el archivo `LICENSE` para más detalles.
