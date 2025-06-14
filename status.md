# Hoja de Ruta del Proyecto SwordPHP

## Concepto
El objetivo es desarrollar una alternativa a WordPress que sea minimalista, modular, increíblemente rápida y que siga las mejores prácticas de desarrollo para ser fácilmente mantenible y escalable.

---

## Fases del Proyecto

- [x] **Fase 1: Fundación y Autenticación**
    - [x] Configuración inicial del framework y la base de datos.
    - [x] Creación del modelo y la migración para la tabla `usuarios`.
    - [x] Implementación del sistema de registro, login y logout.
    - [x] Creación de `UsuarioService` para manejar la lógica de negocio de los usuarios.

- [x] **Fase 2: Estructura del Panel de Administración**
    - [x] Creación de rutas protegidas para el panel.
    - [x] Diseño de un layout principal de dos columnas (sidebar y contenido).
    - [x] Creación de funciones de ayuda globales (ej: `usuarioActual()`).
    - [x] Personalización de la cabecera del panel con información del usuario.

- [ ] **Fase 3: Gestión de Assets (CSS/JS)**
    - [ ] Desarrollar un sistema sencillo para "encolar" y gestionar archivos CSS y JavaScript tanto en el panel como en el frontend. Tiene que poder encolar archivos individualmente y carpetas, tambien codigo y html, como en wordpress.

- [ ] **Fase 4: Gestor de Contenido (CRUD Básico)**
    - [ ] Crear el modelo y la migración para la tabla `contenidos` (o `paginas`).
    - [ ] Implementar la interfaz para crear, leer, actualizar y eliminar contenidos.
    - [ ] **(Idea integrada)** Implementar un sistema de metadatos (como `post_meta`) para añadir campos personalizados a los contenidos.
    - [ ] **(Idea integrada)** Crear un manejador de peticiones AJAX (similar a `wp_ajax`) para hacer las interacciones del CRUD más dinámicas que las funciones puedan usar en cualquier parte.

- [ ] **Fase 5: Sistema de Ruteo y Temas (Frontend)**
    - [ ] Desarrollar un sistema de ruteo dinámico que muestre el contenido creado en el frontend.
    - [ ] Crear una estructura básica de "temas" para permitir personalizar la apariencia del sitio público.

- [ ] **Fase 6: Mejoras y Sistema de Plugins (Avanzado)**
    - [ ] **(Idea integrada)** Ampliar el sistema de metadatos para usuarios (`user_meta`).
    - [ ] Diseñar e implementar una arquitectura de plugins para permitir la extensibilidad del core sin modificarlo.

## LLuvia de idea (Estas ideas deben organizarse en las fases si son validas)

- [ ] Creo que lo de tipo de usuario admin vs normal debería ir en la fase 2 