
```markdown
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

* **Rendimiento Extremo:** Construido sobre [Webman](https://www.workerman.net/webman), un framework de alto rendimiento que mantiene la aplicación en memoria para reducir latencias.
* **Separación de Código y Contenido:** Una estricta separación entre el núcleo del sistema (`swordCore`) y el contenido del usuario (`swordContent`), incluyendo temas, plugins y archivos multimedia.
* **Facilidad para Desarrolladores de Temas:** Los desarrolladores de temas pueden añadir lógica y funcionalidades usando un archivo `functions.php` y helpers globales, sin necesidad de entender la arquitectura interna del núcleo.
* **Sin Frameworks CSS/JS Opinados:** El núcleo del panel de administración no depende de frameworks como Bootstrap o Tailwind, ofreciendo un lienzo limpio y ligero.
* **Instalación Sencilla:** Inspirado en WordPress, la instalación se basa en la creación de tablas iniciales mediante un script, en lugar de un sistema de migraciones complejo.
* **Modularidad:** La arquitectura está diseñada para ser extendida a través de un futuro sistema de plugins, manteniendo el núcleo lo más ligero posible.

---

## 🚀 Pila Tecnológica

* **Framework Principal:** [Webman](https://www.workerman.net/webman)
* **ORM de Base de Datos:** [Illuminate Database (Eloquent)](https://laravel.com/docs/11.x/eloquent)
* **Motor de Plantillas:** PHP Nativo (para máxima velocidad y flexibilidad)
* **Gestor de Dependencias:** [Composer](https://getcomposer.org/)

---

## ✨ Características Actuales

* **Núcleo del Sistema:** Basado en Webman para un alto rendimiento.
* **Autenticación:** Sistema completo de registro, inicio y cierre de sesión.
* **Panel de Administración:** Interfaz de administración segura con roles de usuario (admin, suscriptor).
* **Gestión de Contenidos (CRUD):**
    * **Páginas:** Creación, edición y eliminación de páginas.
    * **Tipos de Contenido Personalizados (Post Types):** Sistema para registrar y gestionar tipos de contenido genéricos (ej: proyectos, noticias).
    * **Metadatos:** Sistema de campos personalizados para páginas y usuarios, similar a `post_meta` y `user_meta`.
* **Gestor de Medios:** Subida de archivos con organización por fecha (`año/mes`) y gestión desde el panel.
* **Gestión de Usuarios (CRUD):** Creación, edición y eliminación de usuarios desde el panel.
* **Sistema de Temas:**
    * Arquitectura de temas que separa la lógica del núcleo.
    * Carga de `functions.php` del tema activo.
    * Helpers globales como `getHeader()`, `getFooter()` para la construcción de plantillas.
* **Gestor de Assets:** Funciones globales (`encolarEstilo`, `encolarScript`) para añadir CSS y JS desde los temas, de forma similar a WordPress.

---

## 📁 Estructura de Directorios

El proyecto se divide en dos directorios principales para garantizar una separación clara de responsabilidades.

```
/Sword/
├── 📂 swordContent/      # Contenido del usuario (similar a wp-content)
│   ├── 📂 media/          # Archivos multimedia subidos (organizados por fecha)
│   │   └── 📂 2025/06/
│   └── 📂 themes/         # Temas del sitio
│       └── 📂 sword-theme-default/
│           ├── functions.php
│           ├── pagina.php
│           └── ...
│
└── 📂 swordCore/        # El núcleo del CMS (basado en Webman)
    ├── 📂 app/            # Lógica de la aplicación (Controladores, Modelos, Servicios)
    │   ├── 📂 controller/
    │   ├── 📂 model/
    │   ├── 📂 service/
    │   ├── 📂 middleware/
    │   └── functions.php  # Helpers globales del núcleo
    ├── 📂 config/        # Ficheros de configuración
    ├── 📂 public/        # Punto de entrada y assets públicos del panel
    ├── 📂 vendor/          # Dependencias de Composer
    └── start.php        # Script de inicio
