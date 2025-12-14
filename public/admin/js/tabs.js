document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initNavigation();
});

function initTabs() {
    const tabs = document.querySelectorAll('.botonTab');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetId = tab.dataset.tab; // e.g. "tabInicio"
            const container = tab.closest('.contenedorTabs');

            // Validation
            if (!container || !targetId) return;

            // Desactivar todos en este contenedor
            container.querySelectorAll('.botonTab').forEach(t => t.classList.remove('activo'));
            container.querySelectorAll('.panelTab').forEach(p => p.classList.remove('activo'));

            // Activar el seleccionado
            tab.classList.add('activo');
            const panel = container.querySelector(`#${targetId}`);
            if (panel) {
                panel.classList.add('activo');
            }
        });
    });
}

function initNavigation() {
    const navLinks = document.querySelectorAll('.enlaceNavegacion');
    const mainContent = document.getElementById('contenidoPrincipal');

    if (!mainContent) return;

    navLinks.forEach(link => {
        link.addEventListener('click', async e => {
            // Permitir abrir en nueva pestaña
            if (e.metaKey || e.ctrlKey) return;

            e.preventDefault();
            const url = link.getAttribute('href');

            // Actualizar estado visual del menú
            navLinks.forEach(l => l.classList.remove('activo'));
            link.classList.add('activo');

            try {
                // Indicador de carga simple
                mainContent.style.opacity = '0.5';

                const response = await fetch(url);
                if (!response.ok) throw new Error('Error de red');

                const html = await response.text();

                // Extraer solo el contenido relevante del HTML recibido
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('contenidoPrincipal');

                if (newContent) {
                    mainContent.innerHTML = newContent.innerHTML;
                    // Reinicializar tabs si el nuevo contenido tiene tabs
                    initTabs();

                    // Actualizar URL en el navegador
                    window.history.pushState({}, '', url);
                } else {
                    // Fallback si no encuentra el contenedor
                    window.location.href = url;
                }
            } catch (error) {
                console.error('Error cargando página:', error);
                window.location.href = url;
            } finally {
                mainContent.style.opacity = '1';
            }
        });
    });

    // Manejar botones de atrás/adelante del navegador
    window.addEventListener('popstate', () => {
        window.location.reload();
    });
}
