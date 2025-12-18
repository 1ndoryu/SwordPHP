/**
 * Modulo Editor de Contenidos
 * Maneja slug automatico, metadatos, imagen destacada y eliminacion.
 */

/*
 * Inicializacion del modulo
 */
function inicializarEditor() {
    configurarSlugAutomatico();
    configurarMetadatos();
    configurarBotonEliminar();
    configurarImagenDestacada();
}

/*
 * Configurar generacion automatica de slug
 */
function configurarSlugAutomatico() {
    const inputTitulo = document.getElementById('inputTitulo');
    const inputSlug = document.getElementById('inputSlug');

    if (!inputTitulo || !inputSlug) return;

    let slugEditadoManualmente = inputSlug.value !== '';

    inputSlug.addEventListener('input', function () {
        slugEditadoManualmente = true;
    });

    inputTitulo.addEventListener('input', function () {
        if (!slugEditadoManualmente || inputSlug.value === '') {
            const slug = this.value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .trim();
            inputSlug.value = slug;
        }
    });
}

/*
 * Configurar funcionalidad de metadatos
 */
function configurarMetadatos() {
    const listaMetadatos = document.getElementById('listaMetadatos');
    const mensajeSinMetadatos = document.getElementById('mensajeSinMetadatos');
    const botonAgregar = document.getElementById('botonAgregarMeta');

    if (!botonAgregar) return;

    botonAgregar.addEventListener('click', function () {
        ocultarMensajeVacio();

        const nuevaFila = document.createElement('div');
        nuevaFila.className = 'filaMetadato filaMetadatoNueva';
        nuevaFila.innerHTML = `
            <div class="campoMetaClave">
                <input 
                    type="text" 
                    class="inputMetaClave" 
                    name="meta_keys[]" 
                    placeholder="Clave (ej: autor, precio)"
                    required>
            </div>
            <div class="campoMetaValor">
                <input 
                    type="text" 
                    class="inputMetaValor" 
                    name="meta_values[]"
                    placeholder="Valor">
                <input type="hidden" name="meta_is_json[]" value="0">
            </div>
            <div class="accionesMetadato">
                <button type="button" class="botonConvertirJson" title="Convertir a JSON" onclick="convertirAJson(this)">
                    {}
                </button>
                <button type="button" class="botonEliminarMeta" title="Eliminar" onclick="eliminarMetadato(this)">
                    X
                </button>
            </div>
        `;

        listaMetadatos.appendChild(nuevaFila);
        nuevaFila.querySelector('.inputMetaClave').focus();
    });

    function ocultarMensajeVacio() {
        if (mensajeSinMetadatos) {
            mensajeSinMetadatos.style.display = 'none';
        }
    }
}

/*
 * Ocultar mensaje de metadatos vacios
 */
function ocultarMensajeVacio() {
    const mensajeSinMetadatos = document.getElementById('mensajeSinMetadatos');
    if (mensajeSinMetadatos) {
        mensajeSinMetadatos.style.display = 'none';
    }
}

/*
 * Mostrar mensaje vacio si no hay metadatos
 */
function mostrarMensajeVacioSiNecesario() {
    const listaMetadatos = document.getElementById('listaMetadatos');
    const mensajeSinMetadatos = document.getElementById('mensajeSinMetadatos');
    const filas = listaMetadatos?.querySelectorAll('.filaMetadato');

    if (filas?.length === 0 && mensajeSinMetadatos) {
        mensajeSinMetadatos.style.display = 'block';
    }
}

/*
 * Editar clave de metadato
 */
function editarClave(boton) {
    const fila = boton.closest('.filaMetadato');
    const inputClave = fila.querySelector('.inputMetaClave');

    if (inputClave.hasAttribute('readonly')) {
        inputClave.removeAttribute('readonly');
        inputClave.focus();
        inputClave.select();
        boton.textContent = 'OK';
    } else {
        inputClave.setAttribute('readonly', true);
        boton.textContent = 'Editar';
    }
}