```

---

## 🆚 Equivalencias con WordPress

Para facilitar la transición a los desarrolladores de WordPress, Sword ofrece una serie de funciones y conceptos equivalentes.

### ✅ Equivalencias Directas y Conceptuales

| Función / Método Sword                 | Equivalente WordPress                 |
| :---------------------------------------- | :------------------------------------ |
| `encolarEstilo('id', 'ruta')`             | `wp_enqueue_style('handle', 'src')`   |
| `encolarScript('id', 'ruta')`             | `wp_enqueue_script('handle', 'src')`  |
| `assetService()->localizarScript(...)`    | `wp_localize_script(...)`             |
| `rutaTema('path/recurso.css')`            | `get_theme_file_uri('path/file.css')` |
| `ajaxAccion('nombre', $cb)`               | `add_action('wp_ajax_nombre', $cb)`   |
| `usuarioActual()`                         | `wp_get_current_user()`               |
| `idUsuarioActual()`                       | `get_current_user_id()`               |
| `!is_null(usuarioActual())`               | `is_user_logged_in()`                 |
| `obtenerMetaUser()`                       | `get_user_meta()`                     |
| `guardarMetaUser()`                       | `update_user_meta()` / `add_user_meta()` |
| `eliminarMetaUser()`                      | `delete_user_meta()`                  |
| `$pagina->guardarMeta('clave', 'valor')`  | `update_post_meta($post_id, ...)`     |
| `$pagina->obtenerMeta('clave')`          | `get_post_meta($post_id, ..., true)`  |
| `$pagina->eliminarMeta('clave')`         | `delete_post_meta($post_id, ...)`     |
| `$opcionService->guardarOpcion(...)`      | `update_option('name', 'value')`      |
| `$opcionService->obtenerOpcion(...)`      | `get_option('name')`                  |
| `getHeader()`                             | `get_header()`                        |
| `getFooter()`                             | `get_footer()`                        |

### ❌ Funcionalidades Faltantes (Próximamente)

| Función Sword | Equivalente WordPress |
| :--------------- | :-------------------- |
| `(No existe)`    | `add_action()`        |
| `(No existe)`    | `do_action()`         |
| `(No existe)`    | `add_filter()`        |
| `(No existe)`    | `apply_filters()`     |
| `(No existe)`    | `get_permalink()`     |
| `(No existe)`    | `the_title()`         |
| `(No existe)`    | `the_content()`       |
| `(No existe)`    | `have_posts()`        |
| `(No existe)`    | `the_post()`          |
| `(No existe)`    | `new WP_Query()`      |

---

## 🛠️ Instalación y Puesta en Marcha (Local)

1.  **Clonar el repositorio:**
    ```bash
    git clone [https://github.com/1ndoryu/SwordPHP](https://github.com/1ndoryu/SwordPHP)
    cd Sword
    ```

2.  **Instalar dependencias de Composer:**
    ```bash
    # Navega al directorio del núcleo
    cd swordCore

    # Instala las dependencias
    composer install
    ```

3.  **Configurar el entorno:**
    * Copia el archivo `.env.example` a `.env` dentro de `swordCore`.
    * Ajusta las credenciales de tu base de datos (PostgreSQL) en el archivo `.env`.
    ```
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=sword
    DB_USERNAME=postgres
    DB_PASSWORD=tu_clave
    ```

4.  **Crear la base de datos:** Asegúrate de crear una base de datos con el nombre que especificaste en el archivo `.env`.

5.  **Ejecutar el script de instalación (Próximamente):** Se creará un script para generar las tablas iniciales del sistema.

6.  **Iniciar el servidor:**
    ```bash
    # Desde el directorio swordCore
    php start.php start
    ```
    También puedes usar `windows.bat` si estás en Windows. El servidor estará disponible en `http://127.0.0.1:8787`.

---

## 🗺️ Hoja de Ruta (Roadmap)

-   [x] **Fase 1: Fundación y Autenticación**
-   [x] **Fase 2: Estructura del Panel de Administración**
-   [x] **Fase 3: Gestión de Assets y AJAX**
-   [x] **Fase 4: Gestor de Páginas (CRUD)**
-   [x] **Fase 5: Sistema de Ruteo y Temas (Frontend)**
-   [x] **Fase 6: Mejoras y Extensibilidad** (CRUD de Usuarios, Gestor de Medios, Metadatos de Usuario).
-   [x] **Fase 7: Gestor de Contenido Avanzado (Tipos de Contenido)**
-   [ ] **Fase 8: Gestión de Temas** - Desarrollar la gestión completa de temas desde el panel (ver, activar, eliminar).
-   [ ] **Fase 9: Sistema de Plantillas** - Permitir seleccionar plantillas de página desde el editor (similar a las plantillas de página de WordPress).
-   [ ] **Fase 10: Mejoras en Formularios** - Permitir edición de slugs, validación de slugs únicos, etc.
-   [ ] **Fase 11: Arquitectura de Plugins** - Diseñar e implementar el sistema de plugins. *Tarea a largo plazo.*

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
```