<?php
// Asumimos que la función partial() y assetService() son parte de tu framework.
// Si no lo son, este es un ejemplo de cómo podrías estructurar tu vista.

$tituloPagina = 'Biblioteca de Medios';

// Incluir el encabezado de la página de administración
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);

// Capturamos el JavaScript para encolarlo correctamente en el footer.
ob_start();
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        uploadZone.addEventListener('drop', (e) => {
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
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    progressBar.style.width = percent.toFixed(2) + '%';
                    progressBar.textContent = percent.toFixed(2) + '%';
                    progressBar.setAttribute('aria-valuenow', percent);
                }
            };

            // Evento cuando la subida se completa (exitosa o no)
            xhr.onload = function() {
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
            xhr.onerror = function() {
                uploadProgress.style.display = 'none';
                uploadMessages.innerHTML = `<div class="alertMessage error">Error de red al intentar subir los archivos.</div>`;
            };

            xhr.send(formData);
        }

        // --- Función para renderizar un nuevo elemento en la galería ---
        function renderMediaItem(item) {
            const isImage = item.tipo_mime.startsWith('image/');

            const previewHtml = isImage ?
                `<img src="${item.url_publica}" class="mediaImage" alt="${item.titulo}">` :
                `<div class="mediaIcon"><span>FILE</span></div>`;

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
</script>
<?php
$scriptContenido = ob_get_clean();
// Encolar el script usando el servicio de assets de tu framework
assetService()->agregarJsEnLinea($scriptContenido);
?>

<div class="pageContainer">
    <main class="bloque mainMediaContainer">

        <div class="uploadSection">
            <h3 class="sectionTitle">Subir Nuevo Archivo</h3>
            <input type="file" id="fileInput" multiple style="display: none;" aria-hidden="true">

            <div id="uploadZone" class="uploadArea">
                <p>Arrastra y suelta archivos aquí, o haz clic para seleccionarlos.</p>
            </div>

            <div id="uploadProgress" class="progressContainer" style="display: none;">
                <div id="progressBar" class="progressBar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>

            <div id="uploadMessages" class="messagesContainer"></div>
        </div>

        <hr class="separator">

        <div class="mediaLibrary">
            <h3 class="sectionTitle">Biblioteca de Medios</h3>
            <div id="mediaGallery" class="galleryGrid">
                <?php if (isset($mediaItems) && !$mediaItems->isEmpty()): ?>
                    <?php foreach ($mediaItems as $item): ?>
                        <div class="galleryItem">
                            <div class="mediaCard" data-id="<?= htmlspecialchars($item->id) ?>">
                                <?php if (strpos($item->tipo_mime, 'image/') === 0): ?>
                                    <img src="<?= htmlspecialchars($item->url_publica) ?>" class="mediaImage" alt="<?= htmlspecialchars($item->titulo) ?>">
                                <?php else: ?>
                                    <div class="mediaIcon"><span>FILE</span></div>
                                <?php endif; ?>
                                <div class="mediaBody">
                                    <p class="mediaTitle" title="<?= htmlspecialchars($item->titulo) ?>"><?= htmlspecialchars($item->titulo) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="fullWidth">
                        <p>No se han encontrado medios en la biblioteca. Sube uno nuevo para empezar.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

<style>
    /* --- Estructura y Layout --- */
    .pageContainer {
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
    }

    .mainMediaContainer {
        padding: 20px;
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    .uploadSection {}

    .mediaLibrary {}

    .separator {
        border: 0;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    .sectionTitle {
        margin-bottom: 12px;
        font-weight: 500;
        line-height: 1.2;
        font-size: 13px;
    }

    .fullWidth {
        width: 100%;
    }

    /* --- Zona de Subida (Drag & Drop) --- */
    .uploadArea {
        border: var(--borde);
        border-radius: var(--radius);
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.3s, background-color 0.3s;
    }

    .uploadArea p {
        margin: 0;
        color: #6c757d;
    }

    .uploadArea.dragover {
        background-color: #e9ecef;
        border-color: #0d6efd;
    }

    /* --- Barra de Progreso --- */
    .progressContainer {
        margin-top: 1rem;
        display: flex;
        height: 1.5rem;
        overflow: hidden;
        font-size: .75rem;
        background-color: #e9ecef;
        border-radius: .375rem;
    }

    .progressBar {
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        background-color: #0d6efd;
        transition: width .6s ease;
    }

    /* --- Contenedor de Mensajes --- */
    .messagesContainer {
        margin-top: 1rem;
    }

    .alertMessage {
        position: relative;
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: .375rem;
    }

    .alertMessage.success {
        color: #0f5132;
        background-color: #d1e7dd;
        border-color: #badbcc;
    }

    .alertMessage.error {
        color: #842029;
        background-color: #f8d7da;
        border-color: #f5c2c7;
    }

    /* --- Galería de Medios --- */
    .galleryGrid {
        display: flex;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
    }

    .galleryItem {
        position: relative;
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
        margin-bottom: 1.5rem;
        box-sizing: border-box;
    }

    /* Grid responsivo */
    @media (min-width: 576px) {
        .galleryItem {
            width: 50%;
        }
    }

    @media (min-width: 768px) {
        .galleryItem {
            width: 25%;
        }
    }

    @media (min-width: 992px) {
        .galleryItem {
            width: 25%;
        }
    }

    @media (min-width: 1200px) {
        .galleryItem {
            width: 25%;
        }
    }

    /* --- Tarjeta de Elemento Media --- */
    .mediaCard {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        height: 100%;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: var(--borde);
        overflow: hidden;
    }

    .mediaImage {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }

    .mediaIcon {
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        color: #6c757d;
    }

    .mediaIcon span {
        font-size: 2rem;
        font-weight: bold;
        font-family: monospace;
    }

    .mediaBody {
        flex: 1 1 auto;
        padding: 0.5rem;
        background-color: var(--fondo);
    }

    .mediaTitle {
        margin: 0;
        font-size: 0.875em;
        /* Truncar texto */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<?php
// Incluir el pie de página de la página de administración
echo partial('layouts/admin-footer', []);
?>