document.addEventListener('DOMContentLoaded', function () {
    // --- Selección de Elementos del DOM ---
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const uploadMessages = document.getElementById('uploadMessages');
    const mediaGallery = document.getElementById('mediaGallery');

    // --- Eventos para activar la subida de archivos ---
    uploadZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            uploadFiles(fileInput.files);
        }
    });

    // --- Lógica de Arrastrar y Soltar (Drag and Drop) ---
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
        // CORRECCIÓN: Se comprueba la propiedad correcta 'tipomime'.
        const isImage = item.tipomime && item.tipomime.startsWith('image/');

        const previewHtml = isImage ? `<img src="${item.url_publica}" class="mediaImage" alt="${item.titulo}">` : `<div class="mediaIcon"><span>FILE</span></div>`;

        const itemWrapper = document.createElement('div');
        itemWrapper.className = 'galleryItem';

        itemWrapper.innerHTML = `
      <div class="mediaCard" data-id="${item.id}">
        ${previewHtml}
        <div class="mediaBody">
          <p class="mediaTitle" title="${item.titulo}">${item.titulo}</p>
        </div>
      </div>
    `;
        // Insertar el nuevo elemento al principio del contenedor de la galería
        mediaGallery.insertAdjacentElement('afterbegin', itemWrapper);
    }
});