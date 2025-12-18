import React, {useState, useCallback} from 'react';
import {Media} from '../../types';
import {Button} from '../ui/Button';
import {Select} from '../form/Select';
import {FileText, Music, Video} from 'lucide-react';

/* Hooks */
import {useMediaFetch, useFileUpload} from '../../hooks';

/* Componentes */
import {MediaDetailsPanel} from './MediaDetailsPanel';

interface MediaLibraryProps {
    onSelect?: (media: Media) => void;
    multiSelect?: boolean;
    className?: string;
    embedded?: boolean;
}

export const MediaLibrary = ({onSelect, multiSelect, className = '', embedded = false}: MediaLibraryProps) => {
    /* Estado de seleccion */
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [selectedMedia, setSelectedMedia] = useState<Media | null>(null);
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');

    /* Hook de fetch de medios */
    const {files, loading, pagination, filterType, search, page, setFilterType, setSearch, setPage, refetch} = useMediaFetch();

    /* Hook de upload */
    const {isDragging, fileInputRef, handleUpload, handleDragOver, handleDragLeave, handleDrop, triggerFileInput} = useFileUpload({
        onUploadComplete: refetch
    });

    /* Handlers */
    const handleItemClick = useCallback(
        (media: Media) => {
            setSelectedId(media.id);
            setSelectedMedia(media);
            if (onSelect) {
                onSelect(media);
            }
        },
        [onSelect]
    );

    const cerrarDetalles = useCallback(() => {
        setSelectedId(null);
        setSelectedMedia(null);
    }, []);

    const getIconForType = (mime: string) => {
        if (mime.startsWith('image/')) return null;
        if (mime.startsWith('video/')) return <Video className="iconoTipo" />;
        if (mime.startsWith('audio/')) return <Music className="iconoTipo" />;
        return <FileText className="iconoTipo" />;
    };

    return (
        <div className={`contenedorMedios ${className}`} id="contenedorMedios">
            {/* Barra de herramientas */}
            <div className="barraHerramientas">
                <div className="barraHerramientasIzquierda">
                    <Button variant="primary" id="botonSubirArchivo" onClick={triggerFileInput}>
                        + Subir archivo
                    </Button>
                    <input type="file" ref={fileInputRef} id="inputArchivoOculto" className="oculto" onChange={e => handleUpload(e.target.files)} multiple accept="image/*,audio/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx" />
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

            {/* Zona de drop */}
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

            {/* Grilla de medios */}
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

            {/* Paginacion */}
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

            {/* Panel de detalles */}
            {selectedMedia && <MediaDetailsPanel media={selectedMedia} onClose={cerrarDetalles} />}
        </div>
    );
};
