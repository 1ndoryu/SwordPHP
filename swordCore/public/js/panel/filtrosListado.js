document.addEventListener('DOMContentLoaded', function () {
    const inicializarFiltros = (formId, containerId) => {
        const form = document.getElementById(formId);
        if (!form) return;

        const inputs = form.querySelectorAll('input, select');

        // Función debounce
        const debounce = (func, delay) => {
            let timeout;
            return function (...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        };

        const submitForm = async () => {
            const formData = new FormData(form);
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value) { // Solo añadir parámetros que tienen valor
                    params.append(key, value);
                }
            }

            // Mantener el parámetro 'page' si existe y estamos cambiando filtros,
            // o resetear a página 1 si se está haciendo una nueva búsqueda/filtro.
            // Para simplificar, por ahora siempre vamos a la página 1 al cambiar filtros.
            // params.delete('page'); // Opcional: forzar ir a la página 1

            const url = `${form.action}?${params.toString()}`;

            try {
                // Opción A: Recarga de página (más simple)
                window.location.href = url;

                // Opción B: AJAX (más complejo, requiere que el backend devuelva solo el fragmento HTML)
                // const response = await fetch(url, {
                //     method: 'GET', // O el método que use tu backend para listar
                //     headers: {
                //         'X-Requested-With': 'XMLHttpRequest' // Para identificar la petición AJAX en backend si es necesario
                //     }
                // });
                // if (!response.ok) {
                //     throw new Error(`Error en la petición: ${response.statusText}`);
                // }
                // const html = await response.text();
                // const container = document.getElementById(containerId);
                // if (container) {
                //     container.innerHTML = html;
                //     // Aquí también necesitarías re-inicializar listeners de paginación si la paginación también se carga por AJAX
                // } else {
                //     // Si el contenedor no se encuentra, podría ser mejor recargar toda la página
                //     window.location.href = url;
                // }

            } catch (error) {
                console.error('Error al aplicar filtros:', error);
                // Como fallback, o si hay error, recargar la página
                // window.location.href = url;
            }
        };

        const debouncedSubmit = debounce(submitForm, 500); // 500ms de espera

        inputs.forEach(input => {
            if (input.type === 'text' || input.type === 'date') {
                input.addEventListener('input', debouncedSubmit);
            } else if (input.tagName === 'SELECT') {
                input.addEventListener('change', submitForm); // Para selects, enviar inmediatamente
            }
        });

        // Evitar envío tradicional del formulario si se usa AJAX,
        // pero como estamos usando recarga de página (Opción A),
        // este preventDefault no es estrictamente necesario para la Opción A,
        // pero sí si se cambia a Opción B (AJAX).
        // form.addEventListener('submit', function(event) {
        //     event.preventDefault(); // Prevenir envío tradicional si se usa AJAX
        //     submitForm(); // Llamar a nuestra función de envío (que puede ser debounced o no)
        // });
    };

    // Inicializar para cada página de listado
    inicializarFiltros('filtrosFormPaginas', 'listaPaginasContainer');
    inicializarFiltros('filtrosFormContenido', 'listaContenidoContainer'); // Asumiendo que este es el ID para tipoContenido
    inicializarFiltros('filtrosFormUsuarios', 'listaUsuariosContainer');

    // Manejo de botones "Limpiar"
    // Esto es genérico, si los botones de limpiar tienen una clase común.
    // Si no, se puede hacer específico por ID de formulario.
    document.querySelectorAll('.btnLimpiar').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const form = this.closest('form'); // Encuentra el formulario padre del botón
            if (form) {
                form.reset(); // Resetea los campos del formulario
                // Obtener la URL base del action del form (sin query params)
                const baseUrl = form.action.split('?')[0];
                window.location.href = baseUrl; // Redirige a la URL limpia
            }
        });
    });

});
