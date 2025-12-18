import React, {useEffect, useState, useRef} from 'react';
import {Media, Pagination} from '../../types';
import {Button} from '../ui/Button';
import {Select} from '../form/Select';
import {FileText, Music, Video} from 'lucide-react';

interface MediaLibraryProps {
    onSelect?: (media: Media) => void;
    multiSelect?: boolean; // Not fully implemented in this version
    className?: string;
    embedded?: boolean; // If true, adjusts layout for embedding in a page
}

export const MediaLibrary = ({onSelect, multiSelect, className = '', embedded = false}: MediaLibraryProps) => {
    const [files, setFiles] = useState<Media[]>([]);
    const [loading, setLoading] = useState(false);
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
    const [pagination, setPagination] = useState<Pagination | null>(null);

    // Filters
    const [filterType, setFilterType] = useState('');
    const [search, setSearch] = useState('');
    const [page, setPage] = useState(1);

    // Upload
    const [isDragging, setIsDragging] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    // Selection
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [selectedMedia, setSelectedMedia] = useState<Media | null>(null);

    useEffect(() => {
        fetchMedia();
    }, [page, filterType, search]); // Debounce search in real app

    const fetchMedia = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams({
                format: 'json',
                page: page.toString(),
                type: filterType,
                search: search
            });
            const res = await fetch(`/admin/media/selector?${params}`);
            const data = await res.json();

            if (data.success) {
                setFiles(data.medios);
                setPagination(data.pagination);
            }
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
        }
    };

    const handleUpload = async (fileList: FileList | null) => {
        if (!fileList || fileList.length === 0) return;

        const formData = new FormData();
        formData.append('file', fileList[0]); // Handle single file for now

        try {
            setLoading(true);
            const res = await fetch('/admin/media/upload', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                // Refresh list or prepend
                fetchMedia();
                if (onSelect) {
                    // Optionally auto-select uploaded file
                }
            } else {
                alert(data.message || 'Error uploading file');
            }
        } catch (e) {
            alert('Upload failed');
        } finally {
            setLoading(false);
        }
    };

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(true);
    };

    const handleDragLeave = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(false);
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(false);
        handleUpload(e.dataTransfer.files);
    };

    const handleItemClick = (media: Media) => {
        setSelectedId(media.id);
        setSelectedMedia(media);
        if (onSelect) {
            onSelect(media);
        }
    };

    const cerrarDetalles = () => {
        setSelectedId(null);
        setSelectedMedia(null);
    };

    const formatBytes = (bytes: number) => {
        if (bytes > 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        return (bytes / 1024).toFixed(2) + ' KB';
    };

    const getIconForType = (mime: string) => {
        if (mime.startsWith('image/')) return null; // Use thumbnail
        if (mime.startsWith('video/')) return <Video className="iconoTipo" />;
        if (mime.startsWith('audio/')) return <Music className="iconoTipo" />;
        return <FileText className="iconoTipo" />;
    };

    return (
        <div className={`contenedorMedios ${className}`}>
            <div className="barraHerramientas">
                <div className="barraHerramientasIzquierda">
                    <Button variant="primary" id="botonSubirArchivo" onClick={() => fileInputRef.current?.click()}>
                        + Subir archivo
                    </Button>
                    <input type="file" ref={fileInputRef} id="inputArchivoOculto" style={{display: 'none'}} onChange={e => handleUpload(e.target.files)} multiple accept="image/*,audio/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx" />
                    <Button variant={viewMode === 'grid' ? 'primary' : 'secondary'} onClick={() => setViewMode('grid')} className="botonToggleVista" id="botonVistaGrilla" title="Vista grilla">
                        &#9638;
                    </Button>
                    <Button variant={viewMode === 'list' ? 'primary' : 'secondary'} onClick={() => setViewMode('list')} className="botonToggleVista" id="botonVistaLista" title="Vista lista">
                        &#9776;
                    </Button>
                </div>

                <div className="barraHerramientasDerecha">
                    <form className="formularioFiltros" id="formularioFiltrosMedios" onSubmit={e => e.preventDefault()}>
                        <Select
                            value={filterType}
                            onChange={e => setFilterType(e.target.value)}
                            className="selectFiltro"
                            options={[
                                {label: 'Todos los tipos', value: ''},
                                {label: 'Imágenes', value: 'image'},
                                {label: 'Videos', value: 'video'},
                                {label: 'Audio', value: 'audio'},
                                {label: 'Documentos', value: 'application'}
                            ]}
                        />
                        <div className="grupoBusqueda">
                            <input type="text" value={search} onChange={e => setSearch(e.target.value)} placeholder="Buscar por nombre..." className="inputBusqueda" />
                            <button type="submit" className="botonBuscar">
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div className={`zonaDropMedios ${isDragging ? 'zonaDropActiva' : ''}`} onDragOver={handleDragOver} onDragLeave={handleDragLeave} onDrop={handleDrop}>
                <span className="zonaDropIcono">📂</span>
                <p className="zonaDropContenido">Arrastra archivos aquí para subirlos</p>
            </div>

            {loading && <div className="cargando">Cargando medios...</div>}

            {!loading && files.length === 0 && (
                <div className="mediosVacio">
                    <p>No se encontraron archivos.</p>
                </div>
            )}

            <div className={`grillaMedios ${viewMode === 'grid' ? 'vistaGrilla' : 'vistaLista'}`}>
                {files.map(file => (
                    <div key={file.id} className={`itemMedio ${selectedId === file.id ? 'seleccionado' : ''}`} onClick={() => handleItemClick(file)}>
                        <div className={`miniaturaContenedor ${file.mime_type.startsWith('audio/') ? 'tipoAudio' : file.mime_type.startsWith('video/') ? 'tipoVideo' : !file.mime_type.startsWith('image/') ? 'tipoDocumento' : ''}`}>
                            {file.mime_type.startsWith('image/') ? <img src={`/${file.path}`} alt={file.metadata?.alt_text || file.metadata?.original_name} className="miniaturaMedio" /> : <div className="miniaturaPlaceholder">{getIconForType(file.mime_type)}</div>}

                            {onSelect && (
                                <div className="overlayMedio">
                                    <div className="indicadorSeleccion">✓</div>
                                </div>
                            )}
                        </div>

                        <div className="infoMedio">
                            <div className="nombreMedio" title={file.metadata?.original_name}>
                                {file.metadata?.original_name}
                            </div>
                            <div className="metaMedio">{(file.metadata?.size_bytes / 1024).toFixed(1)} KB</div>
                        </div>
                    </div>
                ))}
            </div>

            {pagination && pagination.total_pages > 1 && (
                <div className="selectorPaginacion">
                    <Button disabled={page === 1} onClick={() => setPage(p => p - 1)} variant="secondary">
                        Anterior
                    </Button>
                    <span className="paginacionInfo">
                        {page} de {pagination.total_pages}
                    </span>
                    <Button disabled={page === pagination.total_pages} onClick={() => setPage(p => p + 1)} variant="secondary">
                        Siguiente
                    </Button>
                </div>
            )}

            {selectedMedia && (
                <div id="panelDetallesMedio" className="panelDetallesMedio">
                    <div className="encabezadoDetalles">
                        <h3>Detalles del archivo</h3>
                        <button type="button" className="botonCerrarDetalles" onClick={cerrarDetalles}>
                            ×
                        </button>
                    </div>
                    <div className="contenidoDetalles">
                        <div className="previewDetalles" id="previewDetalles">
                            {selectedMedia.mime_type.startsWith('image/') ? <img src={`/${selectedMedia.path}`} alt={selectedMedia.metadata?.alt_text || selectedMedia.metadata?.original_name} /> : <div className="miniaturaPlaceholder">{getIconForType(selectedMedia.mime_type)}</div>}
                        </div>
                        <form id="formDetallesMedio" className="formDetallesMedio">
                            <input type="hidden" id="detalleMediaId" name="id" value={selectedMedia.id} />

                            <div className="grupoFormulario">
                                <label htmlFor="detalleTitulo">Titulo</label>
                                <input type="text" id="detalleTitulo" name="title" className="inputFormulario" defaultValue={selectedMedia.metadata?.title || ''} />
                            </div>

                            <div className="grupoFormulario">
                                <label htmlFor="detalleAltText">Texto alternativo</label>
                                <input type="text" id="detalleAltText" name="alt_text" className="inputFormulario" defaultValue={selectedMedia.metadata?.alt_text || ''} />
                            </div>

                            <div className="grupoFormulario">
                                <label htmlFor="detalleDescripcion">Descripcion</label>
                                <textarea id="detalleDescripcion" name="description" className="inputFormulario textareaFormulario" rows={3} defaultValue={selectedMedia.metadata?.description || ''} />
                            </div>

                            <div className="infoArchivoDetalles">
                                <p>
                                    <strong>Nombre:</strong> <span id="detalleNombre">{selectedMedia.metadata?.original_name}</span>
                                </p>
                                <p>
                                    <strong>Tipo:</strong> <span id="detalleTipo">{selectedMedia.mime_type}</span>
                                </p>
                                <p>
                                    <strong>Tamano:</strong> <span id="detalleTamano">{formatBytes(selectedMedia.metadata?.size_bytes || 0)}</span>
                                </p>
                                <p>
                                    <strong>Subido:</strong> <span id="detalleFecha">{new Date(selectedMedia.created_at).toLocaleString('es-ES')}</span>
                                </p>
                            </div>

                            <div className="grupoFormulario">
                                <label>URL del archivo</label>
                                <div className="grupoUrlCopiar">
                                    <input type="text" id="detalleUrl" className="inputFormulario" readOnly value={`${window.location.origin}/${selectedMedia.path}`} />
                                    <button type="button" className="botonSecundario" onClick={() => navigator.clipboard.writeText(`${window.location.origin}/${selectedMedia.path}`)}>
                                        Copiar
                                    </button>
                                </div>
                            </div>

                            <div className="accionesDetalles">
                                <button type="submit" className="botonPrimario">
                                    Guardar cambios
                                </button>
                                <button type="button" className="botonPeligro">
                                    Eliminar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};
