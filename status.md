# Hoja de Ruta del Proyecto SwordPHP

## Concepto
El objetivo es desarrollar una alternativa a WordPress que sea minimalista, modular, increíblemente rápida y que siga las mejores prácticas de desarrollo para ser fácilmente mantenible y escalable. Tiene que ser facil para los desarrolladores de tema, conservar la esencia de wordpress de que en los temas, se puede hacer logica facil sin tocar la arquitectura y sin comprender como esta estructurado el tema, el usuario final no tiene que tocar nada ni modificar nada en nuestro cms o framework, tiene que enfocarse en construir su tema de la forma en la que quiera, sin arquictectura facilitandole funciones globales para su desarrollo (estas funciones no deben estar atadas a use /app ni nada)

---

## Principios Arquitectónicos
- **Instalación sin Migraciones:** No se utilizará un sistema de migraciones tradicional. Las tablas necesarias para el core del sistema (`usuarios`, `paginas`, `paginameta`, etc.) deben ser creadas mediante un script de instalación inicial, similar a como lo hace WordPress.

---

## Fases del Proyecto

- [x] **Fase 1: Fundación y Autenticación**
    - [x] Configuración inicial del framework y la base de datos.
    - [x] Creación del modelo y la tabla `usuarios`.
    - [x] Implementación del sistema de registro, login y logout.
    - [x] Creación de `UsuarioService` para manejar la lógica de negocio de los usuarios.

- [x] **Fase 2: Estructura del Panel de Administración**
    - [x] Creación de rutas protegidas para el panel.
    - [x] Diseño de un layout principal de dos columnas (sidebar y contenido).
    - [x] Creación de funciones de ayuda globales (ej: `usuarioActual()`).
    - [x] Personalización de la cabecera del panel con información del usuario.
    - [x] Implementar sistema de roles de usuario (admin/normal) y proteger rutas según el rol. Evitar que los usuarios normales o suscriptores, accedan al panel. El primer usuario en crearse tiene que ser admin, como en wp. 

- [x] **Fase 3: Gestión de Assets (CSS/JS)**
    - [x] Desarrollar un sistema sencillo para "encolar" y gestionar archivos CSS, JS, código y HTML.
    - [x] Desarrollar una forma de "localizar" scripts (pasar datos PHP a JS de forma segura, como `wp_localize_script`).

- [x] **Fase 4: Gestor de Páginas (CRUD Básico)**
    - [x] Crear el modelo y la tabla para `paginas`.
    - [x] Implementar la interfaz para crear, leer, actualizar y eliminar páginas.
    - [x] Implementar un sistema de metadatos (`pagina_meta`) para añadir campos personalizados a las páginas.
    - [x] Crear un sistema que sea tan fácil de usar para un desarrollador de temas como add_action en WordPress, pero que por debajo sea lo más seguro y estructurado posible, sin obligar al usuario a tocar los archivos del núcleo del CMS.
        - [x] El "Cerebro" Central (AjaxManagerService): Un nuevo servicio que actúa como el registro central de todas las acciones AJAX. Es el equivalente al sistema de "hooks" de WordPress, pero contenido en una clase.
        - [x] La Función "Mágica" Fácil de Usar (registrar_accion_ajax): Una función global, simple, que cualquier desarrollador podrá usar en el functions.php de su tema. Esta será nuestra versión de add_action. (si se puede usar camelCase por favor seria genial, tipo registrarAcion(ajaxAccion_funcionN, ajaxFuncion_funcionNombre) ejemplo, es opcional y si dificulta algo no hacerlo) 
        - [x] El "Portero" (AjaxController): El controlador se simplifica al máximo. Su única misión es recibir la llamada, preguntar al "Cerebro" qué función ejecutar y devolver la respuesta. No sabe nada sobre las acciones en sí.

- [ ] **Fase 5: Sistema de Ruteo y Temas (Frontend)**
    - [ ] Desarrollar un sistema de ruteo dinámico que muestre el contenido de las páginas creadas en el frontend, podran usar plantillas (asi como en wp).
    - [ ] Crear una estructura básica de "temas" para permitir personalizar la apariencia y lógica del sitio público. Los temas como en wp deben de tener su propio function php, sus estilos, plantillas, como en wp. 

- [ ] **Fase 6: Mejoras y Extensibilidad**
    - [ ] Ampliar el sistema de metadatos para usuarios (`user_meta`).
    - [ ] Implementar CRUD para la gestión de usuarios en el panel.
    - [ ] Diseñar e implementar una arquitectura de plugins para permitir la extensibilidad del core sin modificarlo.

- [ ] **Fase 7: Gestor de Contenido Avanzado**
    - [ ] Diseñar e implementar un sistema de "Tipos de Contenido" (Post Types) para registrar y gestionar diferentes clases de contenido (ej: páginas, noticias, productos) de forma genérica.


## LLuvia de idea (Estas ideas deben organizarse en las fases si son validas, si se te pide actualizar status.md integralas)

- [ ] Supongo que todos los posttype tendra un crud centralizado, que podra usar el wp_ajax, agregar metas, borrar metas, crear post, etc, etc, todo esto debe ser facil con funciones globables como en wordpress para cuando se hagan funciones personalizadas en los temas.
- [ ] El sistema de encolar script o cualquier cosa tiene que ser facil como en wp, pero mas facil, aqui no se como lo hicimos pero no lo veo practico si literalmente lo tenemos que especificar en las clases.
- [ ] Un sistema centralizado como en wp de medis, imagenes, archivos, etc, que se guarda por fecha y sera accesible desde el panel y los temas.
- [ ] Separacion la carpeta sword-webman, se llamara swordCore o no se que nombre seria bueno para representar el nucleo, luego necesito una separación para swordContent donde estaran los plugin, temas, las subidas, etc, ese contenido dinamico que no es obligatorio para que el core funcione, tendra una estructura similar a wordpress

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