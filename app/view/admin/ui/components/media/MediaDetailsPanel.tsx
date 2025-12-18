import React from 'react';
import {Media} from '../../types';
import {FileText, Music, Video} from 'lucide-react';

interface MediaDetailsPanelProps {
    media: Media;
    onClose: () => void;
}

export const MediaDetailsPanel: React.FC<MediaDetailsPanelProps> = ({media, onClose}) => {
    const formatBytes = (bytes: number) => {
        if (bytes > 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        return (bytes / 1024).toFixed(2) + ' KB';
    };

    const getIconForType = (mime: string) => {
        if (mime.startsWith('image/')) return null;
        if (mime.startsWith('video/')) return <Video className="iconoTipo" />;
        if (mime.startsWith('audio/')) return <Music className="iconoTipo" />;
        return <FileText className="iconoTipo" />;
    };

    return (
        <div id="panelDetallesMedio" className="panelDetallesMedio">
            <div className="encabezadoDetalles">
                <h3>Detalles del archivo</h3>
                <button type="button" className="botonCerrarDetalles" onClick={onClose}>
                    ×
                </button>
            </div>
            <div className="contenidoDetalles">
                <div className="previewDetalles" id="previewDetalles">
                    {media.mime_type.startsWith('image/') ? <img src={`/${media.path}`} alt={media.metadata?.alt_text || media.metadata?.original_name} /> : <div className="miniaturaPlaceholder">{getIconForType(media.mime_type)}</div>}
                </div>
                <form id="formDetallesMedio" className="formDetallesMedio">
                    <input type="hidden" id="detalleMediaId" name="id" value={media.id} />

                    <div className="grupoFormulario">
                        <label htmlFor="detalleTitulo">Titulo</label>
                        <input type="text" id="detalleTitulo" name="title" className="inputFormulario" defaultValue={media.metadata?.title || ''} />
                    </div>

                    <div className="grupoFormulario">
                        <label htmlFor="detalleAltText">Texto alternativo</label>
                        <input type="text" id="detalleAltText" name="alt_text" className="inputFormulario" defaultValue={media.metadata?.alt_text || ''} />
                    </div>

                    <div className="grupoFormulario">
                        <label htmlFor="detalleDescripcion">Descripcion</label>
                        <textarea id="detalleDescripcion" name="description" className="inputFormulario textareaFormulario" rows={3} defaultValue={media.metadata?.description || ''} />
                    </div>

                    <div className="infoArchivoDetalles">
                        <p>
                            <strong>Nombre:</strong> <span id="detalleNombre">{media.metadata?.original_name}</span>
                        </p>
                        <p>
                            <strong>Tipo:</strong> <span id="detalleTipo">{media.mime_type}</span>
                        </p>
                        <p>
                            <strong>Tamano:</strong> <span id="detalleTamano">{formatBytes(media.metadata?.size_bytes || 0)}</span>
                        </p>
                        <p>
                            <strong>Subido:</strong> <span id="detalleFecha">{new Date(media.created_at).toLocaleString('es-ES')}</span>
                        </p>
                    </div>

                    <div className="grupoFormulario">
                        <label>URL del archivo</label>
                        <div className="grupoUrlCopiar">
                            <input type="text" id="detalleUrl" className="inputFormulario" readOnly value={`${window.location.origin}/${media.path}`} />
                            <button type="button" className="botonSecundario" onClick={() => navigator.clipboard.writeText(`${window.location.origin}/${media.path}`)}>
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
    );
};
