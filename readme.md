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