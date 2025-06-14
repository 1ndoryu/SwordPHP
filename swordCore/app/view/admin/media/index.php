<?php
// Usamos el método de carga de layouts que establecimos.
include __DIR__ . '/../../layouts/admin-header.php';

// Capturamos el JavaScript para encolarlo correctamente en el footer.
ob_start();
?>
document.addEventListener('DOMContentLoaded', function () {
    const zonaSubida = document.getElementById('zonaSubida');
    const inputArchivos = document.getElementById('inputArchivos');
    const progresoSubida = document.getElementById('progresoSubida');
    const barraProgreso = document.getElementById('barraProgreso');
    const mensajesSubida = document.getElementById('mensajesSubida');
    const mediaGaleriaContenedor = document.getElementById('mediaGaleriaContenedor');
    // Se ha eliminado la línea: const bibliotecaTab = new bootstrap.Tab(...);

    // --- Eventos para activar la subida ---
    zonaSubida.addEventListener('click', () => inputArchivos.click());
    inputArchivos.addEventListener('change', () => {
        if (inputArchivos.files.length) {
            subirArchivos(inputArchivos.files);
        }
    });

    // --- Lógica de Arrastrar y Soltar (Drag and Drop) ---
    zonaSubida.addEventListener('dragover', (e) => {
        e.preventDefault();
        zonaSubida.classList.add('dragover');
    });
    zonaSubida.addEventListener('dragleave', () => zonaSubida.classList.remove('dragover'));
    zonaSubida.addEventListener('drop', (e) => {
        e.preventDefault();
        zonaSubida.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            subirArchivos(e.dataTransfer.files);
        }
    });

    // --- Función Principal de Subida ---
    function subirArchivos(archivos) {
        const formData = new FormData();
        for (const archivo of archivos) {
            formData.append('archivos[]', archivo);
        }

        mensajesSubida.innerHTML = '';
        progresoSubida.style.display = 'block';
        barraProgreso.style.width = '0%';
        barraProgreso.textContent = '0%';
        barraProgreso.setAttribute('aria-valuenow', '0');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/panel/media/subir', true);

        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                const porcentaje = (e.loaded / e.total) * 100;
                barraProgreso.style.width = porcentaje.toFixed(2) + '%';
                barraProgreso.textContent = porcentaje.toFixed(2) + '%';
                barraProgreso.setAttribute('aria-valuenow', porcentaje);
            }
        };

        xhr.onload = function () {
            progresoSubida.style.display = 'none';
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.exito) {
                        mensajesSubida.innerHTML = `<div class="alert alert-success">Archivos subidos correctamente.</div>`;
                        response.media.reverse().forEach(renderizarElementoMedia);
                        // Se eliminó la línea: bibliotecaTab.show();
                    } else {
                        mensajesSubida.innerHTML = `<div class="alert alert-danger">Error: ${response.mensaje}</div>`;
                    }
                } catch (e) {
                    mensajesSubida.innerHTML = `<div class="alert alert-danger">Error al procesar la respuesta del servidor.</div>`;
                }
            } else {
                let mensajeError = `Error del servidor: ${xhr.statusText || 'desconocido'}`;
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    mensajeError = errorResponse.mensaje || mensajeError;
                } catch(e) { /* No es JSON, usar el statusText */ }
                mensajesSubida.innerHTML = `<div class="alert alert-danger">${mensajeError}</div>`;
            }
        };

        xhr.onerror = function () {
            progresoSubida.style.display = 'none';
            mensajesSubida.innerHTML = `<div class="alert alert-danger">Error de red al intentar subir los archivos.</div>`;
        };
        
        xhr.send(formData);
    }

    // --- Función para renderizar un elemento en la galería ---
    function renderizarElementoMedia(item) {
        const esImagen = item.tipo_mime.startsWith('image/');
        const previewHtml = esImagen 
            ? `<img src="${item.url_publica}" class="card-img-top media-card-img" alt="${item.titulo}">`
            : `<div class="media-card-icon"><i class="bi bi-file-earmark-text"></i></div>`;

        const itemHtml = `
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
                <div class="card media-item h-100" data-id="${item.id}">
                    ${previewHtml}
                    <div class="card-body p-2">
                        <p class="card-text text-truncate small" title="${item.titulo}">${item.titulo}</p>
                    </div>
                </div>
            </div>
        `;
        mediaGaleriaContenedor.insertAdjacentHTML('afterbegin', itemHtml);
    }
});
<?php
$scriptContenido = ob_get_clean();
assetService()->agregarJsEnLinea($scriptContenido);
?>

<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Medios</h1>
            </div>

            <div class="mb-5">
                <h3 class="mb-3">Subir nuevo archivo</h3>
                <input type="file" id="inputArchivos" multiple style="display: none;">
                <div id="zonaSubida" class="zona-subida-estilo">
                    <p>Arrastra y suelta archivos aquí, o haz clic para seleccionarlos.</p>
                </div>
                <div id="progresoSubida" class="mt-3" style="display: none;">
                    <div class="progress">
                        <div id="barraProgreso" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                </div>
                <div id="mensajesSubida" class="mt-3"></div>
            </div>

            <hr>

            <div class="mt-5">
                <h3 class="mb-3">Biblioteca de medios</h3>
                <div id="mediaGaleriaContenedor" class="row">
                    <?php if (isset($mediaItems) && !$mediaItems->isEmpty()): ?>
                        <?php foreach ($mediaItems as $item): ?>
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
                                <div class="card media-item h-100" data-id="<?= htmlspecialchars($item->id) ?>">
                                    <?php if (strpos($item->tipo_mime, 'image/') === 0): ?>
                                        <img src="<?= htmlspecialchars($item->url_publica) ?>" class="card-img-top media-card-img" alt="<?= htmlspecialchars($item->titulo) ?>">
                                    <?php else: ?>
                                        <div class="media-card-icon"><i class="bi bi-file-earmark-text"></i></div>
                                    <?php endif; ?>
                                    <div class="card-body p-2">
                                        <p class="card-text text-truncate small" title="<?= htmlspecialchars($item->titulo) ?>"><?= htmlspecialchars($item->titulo) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p>No se han encontrado medios en la biblioteca. Sube uno nuevo para empezar.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.zona-subida-estilo {
    border: 2px dashed #ccc;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    background-color: #f8f9fa;
    transition: border-color 0.3s, background-color 0.3s;
}
.zona-subida-estilo.dragover {
    background-color: #e9ecef;
    border-color: #0d6efd;
}
.media-item .media-card-img {
    height: 120px;
    object-fit: cover;
}
.media-item .media-card-icon {
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    font-size: 3rem;
    color: #6c757d;
}
</style>

<?php
include __DIR__ . '/../../layouts/admin-footer.php';
?>