/**
 * SwordPHP Admin SPA - Sistema de navegacion sin recarga
 * Intercepta enlaces y carga contenido via AJAX
 */

(function () {
    'use strict';

    const SPA = {
        contenedorPrincipal: null,
        urlBase: '/admin',
        cargando: false,

        /**
         * Inicializa el sistema SPA
         */
        init: function () {
            this.contenedorPrincipal = document.getElementById('contenidoPrincipal');
            if (!this.contenedorPrincipal) {
                console.warn('SPA: No se encontro #contenidoPrincipal');
                return;
            }

            this.interceptarEnlaces();
            this.interceptarFormularios();
            this.manejarHistorial();

            console.log('SPA: Sistema inicializado');
        },

        /**
         * Intercepta clics en enlaces del admin
         */
        interceptarEnlaces: function () {
            document.addEventListener('click', e => {
                const enlace = e.target.closest('a[href]');
                if (!enlace) return;

                const href = enlace.getAttribute('href');

                // Ignorar enlaces externos, con target, o que no sean del admin
                if (!href || href.startsWith('http') || href.startsWith('#') || enlace.hasAttribute('target') || enlace.hasAttribute('data-no-spa') || !href.startsWith('/admin')) {
                    return;
                }

                // Ignorar si se presiono Ctrl/Alt (para seleccion multiple)
                if (e.ctrlKey || e.altKey || e.metaKey) {
                    return;
                }

                e.preventDefault();
                this.navegar(href);
            });
        },

        /**
         * Intercepta envio de formularios
         */
        interceptarFormularios: function () {
            document.addEventListener('submit', e => {
                const formulario = e.target.closest('form');
                if (!formulario) return;

                // Ignorar formularios con data-no-spa
                if (formulario.hasAttribute('data-no-spa')) return;

                const action = formulario.getAttribute('action') || window.location.pathname;

                // Solo interceptar formularios del admin
                if (!action.startsWith('/admin')) return;

                // Ignorar formularios GET (filtros, busqueda)
                if (formulario.method.toUpperCase() === 'GET') {
                    e.preventDefault();
                    const formData = new FormData(formulario);
                    const params = new URLSearchParams(formData).toString();
                    const url = action + (params ? '?' + params : '');
                    this.navegar(url);
                    return;
                }

                e.preventDefault();
                this.enviarFormulario(formulario);
            });
        },

        /**
         * Maneja el boton atras/adelante del navegador
         */
        manejarHistorial: function () {
            window.addEventListener('popstate', e => {
                if (e.state && e.state.spa) {
                    this.cargarContenido(window.location.pathname + window.location.search, false);
                }
            });

            // Guardar estado inicial
            if (window.location.pathname.startsWith('/admin')) {
                history.replaceState({spa: true}, '', window.location.href);
            }
        },

        /**
         * Navega a una URL via AJAX
         */
        navegar: function (url) {
            history.pushState({spa: true}, '', url);
            this.cargarContenido(url, true);
        },

        /**
         * Carga contenido via AJAX
         */
        cargarContenido: function (url, actualizarHistorial) {
            if (this.cargando) return;
            this.cargando = true;

            // Mostrar indicador de carga
            this.contenedorPrincipal.classList.add('cargando');

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-SPA-Request': '1'
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Error ' + response.status);
                    return response.text();
                })
                .then(html => {
                    // Parsear la respuesta
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Buscar el contenido principal en la respuesta
                    const nuevoContenido = doc.getElementById('contenidoPrincipal');
                    const nuevoTitulo = doc.querySelector('.encabezado h1');

                    if (nuevoContenido) {
                        this.contenedorPrincipal.innerHTML = nuevoContenido.innerHTML;
                    } else {
                        // Si no hay contenidoPrincipal, usar el body completo
                        // (para respuestas parciales)
                        const body = doc.body;
                        if (body) {
                            this.contenedorPrincipal.innerHTML = body.innerHTML;
                        }
                    }

                    // Actualizar titulo del encabezado
                    if (nuevoTitulo) {
                        const tituloActual = document.querySelector('.encabezado h1');
                        if (tituloActual) {
                            tituloActual.textContent = nuevoTitulo.textContent;
                        }
                    }

                    // Actualizar titulo de la pagina
                    const metaTitulo = doc.querySelector('title');
                    if (metaTitulo) {
                        document.title = metaTitulo.textContent;
                    }

                    // Actualizar enlace activo en sidebar
                    this.actualizarNavegacionActiva(url);

                    // Reinicializar scripts
                    this.reinicializarScripts();

                    // Hacer scroll arriba
                    this.contenedorPrincipal.scrollTop = 0;
                })
                .catch(error => {
                    console.error('SPA Error:', error);
                    // En caso de error, hacer navegacion tradicional
                    window.location.href = url;
                })
                .finally(() => {
                    this.cargando = false;
                    this.contenedorPrincipal.classList.remove('cargando');
                });
        },

        /**
         * Envia un formulario via AJAX
         */
        enviarFormulario: function (formulario) {
            const formData = new FormData(formulario);
            const action = formulario.getAttribute('action') || window.location.pathname;
            const method = formulario.method.toUpperCase();

            // Mostrar indicador de carga
            this.contenedorPrincipal.classList.add('cargando');

            // Deshabilitar boton de envio
            const botonSubmit = formulario.querySelector('button[type="submit"]');
            if (botonSubmit) {
                botonSubmit.disabled = true;
                botonSubmit.dataset.textoOriginal = botonSubmit.textContent;
                botonSubmit.textContent = 'Guardando...';
            }

            fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-SPA-Request': '1'
                }
            })
                .then(response => {
                    // Verificar si es un redirect
                    if (response.redirected) {
                        this.navegar(response.url.replace(window.location.origin, ''));
                        return null;
                    }
                    return response.text();
                })
                .then(html => {
                    if (!html) return;

                    // Parsear respuesta
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const nuevoContenido = doc.getElementById('contenidoPrincipal');
                    if (nuevoContenido) {
                        this.contenedorPrincipal.innerHTML = nuevoContenido.innerHTML;
                    } else {
                        this.contenedorPrincipal.innerHTML = doc.body.innerHTML;
                    }

                    this.reinicializarScripts();
                })
                .catch(error => {
                    console.error('SPA Form Error:', error);
                    alert('Error al guardar. Intenta de nuevo.');
                })
                .finally(() => {
                    this.contenedorPrincipal.classList.remove('cargando');
                    if (botonSubmit) {
                        botonSubmit.disabled = false;
                        botonSubmit.textContent = botonSubmit.dataset.textoOriginal;
                    }
                });
        },

        /**
         * Actualiza el enlace activo en la navegacion
         */
        actualizarNavegacionActiva: function (url) {
            const enlaces = document.querySelectorAll('.barraLateral .enlaceNavegacion');
            enlaces.forEach(enlace => {
                const href = enlace.getAttribute('href');
                if (url === href || url.startsWith(href + '/')) {
                    enlace.classList.add('activo');
                } else if (href === '/admin' && url === '/admin') {
                    enlace.classList.add('activo');
                } else {
                    enlace.classList.remove('activo');
                }
            });
        },

        /**
         * Reinicializa scripts despues de cargar contenido
         */
        reinicializarScripts: function () {
            // Ejecutar scripts inline del contenido nuevo
            const scripts = this.contenedorPrincipal.querySelectorAll('script');
            scripts.forEach(script => {
                const nuevoScript = document.createElement('script');
                if (script.src) {
                    nuevoScript.src = script.src;
                } else {
                    nuevoScript.textContent = script.textContent;
                }
                script.parentNode.replaceChild(nuevoScript, script);
            });

            // Disparar evento personalizado para que otros scripts se reinicialicen
            document.dispatchEvent(new CustomEvent('spa:contenidoCargado'));
        }
    };

    // Inicializar cuando el DOM este listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => SPA.init());
    } else {
        SPA.init();
    }

    // Exponer globalmente para uso externo
    window.SPA = SPA;
})();