/*
 * Eliminar metadato
 */
function eliminarMetadato(boton) {
    const fila = boton.closest('.filaMetadato');
    fila.remove();
    mostrarMensajeVacioSiNecesario();
}

/*
 * Convertir campo a JSON
 */
function convertirAJson(boton) {
    const fila = boton.closest('.filaMetadato');
    const campoValor = fila.querySelector('.campoMetaValor');
    const inputActual = campoValor.querySelector('.inputMetaValor');
    const valorActual = inputActual.value;

    const textarea = document.createElement('textarea');
    textarea.className = 'inputMetaValor textareaMetaValor';
    textarea.name = 'meta_values[]';
    textarea.placeholder = 'Valor (JSON)';

    try {
        const parsed = JSON.parse(valorActual);
        textarea.value = JSON.stringify(parsed, null, 2);
    } catch (e) {
        textarea.value = valorActual
            ? JSON.stringify(
                  {
                      valor: valorActual
                  },
                  null,
                  2
              )
            : '{\n  \n}';
    }

    const hiddenIsJson = campoValor.querySelector('input[name="meta_is_json[]"]');
    hiddenIsJson.value = '1';

    inputActual.replaceWith(textarea);
    boton.remove();
}

/*
 * Configurar boton de eliminar contenido
 */
function configurarBotonEliminar() {
    const botonEliminar = document.getElementById('botonEliminarContenido');

    if (!botonEliminar) return;

    const baseUrl = botonEliminar.dataset.baseUrl;
    const contentId = botonEliminar.dataset.contentId;

    botonEliminar.addEventListener('click', function () {
        if (!confirm('¿Seguro que deseas mover este contenido a la papelera?')) return;
        if (!contentId) return;

        fetch(baseUrl + '/' + contentId, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (window.SPA) {
                        window.SPA.navegar(baseUrl);
                    } else {
                        window.location.href = baseUrl;
                    }
                } else {
                    alert('Error al eliminar: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el contenido');
            });
    });
}

/*
 * Configurar selector de imagen destacada
 */
function configurarImagenDestacada() {
    const botonSeleccionar = document.getElementById('botonSeleccionarImagen');
    const botonQuitar = document.getElementById('botonQuitarImagen');
    const previewImagen = document.getElementById('previewImagenDestacada');
    const inputImagenId = document.getElementById('inputImagenDestacadaId');
    const inputImagenUrl = document.getElementById('inputImagenDestacadaUrl');

    if (botonSeleccionar) {
        botonSeleccionar.addEventListener('click', function () {
            if (typeof SelectorMedios === 'undefined') {
                alert('Error: El selector de medios no esta disponible');
                return;
            }

            window.abrirSelectorMedios({
                tipo: 'image',
                multiple: false,
                onSeleccionar: function (medio) {
                    previewImagen.innerHTML = `<img src="${medio.url}" alt="Imagen destacada">`;
                    inputImagenId.value = medio.id;
                    inputImagenUrl.value = medio.url;
                    botonSeleccionar.textContent = 'Cambiar';
                    botonQuitar.style.display = '';
                }
            });
        });
    }

    if (botonQuitar) {
        botonQuitar.addEventListener('click', function () {
            previewImagen.innerHTML = `
                <div class="placeholderImagen">
                    <span class="iconoPlaceholder">🖼️</span>
                    <span>Sin imagen</span>
                </div>
            `;
            inputImagenId.value = '';
            inputImagenUrl.value = '';
            botonSeleccionar.textContent = 'Seleccionar';
            this.style.display = 'none';
        });
    }
}

/* Inicializar cuando el DOM este listo */
document.addEventListener('DOMContentLoaded', inicializarEditor);

/* Reinicializar cuando SPA cargue nuevo contenido */
document.addEventListener('spa:contenidoCargado', inicializarEditor);
