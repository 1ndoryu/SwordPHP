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

## 2. Instrucciones para la IA: Flujo de Trabajo

Estas instrucciones son el protocolo a seguir para iniciar y ejecutar cualquier tarea de desarrollo o documentación.

### Cómo Empezar una Tarea (SOLO PARA MODO DEFAULT)

Cuando se te asigne una tarea, tu primer paso es un análisis preliminar del proyecto completo para preparar el terreno para la implementación.

1. **Análisis de la Tarea:** Comprende a fondo los requisitos de la tarea descrita en la "Hoja de Ruta".

2. **Identificación de Archivos Relevantes:** Examina la totalidad del código fuente y determina qué archivos son necesarios o relevantes para completar la tarea. **Es preferible incluir más archivos de los necesarios a que falte información crucial.**

3. **Construcción del Comando de Contexto:** Prepara el comando o la lista de archivos que se utilizarán para la fase de implementación. Esto enfoca el trabajo en un subconjunto relevante del proyecto.

4. **Justificación de la Selección:** Junto al comando, añade una breve explicación de por qué cada archivo seleccionado es relevante para la tarea. Esto guía el razonamiento durante la implementación.

5. **Directriz Clave - No Implementar la Solución:** En esta fase inicial, tu objetivo es **investigar y preparar, no resolver**. No debes incluir el código de la solución final. Tu análisis debe servir como una guía de investigación para el siguiente paso.

### Tareas de Documentación

Para las tareas de documentación, el flujo es similar:

1. Decide qué funcionalidad o componente necesita ser documentado.
2. Identifica y lee los archivos del núcleo (`swordCore`) necesarios para entender completamente dicha funcionalidad.
3. Construye la documentación en formato Markdown dentro del directorio `content/docs`.
4. Durante el proceso, siéntete libre de proponer mejoras o cambios en el código del núcleo para mejorar la consistencia y la claridad. La documentación debe reflejar una visión a largo plazo del proyecto.

### Cómo Empezar una Tarea (SOLO PARA MODO SELECCION O BUSQUEDA)

1. **Resolver** Estamos en el terreno de la implementación, se ha dado un contexto, debe identificar la tarea a resolver, e ir paso a paso siguiendo las instrucciones en status.md, sigues las directrices técnicas.

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

-   [ ] `admin` | `artista`  |   `fan`  | la api esta condicionada a actuar segun el tipo de usuario, las funcionalidades para admin esta bien, pero fue un error especificar condiciones para artista, y fans, esos tipos de usuarios son especificos de una aplicacion externa, y no deberian especificarse en este cms, por favor, encuentra la manera de que sean las aplicaciones externas que especifiquen, y manterla generalidad y agnosticismo de la api de este cms, le documentacion-api.md y date cuenta del problema

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

# Nota, como hacer test

curl -i -X DELETE "https://swordphp.com/content/4" \
-H "Authorization: Bearer $API_TOKEN"

export BASE_URL="https://swordphp.com"

export ARTISTA_USER="test6"
export ARTISTA_PASS="test6"
export ADMIN_USER="wans"
export ADMIN_PASS="rcJd4>@m4p8D"

export API_TOKEN="OJ2w2vxeXF07BHeYOPFbuRtTZ9RGsiSYiiJ6LFiEA0EWafFvGqw0yxz67iB9"