/**
 * Inicializa la funcionalidad de despliegue para los submenús
 * en la barra lateral del panel de administración.
 */
function inicializarSidebarAdmin() {
    const navItems = document.querySelectorAll('.panelSidebarNav .nav-item');

    navItems.forEach(item => {
        const link = item.querySelector('a.nav-link');
        const submenu = item.querySelector('.nav-submenu');

        // Añadir el listener solo a los elementos que tienen un submenú.
        if (submenu) {
            link.addEventListener('click', function (e) {
                // Prevenir la navegación si el enlace es un ancla simple.
                if (this.getAttribute('href') === '#') {
                    e.preventDefault();
                }

                // Alternar la clase 'open' en el elemento LI padre.
                item.classList.toggle('open');
            });
        }
    });
}

/**
 * Nos aseguramos de que el DOM esté completamente cargado antes de
 * ejecutar el script del sidebar.
 */
document.addEventListener('DOMContentLoaded', inicializarSidebarAdmin);
