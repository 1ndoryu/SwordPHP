# Hoja de Ruta del Proyecto SwordPHP

## 1. Filosofía y Principios Fundamentales

### Concepto

El objetivo es construir un Sistema de Gestión de Contenidos (CMS) que sirva como una alternativa a WordPress, priorizando el **minimalismo**, la **velocidad** y la **modularidad**. El diseño debe adherirse a las mejores prácticas de desarrollo para garantizar que sea fácil de mantener y escalar.

Un pilar fundamental es la **experiencia del desarrollador**. Los creadores de temas deben poder extender la funcionalidad del CMS de forma sencilla, similar al ecosistema de WordPress, utilizando funciones globales intuitivas sin necesidad de comprender la arquitectura interna del núcleo o gestionar espacios de nombres complejos.

### Directrices Generales

-   **Convenciones:** Todo el código se escribirá en español utilizando la convención `camelCase`.
-   **Equivalencias:** Se buscará progresivamente la equivalencia funcional con WordPress, utilizando el archivo `equivalencia.md` como guía y prefiriendo funciones globales sobre métodos de clase.
-   **Estilos:** No se utilizarán frameworks de CSS como Tailwind o Bootstrap. Se priorizará CSS nativo y modular.
-   **Fase Actual:** El proyecto se encuentra en fase **Alpha**. Las decisiones y la documentación deben tener una visión a largo plazo para sentar bases sólidas.

---

#

#

#

#

#

#

#

#

#

#

#

---

## 3. Hoja de Ruta: Tareas Prioritarias (TAREA ACTUAL)


---

#

#

#

#

#

#

#

#

#

#

#

## 4. Tareas aplazadas Sword

Esta es una lista de funcionalidades y mejoras a considerar para futuras versiones.

-   [ ] La creacion de post type debe ser dinamica y no definida en el codigo.
-   [ ] Documentar la función `definePostType`.
-   [ ] API para gestionar los menús de navegación del panel.
-   [ ] API para gestionar los layouts de los temas.
-   [ ] Selección múltiple y acciones en lote en la galería de medios.
-   [ ] Filtros en tiempo real para las tablas de contenido en el panel.
-   [ ] Documentar la arquitectura recomendada para la creación de temas.
-   [ ] API de Ajustes (Customizer) para que temas y plugins añadan opciones de configuración.
-   [ ] Completar el documento `equivalencias.md`.
-   [ ] Crear `hooks.md` para documentar todos los `addAction` y `addFilter` del núcleo.
-   [ ] Crear una guía de uso de la base de datos con Eloquent.
-   [ ] Investigar la infraestructura para un marketplace de plugins y temas.
-   [ ] Mejoras en el gestor de medios: vista de galería, edición de metadatos.
-   [ ] Investigar y desarrollar una capa de compatibilidad para temas de WordPress.
-   [ ] Optimización de la base de datos y consultas de metadatos (arreglar el camelCase)
-   [ ] Css para elementos generales del form.

---

## 5. Directrices Técnicas Generales para la IA

-   **Prioridad a la Refactorización:** Antes de implementar código nuevo, busca oportunidades para simplificar y reducir el existente.
-   **Recomendación de Herramientas:** Si una herramienta externa puede acelerar el desarrollo, propón su uso.
-   **Formato de Código:** Proporciona únicamente el código modificado, preferiblemente a nivel de función/método completo. Presenta un archivo completo solo si fue reescrito en su totalidad. Para tareas grandes, procede archivo por archivo.
-   **Abstracción:** Asume que todas las clases y funciones necesarias ya existen y están disponibles (`autoloading`).
-   **JavaScript:** Encapsula todo el código JS en funciones. No generes HTML estructural desde JS y evita listeners de carga del DOM (`DOMContentLoaded`) a menos que sea indispensable.
-   **Modularidad:** Aplica los principios SOLID y estructura el código separando responsabilidades (Servicios, Controladores, Vistas, etc.).
