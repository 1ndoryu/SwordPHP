# Hoja de Ruta del Proyecto SwordPHP

## Concepto
El objetivo es desarrollar una alternativa a WordPress que sea minimalista, modular, increíblemente rápida y que siga las mejores prácticas de desarrollo para ser fácilmente mantenible y escalable. Tiene que ser fácil para los desarrolladores de temas conservar la esencia de WordPress: poder añadir lógica de forma sencilla en los temas sin necesidad de comprender o modificar la arquitectura del núcleo. El desarrollador de temas debe poder enfocarse en construir su tema, facilitándole funciones globales para su desarrollo (sin estar atadas a `use /App` ni a espacios de nombres complejos).

Progresivamente ir haciendo las equivalencias de wp siguiendo de guia equivalencia.md. Hay que corregir para no usar metodos, sino directamente funciones como wp.

---

## Principios Arquitectónicos
- **Instalación sin Migraciones:** No se utilizará un sistema de migraciones tradicional. Las tablas necesarias para el core del sistema (`usuarios`, `paginas`, `opciones`, etc.) deben ser creadas mediante un script de instalación inicial, similar a como lo hace WordPress.
- **Separación de Lógica y Presentación:** El núcleo (`swordCore`) debe estar completamente separado del contenido del usuario (`swordContent`), que incluye temas, plugins y archivos subidos.

---

## Fases del Proyecto

- [x] **Fase 1: Fundación y Autenticación**
    - Configuración del framework, la base de datos y el sistema de autenticación.

- [x] **Fase 2: Estructura del Panel de Administración**
    - Implementación del panel de administración con rutas protegidas y roles de usuario.

- [x] **Fase 3: Gestión de Assets y AJAX**
    - Creación de servicios para encolar assets (CSS/JS) y para manejar acciones AJAX de forma segura.

- [x] **Fase 4: Gestor de Páginas (CRUD)**
    - Implementación del CRUD de páginas, incluyendo un sistema de metadatos (`pagina_meta`).

- [x] **Fase 5: Sistema de Ruteo y Temas (Frontend)**
    - Desarrollo del ruteo dinámico, estructura básica de temas, y separación arquitectónica de `swordCore` y `swordContent`.

- [ ] **Fase 6: Mejoras y Extensibilidad**
    - [x] Implementar un CRUD para la gestión de usuarios en el panel de administración.
    - [x] Cambiar el motor de plantillas de Blade a PHP nativo.
    - [x] Refinar el `AssetService` para que sea fácilmente utilizable desde el `functions.php` de los temas.
    - [ ] Implementar la edición de usuarios en el panel de administración. *dejar para el final*
    - [x] Ampliar el sistema de metadatos para usuarios (`user_meta`).
    - [ ] Implementar un gestor de medios (`Media Library`) centralizado para subir y gestionar archivos. Tiene soportar varios tipos de archivos como en wp. Se guardan en swordContent en media y adentro estarán carpetas ordenadas por año, mes, y usuario.
    - [ ] Galeria en panel de los archivos subidos, se pueden borrar, modificar, etc, como en wp. 
    - [x] Bug en el index de galeria media:120  Uncaught ReferenceError: bootstrap is not defined at HTMLDocument.<anonymous> (media:120:27)
    
- [ ] **Fase 7: Gestor de Contenido Avanzado**
    - [ ] Diseñar un sistema de **"Tipos de Contenido" (Post Types)** que permita registrar y gestionar diferentes clases de contenido (ej: noticias, productos) de forma genérica, con su CRUD y funciones globales. Registrar el 

- [ ] **Fase 8: Gestión de Temas**
    - [ ] Desarrollar la gestión completa de temas desde el panel de administración (ver, activar).

- [ ] **Fase 9: Arquitectura de Plugins**
    - [ ] Diseñar e implementar una arquitectura de **plugins** para permitir la extensibilidad del core sin modificarlo, manteniendo una experiencia de desarrollo similar a la de WordPress. *TAREA PARA EL FINAL*

---

## Lluvias de ideas (tareas de faltan incluir en el flujo y ordenar sin validas)

- [ ] function.php se esta haciendo muy largo, hay que ordenarlo archivos mas pequeños, tal vez una carpeta de utils, no se. 

## Documentación
- [x] Crear un documento (`equivalencias.md`) que compare las funciones globales de SwordPHP con sus equivalentes en WordPress (ej: `add_action` vs `ajaxAccion`, funciones de metadatos, etc.).


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