document.addEventListener('DOMContentLoaded', function () {
    // --- Selección de Elementos del DOM ---
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const uploadMessages = document.getElementById('uploadMessages');
    const mediaGallery = document.getElementById('mediaGallery');

    // --- Eventos para activar la subida de archivos ---
    if (uploadZone) {
        uploadZone.addEventListener('click', () => fileInput.click());
    }
    if (fileInput) {
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                uploadFiles(fileInput.files);
            }
        });
    }


    // --- Lógica de Arrastrar y Soltar (Drag and Drop) ---
    if (uploadZone) {
        uploadZone.addEventListener('dragover', e => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        uploadZone.addEventListener('drop', e => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files; // Asigna los archivos al input
                uploadFiles(e.dataTransfer.files);
            }
        });
    }

    // --- Función Principal de Subida con XMLHttpRequest ---
    function uploadFiles(files) {
        const formData = new FormData();
        for (const file of files) {
            formData.append('archivos[]', file);
        }

        // Reiniciar UI de progreso y mensajes
        uploadMessages.innerHTML = '';
        uploadProgress.style.display = 'block';
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
        progressBar.setAttribute('aria-valuenow', '0');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/panel/media/subir', true);

        // Evento para monitorear el progreso de la subida
        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                const percent = (e.loaded / e.total) * 100;
                progressBar.style.width = percent.toFixed(2) + '%';
                progressBar.textContent = percent.toFixed(2) + '%';
                progressBar.setAttribute('aria-valuenow', percent);
            }
        };

        // Evento cuando la subida se completa (exitosa o no)
        xhr.onload = function () {
            uploadProgress.style.display = 'none'; // Ocultar barra de progreso
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.exito && response.media) {
                        uploadMessages.innerHTML = `<div class="alertMessage success">Archivos subidos correctamente.</div>`;
                        // Añade los nuevos elementos al inicio de la galería
                        response.media.reverse().forEach(renderMediaItem);
                    } else {
                        uploadMessages.innerHTML = `<div class="alertMessage error">Error: ${response.mensaje || 'Respuesta inesperada.'}</div>`;
                    }
                } catch (e) {
                    uploadMessages.innerHTML = `<div class="alertMessage error">Error al procesar la respuesta del servidor.</div>`;
                }
            } else {
                let errorMsg = `Error del servidor: ${xhr.statusText || 'desconocido'}`;
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    errorMsg = errorResponse.mensaje || errorMsg;
                } catch (e) {
                    /* No es JSON, usar statusText */
                }
                uploadMessages.innerHTML = `<div class="alertMessage error">${errorMsg}</div>`;
            }
        };

        // Evento para errores de red
        xhr.onerror = function () {
            uploadProgress.style.display = 'none';
            uploadMessages.innerHTML = `<div class="alertMessage error">Error de red al intentar subir los archivos.</div>`;
        };

        xhr.send(formData);
    }

    // --- Función para renderizar un nuevo elemento en la galería ---
    function renderMediaItem(item) {
        const isImage = item.tipo_mime && item.tipo_mime.startsWith('image/');

        const previewHtml = isImage ? `<img src="${item.url_publica}" class="mediaImage" alt="${item.titulo}">` : `<div class="mediaIcon"><span>FILE</span></div>`;

        const itemWrapper = document.createElement('div');
        itemWrapper.className = 'galleryItem';

        itemWrapper.innerHTML = `
      <div class="mediaCard" data-id="${item.id}">
        ${previewHtml}
        <div class="mediaBody">
          <p class="mediaTitle" title="${item.titulo}">${item.titulo}</p>
        </div>
        <div class="mediaActions">
            <button type="button" class="btnN btnVer" data-id="${item.id}">Ver</button>
            <button type="button" class="btnN IconoRojo" onclick="eliminarRecurso('/panel/media/destroy/${item.id}', '<?= csrf_token() ?? '' ?>', '¿Estás seguro de que quieres eliminar este archivo? Esta acción es permanente.')">Eliminar</button>
        </div>
      </div>
    `;
        if (mediaGallery) {
            // Elimina el mensaje "No se han encontrado medios..." si existe
            const noMediaMessage = mediaGallery.querySelector('.fullWidth');
            if (noMediaMessage) {
                noMediaMessage.remove();
            }
            mediaGallery.insertAdjacentElement('afterbegin', itemWrapper);
        }
    }

    // --- [+] NUEVO: Lógica para el Modal de "Ver" ---
    const modalVerMedia = document.getElementById('modalVerMedia');
    if (modalVerMedia && mediaGallery) {
        const cerrarModalVerMedia = document.getElementById('cerrarModalVerMedia');
        const cerrarModalVerMediaBtn = document.getElementById('cerrarModalVerMediaBtn');
        const modalVerMediaContenido = document.getElementById('modalVerMediaContenido');

        const abrirModalVer = async (mediaId) => {
            modalVerMedia.style.display = 'flex';
            modalVerMediaContenido.innerHTML = '<p>Cargando detalles...</p>';

            try {
                const respuesta = await fetch(`/panel/ajax/obtener-media-info/${mediaId}`);
                if (!respuesta.ok) {
                    throw new Error(`Error de red: ${respuesta.statusText}`);
                }

                const datos = await respuesta.json();
                if (datos.exito && datos.media) {
                    renderizarDetallesMedia(datos.media);
                } else {
                    throw new Error(datos.mensaje || 'No se pudieron cargar los datos.');
                }
            } catch (error) {
                modalVerMediaContenido.innerHTML = `<p style="color: red;">${error.message}</p>`;
                console.error(error);
            }
        };

        const cerrarModal = () => {
            modalVerMedia.style.display = 'none';
            modalVerMediaContenido.innerHTML = '';
        };

        const renderizarDetallesMedia = (media) => {
            const metadata = media.metadata || {};
            const tamaño = metadata.tamaño_bytes ? `${(metadata.tamaño_bytes / 1024).toFixed(2)} KB` : 'No disponible';
            const fechaSubida = media.created_at ? new Date(media.created_at).toLocaleString('es-ES') : 'No disponible';

            const contenidoHTML = `
                <div class="media-preview">
                    ${media.tipomime && media.tipomime.startsWith('image/')
                        ? `<img src="${media.url_publica}" alt="${media.textoalternativo || media.titulo}">`
                        : `<div class="mediaIcon" style="height: 200px;"><span>${media.tipomime || 'FILE'}</span></div>`
                    }
                </div>
                <div class="media-details">
                    <p><strong>ID:</strong> ${media.id}</p>
                    <p><strong>Título:</strong><br><input type="text" value="${media.titulo || ''}" readonly></p>
                    <p><strong>Texto alternativo:</strong><br><input type="text" value="${media.textoalternativo || ''}" readonly></p>
                    <p><strong>Leyenda:</strong><br><input type="text" value="${media.leyenda || ''}" readonly></p>
                    <p><strong>URL del archivo:</strong><br><input type="text" value="${media.url_publica}" readonly onclick="this.select()"></p>
                    <p><strong>Tipo de archivo:</strong> ${media.tipomime}</p>
                    <p><strong>Tamaño:</strong> ${tamaño}</p>
                    <p><strong>Subido el:</strong> ${fechaSubida}</p>
                </div>
            `;
            modalVerMediaContenido.innerHTML = contenidoHTML;
        };

        mediaGallery.addEventListener('click', (e) => {
            const botonVer = e.target.closest('.btnVer');
            if (botonVer) {
                e.preventDefault();
                const mediaId = botonVer.dataset.id;
                abrirModalVer(mediaId);
            }
        });

        cerrarModalVerMedia.addEventListener('click', cerrarModal);
        cerrarModalVerMediaBtn.addEventListener('click', cerrarModal);
        modalVerMedia.addEventListener('click', (e) => {
            if (e.target === modalVerMedia) {
                cerrarModal();
            }
        });
    }
});