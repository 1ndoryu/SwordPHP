

# Plan de Implementación: Sistema de Tipos de Contenido (Post Types)

El objetivo es crear un sistema que permita registrar y gestionar diferentes tipos de contenido (como 'noticias', 'proyectos', 'productos', etc.) de forma genérica y extensible, similar a los Post Types de WordPress. La implementación se centrará en el registro a través de código y la integración automática en el panel de administración.

## Subtareas

- [ ] **1. Crear el Servicio de Registro Central (`TipoContenidoService`)**
    - **Objetivo:** Establecer una única fuente de verdad para todos los tipos de contenido registrados en el sistema.
    - **Acciones:**
        - Crear un nuevo archivo: `swordCore/app/service/TipoContenidoService.php`.
        - Implementar esta clase como un **Singleton** para que mantenga el estado de los registros durante toda la petición.
        - Crear un método `registrar(string $slug, array $argumentos)` que almacene la configuración de un nuevo tipo de contenido (ej: nombre en plural, singular, etiquetas, si es público, etc.).
        - Crear un método `obtener(string $slug)` para recuperar la configuración de un tipo de contenido específico.
        - Crear un método `obtenerTodos()` que devuelva todos los tipos de contenido registrados.

- [ ] **2. Implementar la Función Global de Registro (`registrarTipoContenido`)**
    - **Objetivo:** Proporcionar a los desarrolladores de temas una función sencilla y global para registrar sus tipos de contenido, al estilo de WordPress.
    - **Acciones:**
        - Añadir la nueva función `registrarTipoContenido(string $slug, array $argumentos)` al archivo `swordCore/app/functions.php`.
        - Internamente, esta función simplemente llamará al método `registrar()` del `TipoContenidoService`.
        - Registrar el tipo de contenido 'página' por defecto usando esta nueva función dentro de `functions.php` para asegurar la consistencia del sistema.

- [ ] **3. Dinamizar el Menú del Panel de Administración**
    - **Objetivo:** Hacer que el menú lateral del panel se actualice automáticamente cuando se registra un nuevo tipo de contenido.
    - **Acciones:**
        - Modificar la función `renderizarMenuLateralAdmin()` en `swordCore/app/functions.php`.
        - La función deberá obtener todos los tipos de contenido registrados llamando a `TipoContenidoService::obtenerTodos()`.
        - Iterar sobre los tipos de contenido y generar dinámicamente los elementos `<li>` del menú. El enlace `href` apuntará a `/panel/{slug}`, donde `{slug}` es el identificador del tipo de contenido (ej: `/panel/noticias`).
        - Mantener los elementos estáticos del menú como 'Dashboard', 'Medios' y 'Ajustes'.

- [ ] **4. Crear un Controlador Genérico para Tipos de Contenido (`TipoContenidoController`)**
    - **Objetivo:** Centralizar la lógica CRUD para todos los tipos de contenido, evitando la duplicación de código.
    - **Acciones:**
        - Crear un nuevo controlador: `swordCore/app/controller/TipoContenidoController.php`.
        - Sus métodos (`index`, `create`, `store`, `edit`, `update`, `destroy`) aceptarán un parámetro `$slug` que identifique el tipo de contenido que se está gestionando.
        - El método `index` consultará la tabla `paginas` filtrando por `WHERE tipocontenido = ?`, usando el `$slug` recibido.
        - El método `store` asignará el valor del `$slug` a la columna `tipocontenido` al crear un nuevo registro.
        - Este controlador reemplazará la necesidad de tener un `PaginaController` específico, ya que podrá manejar 'paginas' y cualquier otro tipo.

- [ ] **5. Unificar las Vistas de Gestión (Listado, Creación, Edición)**
    - **Objetivo:** Reutilizar las vistas del CRUD para cualquier tipo de contenido, personalizando títulos y etiquetas dinámicamente.
    - **Acciones:**
        - Tomar como base las vistas existentes en `swordCore/app/view/admin/paginas/`.
        - Moverlas y generalizarlas a un nuevo directorio, por ejemplo `swordCore/app/view/admin/tipoContenido/`.
        - Modificar las vistas para que usen variables para las etiquetas (ej: "Añadir Nueva Noticia" en lugar de "Añadir Nueva Página") y las URLs (ej: `action="/panel/noticias/store"`).
        - El `TipoContenidoController` pasará a la vista un objeto con la configuración del tipo de contenido actual (obtenida del `TipoContenidoService`) para que la vista pueda renderizar los textos y rutas correctas.

- [ ] **6. Generar Rutas del Panel Dinámicamente**
    - **Objetivo:** Crear automáticamente las rutas del panel para el CRUD de cada tipo de contenido registrado.
    - **Acciones:**
        - Modificar el archivo `swordCore/config/route.php`.
        - Dentro del archivo, antes de definir el grupo de rutas del panel, obtener todos los tipos de contenido registrados desde el `TipoContenidoService`.
        - Crear un bucle que itere sobre cada tipo de contenido y genere mediante programación las rutas CRUD para él (ej: `Route::get('/{slug}', [TipoContenidoController::class, 'index']);`, `Route::get('/{slug}/create', ...)`).
        - Estas nuevas rutas se anidarán dentro del grupo `/panel` y apuntarán al `TipoContenidoController` genérico.
        - Eliminar el grupo de rutas estático para `/panel/paginas`, ya que ahora será manejado por este sistema dinámico.