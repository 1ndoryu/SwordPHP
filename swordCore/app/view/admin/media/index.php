<?php
// Asumimos que la función partial() y assetService() son parte de tu framework.
// Si no lo son, este es un ejemplo de cómo podrías estructurar tu vista.

$tituloPagina = 'Biblioteca de Medios';

// Incluir el encabezado de la página de administración
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);

// Capturamos el JavaScript para encolarlo correctamente en el footer.
ob_start();
?>

<?php
$scriptContenido = ob_get_clean();
// Encolar el script usando el servicio de assets de tu framework
assetService()->agregarJsEnLinea($scriptContenido);
?>

<div class="pageContainer">
    <main class="bloque mainMediaContainer">

        <?php // [+] NUEVO: Bloque para mostrar mensajes flash de la sesión.
            $session = request()->session();
            $exito = $session->pull('exito');
            $error = $session->pull('error');
        ?>

        <?php if ($exito): ?>
            <div class="alertMessage success" style="margin-bottom: 1rem;"><?= htmlspecialchars($exito) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alertMessage error" style="margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

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
                                <?php if (!empty($item->tipomime) && strpos($item->tipomime, 'image/') === 0): ?>
                                    <img src="<?= htmlspecialchars($item->url_publica) ?>" class="mediaImage" alt="<?= htmlspecialchars($item->titulo) ?>">
                                <?php else: ?>
                                    <div class="mediaIcon"><span>FILE</span></div>
                                <?php endif; ?>
                                <div class="mediaBody">
                                    <p class="mediaTitle" title="<?= htmlspecialchars($item->titulo) ?>"><?= htmlspecialchars($item->titulo) ?></p>
                                </div>
                                
                                <div class="mediaActions">
                                    <button type="button" class="btnN btnVer" data-id="<?= htmlspecialchars($item->id) ?>">Ver</button>
                                    <button type="button" class="btnN IconoRojo" onclick="eliminarRecurso('/panel/media/destroy/<?= $item->id ?>', '<?= csrf_token() ?? '' ?>', '¿Estás seguro de que quieres eliminar este archivo? Esta acción es permanente.')">Eliminar</button>
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

<div id="modalVerMedia" class="blque modal modal-sword" style="display: none;">
    <div class="modal-contenido" style="max-width: 800px; width: 100%;">
        <div class="modal-cabecera">
            <h3 id="modalVerMediaTitulo">Detalles del Medio</h3>
            <span class="modal-cerrar" id="cerrarModalVerMedia">&times;</span>
        </div>
        <div class="modal-cuerpo" id="modalVerMediaContenido">
            <p>Cargando...</p>
        </div>
        <div class="modal-pie">
             <button type="button" class="btnN" id="cerrarModalVerMediaBtn">Cerrar</button>
        </div>
    </div>
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

    /* Estilos para Acciones en Hover y Modal */
    .mediaActions {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
        z-index: 2;
    }

    .mediaCard:hover .mediaActions {
        opacity: 1;
        visibility: visible;
    }

    #modalVerMedia .modal-cuerpo {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    #modalVerMedia .media-preview {
        flex: 1;
        min-width: 300px;
    }

    #modalVerMedia .media-preview img {
        max-width: 100%;
        height: auto;
        border-radius: var(--radius);
    }

    #modalVerMedia .media-details {
        flex: 1;
        min-width: 300px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    #modalVerMedia .media-details p {
        margin: 0;
        font-size: 13px;
        word-break: break-all;
    }

    #modalVerMedia .media-details strong {
        color: #333;
    }

    #modalVerMedia .media-details input {
        width: 100%;
        padding: 5px;
        font-size: 13px;
        border: var(--borde);
        border-radius: var(--radius);
        background-color: #f4f4f4;
    }

    span.modal-cerrar {
        cursor: pointer;
    }
</style>

<?php
// Incluir el pie de página de la página de administración
echo partial('layouts/admin-footer', []);
?>