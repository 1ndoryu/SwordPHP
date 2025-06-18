document.addEventListener('DOMContentLoaded', function () {
    // 1. Encontrar todos los 'select' en el documento
    const allSelects = document.querySelectorAll('select');

    // 2. Iterar sobre cada 'select' para transformarlo
    allSelects.forEach(originalSelect => {
        // Crear el contenedor principal
        const customContainer = document.createElement('div');
        customContainer.classList.add('custom-select-container');

        // Crear el disparador (lo que se ve como el select)
        const customTrigger = document.createElement('div');
        customTrigger.classList.add('custom-select-trigger');
        // Mostrar el texto de la opción seleccionada inicialmente
        customTrigger.textContent = originalSelect.options[originalSelect.selectedIndex].textContent;

        // Crear la lista de opciones personalizadas
        const customOptions = document.createElement('ul');
        customOptions.classList.add('custom-options');

        // 3. Crear una opción personalizada por cada <option> original
        Array.from(originalSelect.options).forEach((optionElement, index) => {
            const customOption = document.createElement('li');
            customOption.classList.add('custom-option');
            customOption.textContent = optionElement.textContent;
            customOption.dataset.value = optionElement.value; // Guardar el valor real

            if (originalSelect.selectedIndex === index) {
                customOption.classList.add('selected');
            }

            // 4. Añadir evento de clic a cada opción personalizada
            customOption.addEventListener('click', function () {
                // Quitar 'selected' de la opción anterior
                const previouslySelected = customOptions.querySelector('.selected');
                if (previouslySelected) {
                    previouslySelected.classList.remove('selected');
                }
                // Añadir 'selected' a la nueva opción
                this.classList.add('selected');

                // Actualizar el texto del disparador
                customTrigger.textContent = this.textContent;

                // IMPORTANTE: Actualizar el valor del select original
                originalSelect.value = this.dataset.value;

                // Disparar un evento de cambio en el select original por si hay otros scripts escuchando
                originalSelect.dispatchEvent(new Event('change'));

                // Cerrar el menú desplegable
                customContainer.classList.remove('open');
            });

            customOptions.appendChild(customOption);
        });

        // 5. Ensamblar los elementos creados
        customContainer.appendChild(customTrigger);
        customContainer.appendChild(customOptions);

        // Reemplazar el select original con la versión personalizada
        originalSelect.parentNode.insertBefore(customContainer, originalSelect.nextSibling);

        // 6. Añadir evento para abrir/cerrar el menú
        customTrigger.addEventListener('click', () => {
            customContainer.classList.toggle('open');
        });
    });

    // 7. Cerrar todos los menús si se hace clic fuera
    document.addEventListener('click', function (e) {
        document.querySelectorAll('.custom-select-container').forEach(container => {
            if (!container.contains(e.target)) {
                container.classList.remove('open');
            }
        });
    });
});
