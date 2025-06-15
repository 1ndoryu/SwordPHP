<?php
include __DIR__ . '/../../layouts/admin-header.php';
?>

<style>
    #mediaDropZone {
        border: 2px dashed #ccc;
        border-radius: 5px;
        padding: 40px;
        text-align: center;
        color: #777;
        cursor: pointer;
        transition: border-color 0.3s, background-color 0.3s;
    }

    #mediaDropZone.dragover {
        border-color: #007bff;
        background-color: #f0f8ff;
    }

    .upload-progress-item {
        margin-bottom: 5px;
        padding: 8px;
        background: #f9f9f9;
        border-radius: 3px;
        font-size: 0.9em;
    }

    .media-card-img {
        object-fit: cover;
        height: 150px;
    }

    .media-card-icon {
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        font-size: 48px;
        color: #6c757d;
    }
</style>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Biblioteca de Medios</h5>
        <button id="anadirNuevoMedio" class="btn btn-success">Añadir nuevo</button>
    </div>
    <div class="card-body">
        <input type="file" id="mediaUploadInput" multiple style="display: none;" accept="image/*,application/pdf,video/mp4">

        <div id="mediaDropZone">
            <p>Arrastra y suelta archivos aquí para subirlos, o haz clic para seleccionarlos.</p>
        </div>

        <div id="uploadStatus" class="mt-3"></div>

        <hr>

        <div id="mediaGaleriaContenedor" class="row">
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const anadirBtn = document.getElementById('anadirNuevoMedio');
        const uploadInput = document.getElementById('mediaUploadInput');
        const dropZone = document.getElementById('mediaDropZone');
        const uploadStatus = document.getElementById('uploadStatus');
        const galeriaContenedor = document.getElementById('mediaGaleriaContenedor');

        // Abre el selector de archivos al hacer clic en el botón o la zona de drop
        anadirBtn.addEventListener('click', () => uploadInput.click());
        dropZone.addEventListener('click', () => uploadInput.click());

        // Gestiona los archivos seleccionados desde el input
        uploadInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                gestionarArchivos(e.target.files);
            }
        });

        // --- Lógica de Arrastrar y Soltar (Drag and Drop) ---
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                gestionarArchivos(files);
            }
        });

        // Evita que el navegador abra el archivo si se suelta fuera de la zona
        ['dragover', 'drop'].forEach(eventName => {
            window.addEventListener(eventName, e => e.preventDefault());
        });


        // --- Función para gestionar y subir los archivos ---
        function gestionarArchivos(files) {
            uploadStatus.innerHTML = ''; // Limpia los estados anteriores
            const formData = new FormData();

            for (const file of files) {
                formData.append('archivos[]', file);
                const statusItem = document.createElement('div');
                statusItem.className = 'upload-progress-item';
                statusItem.textContent = `Preparando: ${file.name}`;
                uploadStatus.appendChild(statusItem);
            }

            // Muestra un mensaje de subiendo
            uploadStatus.insertAdjacentHTML('afterbegin', '<div class="alert alert-info">Subiendo archivos...</div>');

            fetch('/panel/media/subir', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.mensaje || 'Error en la respuesta del servidor.')
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    uploadStatus.innerHTML = ''; // Limpia el estado
                    if (data.exito) {
                        uploadStatus.innerHTML = `<div class="alert alert-success">${data.media.length} archivo(s) subido(s) con éxito.</div>`;
                        data.media.forEach(renderizarElementoMedia);
                    } else {
                        throw new Error(data.mensaje || 'Ocurrió un error durante la subida.');
                    }
                })
                .catch(error => {
                    console.error('Error en la subida:', error);
                    uploadStatus.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                });
        }

        // --- Función para renderizar un elemento en la galería ---
        function renderizarElementoMedia(mediaItem) {
            const col = document.createElement('div');
            col.className = 'col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 mb-3';

            const card = document.createElement('div');
            card.className = 'card h-100';

            let previewHtml;
            if (mediaItem.tipo_mime.startsWith('image/')) {
                previewHtml = `<img src="${mediaItem.url_publica}" class="card-img-top media-card-img" alt="${mediaItem.titulo}">`;
            } else {
                // Asumiendo que Font Awesome está disponible en el panel
                previewHtml = `<div class="media-card-icon"><i class="fas fa-file-alt"></i></div>`;
            }

            const cardBodyHtml = `
            <div class="card-footer text-muted p-2">
                <small class="d-block text-truncate" title="${mediaItem.titulo}">${mediaItem.titulo}</small>
            </div>
        `;

            card.innerHTML = previewHtml + cardBodyHtml;
            col.appendChild(card);

            // Añade el nuevo elemento al principio de la galería
            galeriaContenedor.prepend(col);
        }
    });
</script>

<?php
include __DIR__ . '/../../layouts/admin-footer.php';
?>