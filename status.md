# Hoja de Ruta del Proyecto SwordPHP

## Concepto
El objetivo es desarrollar una alternativa a WordPress que sea minimalista, modular, increíblemente rápida y que siga las mejores prácticas de desarrollo para ser fácilmente mantenible y escalable. Tiene que ser fácil para los desarrolladores de temas conservar la esencia de WordPress: poder añadir lógica de forma sencilla en los temas sin necesidad de comprender o modificar la arquitectura del núcleo. El desarrollador de temas debe poder enfocarse en construir su tema, facilitándole funciones globales para su desarrollo (sin estar atadas a `use /App` ni a espacios de nombres complejos).

---

## Principios Arquitectónicos
- **Instalación sin Migraciones:** No se utilizará un sistema de migraciones tradicional. Las tablas necesarias para el core del sistema (`usuarios`, `paginas`, `opciones`, etc.) deben ser creadas mediante un script de instalación inicial, similar a como lo hace WordPress.
- **Separación de Lógica y Presentación:** El núcleo (`Core`) debe estar completamente separado del contenido del usuario (`Content`), que incluye temas, plugins y archivos subidos.

---

## Fases del Proyecto

- [x] **Fase 1: Fundación y Autenticación**
    - Se configuró el framework y la base de datos, implementando un sistema robusto de registro, login/logout y un `UsuarioService` para manejar la lógica de negocio.

- [x] **Fase 2: Estructura del Panel de Administración**
    - Se implementó un panel de administración con rutas protegidas, un layout base, y un sistema de roles de usuario (admin/suscriptor) para controlar el acceso.

- [x] **Fase 3: Gestión de Assets y AJAX**
    - Se desarrolló un `AssetService` para encolar CSS/JS y un `AjaxManagerService` con una función global `registrar_accion_ajax()` para crear un sistema de acciones AJAX seguro, centralizado y fácil de usar desde cualquier parte del código.

- [x] **Fase 4: Gestor de Páginas (CRUD)**
    - Se implementó el CRUD completo para páginas, incluyendo un sistema de metadatos (`pagina_meta`) para añadir campos personalizados, sentando las bases para contenido extensible.

- [ ] **Fase 5: Sistema de Ruteo y Temas (Frontend)**
    - [x] Desarrollar un sistema de ruteo dinámico que muestra el contenido de las páginas publicadas en el frontend.
    - [x] **Refactorización Arquitectónica:** Separar la estructura de directorios en `swordCore` (el núcleo del CMS) y `swordContent` (temas, plugins, uploads) para reflejar la filosofía de WordPress. (lo que tenemos hasta ahora permanecerá en su sitio, es decir, el core tendra su propias rutas y plantillas, mientras que del lado del content sera como wp)
    - [x] Enrutamiento dinamico de paginas creadas.
    - [ ] Crear una estructura básica de "temas" que permita personalizar la apariencia. Cada tema debe poder tener su propio `functions.php`, plantillas de página, y assets (CSS/JS).
    - [ ] En panel se tiene que elegir una pagina de inicio, crearemos una por defecto, pero como en wp, se deberia poder elegir una pagina de inicio para los temas.

- [ ] **Fase 6: Mejoras y Extensibilidad**
    - [ ] Implementar un gestor de medios (`Media Library`) centralizado para subir y gestionar archivos, organizados por fecha.
    - [ ] Refinar el `AssetService` para que sea fácilmente utilizable desde el `functions.php` de los temas, permitiendo encolar scripts y estilos de forma sencilla.
    - [ ] Ampliar el sistema de metadatos para usuarios (`user_meta`).
    - [ ] Implementar un CRUD para la gestión de usuarios en el panel de administración.
    - [ ] Diseñar e implementar una arquitectura de **plugins** para permitir la extensibilidad del core sin modificarlo.

- [ ] **Fase 7: Gestor de Contenido Avanzado**
    - [ ] Diseñar un sistema de **"Tipos de Contenido" (Post Types)** que permita registrar y gestionar diferentes clases de contenido (ej: noticias, productos) de forma genérica.
    - [ ] Este sistema deberá incluir un CRUD centralizado y un conjunto de funciones globales para facilitar la creación, actualización, borrado y gestión de metadatos de cualquier tipo de contenido desde los temas o plugins.

## LLUVIA DE IDEAS (Ideas que tienen que ser integradas en caso de que sean validas)



# NOTAS IA -ESPECIFICAS PARA ESTE PROYECTO

Es de total prioridad antes de proceder con cualquier tarea, reducir la cantidad de codigo si encuentras oportunidades de refactorización, simplificación, etc, tener la menor cantidad de codigo es importante sin perder capacidades.

Siempre que tengas la posibilidad de recomendar una herramienta externa para ahorrar tiempo en hacer algo, hazlo.

# NOTAS PARA LA IA -GENERICAS QUE SE APLICAN GENERALMENTE AGNOSTICAS AL TIPO DE PROYECTO

Asume que todas las clases y funciones necesarias existen y están cargadas (autoloading). No uses class_exists() o function_exists(). Si no indico si un elemento es nuevo o existente, asúmelo como existente.

Encapsula todo el código JS en funciones. Yo gestionaré las invocaciones. No construyas HTML estructural desde JavaScript. No incluyas listeners de carga del DOM (ej: DOMContentLoaded) a menos que sea indispensable para la lógica interna de una función.

Comunícate siempre en español. Para cualquier elemento nuevo (variables, funciones, métodos, meta keys, etc.), utiliza estrictamente camelCase en español (ej: calcularTotal, datosUsuario).

Prefiero el uso de funciones PHP que actúan como Component Renderers para generar HTML, para cosas que de verdad se pueden repetir mucho.

El código debe ser eficiente, seguro, escalable y modular. Aplica los principios SOLID. La estructura del proyecto debe separar responsabilidades: Servicios, Handlers AJAX y Component Renderers. El objetivo es que todo sea reutilizable, mantenible y profesional.

Siempre proporciona la función o método completo, desde su firma hasta su llave de cierre (}). No incluyas código no modificado, la definición de la clase contenedora, ni bloques de código externos. Presenta una clase o archivo completo únicamente si te indico explícitamente que fue reescrito en su totalidad. En caso de duda, opta por la función individual.

Tu respuesta debe contener única y exclusivamente el código de la función, clase o método que fue modificado según mi última instrucción. Si una solicitud implica varios cambios: Identifica y realiza únicamente el primer cambio. Envíame solo la función/método completo que acabas de modificar. Indica cuáles son los siguientes pasos lógicos. Espera mi confirmación antes de proceder.