/**
 * Selector de Medios Reutilizable
 * Componente para seleccionar archivos de la libreria de medios
 */

class SelectorMedios {
    constructor(opciones = {}) {
        this.opciones = {
            tipo: opciones.tipo || 'image',
            multiple: opciones.multiple || false,
            onSeleccionar: opciones.onSeleccionar || null,
            onCancelar: opciones.onCancelar || null,
            ...opciones
        };

        this.mediosSeleccionados = [];
        this.paginaActual = 1;
        this.totalPaginas = 1;
        this.medios = [];
        this.modal = null;
        this.cargando = false;

        this.crearModal();
    }

    crearModal() {
        if (document.getElementById('modalSelectorMedios')) {
            this.modal = document.getElementById('modalSelectorMedios');
            return;
        }

        const html = `
            <div id="modalSelectorMedios" class="modalSelectorMedios">
                <div class="selectorMediosContenedor">
                    <div class="selectorMediosCabecera">
                        <h3>Seleccionar medio</h3>
                        <button type="button" class="botonCerrarSelector" data-accion="cerrar">×</button>
                    </div>
                    <div class="selectorMediosToolbar">
                        <button type="button" class="botonPrimario" data-accion="subir">
                            + Subir archivo
                        </button>
                        <div class="selectorMediosFiltros">
                            <select class="selectFiltro" data-filtro="tipo">
                                <option value="">Todos</option>
                                <option value="image" selected>Imagenes</option>
                                <option value="audio">Audio</option>
                                <option value="video">Video</option>
                                <option value="application">Documentos</option>
                            </select>
                        </div>
                    </div>
                    <div class="selectorMediosContenido">
                        <div class="selectorGrillaMedios" id="selectorGrillaMedios"></div>
                        <div class="selectorPaginacion" id="selectorPaginacion"></div>
                    </div>
                    <div class="selectorMediosPie">
                        <div class="selectorMediosInfo" id="selectorMediosInfo">
                            Ningun archivo seleccionado
                        </div>
                        <div class="selectorMediosAcciones">
                            <button type="button" class="botonSecundario" data-accion="cancelar">Cancelar</button>
                            <button type="button" class="botonPrimario" data-accion="confirmar" disabled>Seleccionar</button>
                        </div>
                    </div>
                </div>
                <input type="file" id="selectorInputSubir" style="display:none" accept="image/*,audio/*,video/*">
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
        this.modal = document.getElementById('modalSelectorMedios');
        this.inicializarEventos();
    }

    inicializarEventos() {
        this.modal.addEventListener('click', e => {
            const accion = e.target.dataset.accion;
            if (accion === 'cerrar' || accion === 'cancelar') {
                this.cerrar();
            } else if (accion === 'confirmar') {
                this.confirmar();
            } else if (accion === 'subir') {
                document.getElementById('selectorInputSubir').click();
            }

            if (e.target === this.modal) {
                this.cerrar();
            }
        });

        this.modal.querySelector('[data-filtro="tipo"]').addEventListener('change', e => {
            this.opciones.tipo = e.target.value;
            this.paginaActual = 1;
            this.cargarMedios();
        });

        document.getElementById('selectorInputSubir').addEventListener('change', e => {
            if (e.target.files.length > 0) {
                this.subirArchivo(e.target.files[0]);
            }
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && this.modal.classList.contains('activo')) {
                this.cerrar();
            }
        });
    }

    abrir(opciones = {}) {
        this.opciones = {...this.opciones, ...opciones};
        this.mediosSeleccionados = [];
        this.paginaActual = 1;

        const selectTipo = this.modal.querySelector('[data-filtro="tipo"]');
        selectTipo.value = this.opciones.tipo || '';

        this.modal.classList.add('activo');
        this.cargarMedios();
        this.actualizarInfo();
    }

    cerrar() {
        this.modal.classList.remove('activo');
        if (this.opciones.onCancelar) {
            this.opciones.onCancelar();
        }
    }

    async cargarMedios() {
        const grilla = document.getElementById('selectorGrillaMedios');
        grilla.innerHTML = '<div class="selectorMediosCargando">Cargando...</div>';

        try {
            const params = new URLSearchParams({
                type: this.opciones.tipo || '',
                page: this.paginaActual,
                format: 'json'
            });

            const response = await fetch(`/admin/media/selector?${params}`, {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });

            const data = await response.json();

            if (data.success) {
                this.medios = data.medios;
                this.totalPaginas = data.pagination.total_pages;
                this.renderizarGrilla();
                this.renderizarPaginacion();
            }
        } catch (error) {
            console.error('Error al cargar medios:', error);
            grilla.innerHTML = '<div class="selectorMediosVacio"><p>Error al cargar los medios</p></div>';
        }
    }

    renderizarGrilla() {
        const grilla = document.getElementById('selectorGrillaMedios');

        if (this.medios.length === 0) {
            grilla.innerHTML = `
                <div class="selectorMediosVacio">
                    <p>No se encontraron archivos</p>
                    <button type="button" class="botonSecundario" data-accion="subir">Subir uno nuevo</button>
                </div>
            `;
            return;
        }

        grilla.innerHTML = this.medios
            .map(medio => {
                const esImagen = medio.mime_type.startsWith('image/');
                const nombre = medio.metadata?.original_name || 'Archivo';
                const seleccionado = this.mediosSeleccionados.some(m => m.id === medio.id);

                let miniaturaHtml = '';
                if (esImagen) {
                    miniaturaHtml = `<img src="${medio.full_url}" alt="${nombre}">`;
                } else if (medio.mime_type.startsWith('audio/')) {
                    miniaturaHtml = '<div class="selectorPlaceholder tipoAudio">🎵</div>';
                } else if (medio.mime_type.startsWith('video/')) {
                    miniaturaHtml = '<div class="selectorPlaceholder tipoVideo">🎬</div>';
                } else {
                    miniaturaHtml = '<div class="selectorPlaceholder tipoDocumento">📄</div>';
                }

                return `
                <div class="selectorItemMedio ${seleccionado ? 'seleccionado' : ''}" 
                     data-id="${medio.id}"
                     data-url="${medio.full_url}"
                     data-nombre="${nombre}"
                     data-mime="${medio.mime_type}">
                    <div class="selectorMiniatura">
                        ${miniaturaHtml}
                        <span class="checkSeleccion">✓</span>
                    </div>
                    <div class="selectorNombre" title="${nombre}">
                        ${nombre.substring(0, 20)}${nombre.length > 20 ? '...' : ''}
                    </div>
                </div>
            `;
            })
            .join('');

        grilla.querySelectorAll('.selectorItemMedio').forEach(item => {
            item.addEventListener('click', () => this.toggleSeleccion(item));
        });
    }

    toggleSeleccion(item) {
        const id = parseInt(item.dataset.id);
        const medio = {
            id: id,
            url: item.dataset.url,
            nombre: item.dataset.nombre,
            mime: item.dataset.mime
        };

        if (this.opciones.multiple) {
            const index = this.mediosSeleccionados.findIndex(m => m.id === id);
            if (index > -1) {
                this.mediosSeleccionados.splice(index, 1);
                item.classList.remove('seleccionado');
            } else {
                this.mediosSeleccionados.push(medio);
                item.classList.add('seleccionado');
            }
        } else {
            document.querySelectorAll('.selectorItemMedio.seleccionado').forEach(el => {
                el.classList.remove('seleccionado');
            });
            this.mediosSeleccionados = [medio];
            item.classList.add('seleccionado');
        }

        this.actualizarInfo();
    }

    actualizarInfo() {
        const info = document.getElementById('selectorMediosInfo');
        const botonConfirmar = this.modal.querySelector('[data-accion="confirmar"]');

        if (this.mediosSeleccionados.length === 0) {
            info.textContent = 'Ningun archivo seleccionado';
            botonConfirmar.disabled = true;
        } else if (this.mediosSeleccionados.length === 1) {
            info.textContent = this.mediosSeleccionados[0].nombre;
            botonConfirmar.disabled = false;
        } else {
            info.textContent = `${this.mediosSeleccionados.length} archivos seleccionados`;
            botonConfirmar.disabled = false;
        }
    }

    renderizarPaginacion() {
        const contenedor = document.getElementById('selectorPaginacion');

        if (this.totalPaginas <= 1) {
            contenedor.innerHTML = '';
            return;
        }

        let html = '';

        html += `<button ${this.paginaActual === 1 ? 'disabled' : ''} data-pagina="${this.paginaActual - 1}">Anterior</button>`;

        for (let i = 1; i <= this.totalPaginas; i++) {
            if (i === this.paginaActual) {
                html += `<button class="paginaActual" disabled>${i}</button>`;
            } else if (i === 1 || i === this.totalPaginas || Math.abs(i - this.paginaActual) <= 2) {
                html += `<button data-pagina="${i}">${i}</button>`;
            } else if (Math.abs(i - this.paginaActual) === 3) {
                html += `<button disabled>...</button>`;
            }
        }

        html += `<button ${this.paginaActual === this.totalPaginas ? 'disabled' : ''} data-pagina="${this.paginaActual + 1}">Siguiente</button>`;

        contenedor.innerHTML = html;

        contenedor.querySelectorAll('button[data-pagina]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.paginaActual = parseInt(btn.dataset.pagina);
                this.cargarMedios();
            });
        });
    }

    async subirArchivo(archivo) {
        const formData = new FormData();
        formData.append('file', archivo);

        try {
            const response = await fetch('/admin/media/upload', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.cargarMedios();
            } else {
                alert('Error al subir: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al subir el archivo');
        }

        document.getElementById('selectorInputSubir').value = '';
    }

    confirmar() {
        if (this.mediosSeleccionados.length === 0) return;

        if (this.opciones.onSeleccionar) {
            if (this.opciones.multiple) {
                this.opciones.onSeleccionar(this.mediosSeleccionados);
            } else {
                this.opciones.onSeleccionar(this.mediosSeleccionados[0]);
            }
        }

        this.cerrar();
    }
}

/* Instancia global para uso simple */
window.SelectorMedios = SelectorMedios;

/* Funcion helper para uso rapido */
window.abrirSelectorMedios = function (opciones) {
    const selector = new SelectorMedios(opciones);
    selector.abrir(opciones);
    return selector;
};
