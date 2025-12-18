import React from 'react';
import {Panel} from '../ui/Panel';
import {Button} from '../ui/Button';
import {Select} from '../form/Select';
import {Content} from '../../types';

interface EditorSidebarProps {
    /* Estado */
    status: string;
    onStatusChange: (value: string) => void;
    featuredImage: {id: string; url: string} | null;
    onOpenMediaSelector: () => void;
    onRemoveFeaturedImage: () => void;

    /* Acciones */
    onSave: (status?: string) => void;
    onDelete?: () => void;
    saving: boolean;

    /* Datos */
    isEdit: boolean;
    contentItem: Content | null;
    contentData: Record<string, any> | null;
    slug: string;
}

export const EditorSidebar: React.FC<EditorSidebarProps> = ({status, onStatusChange, featuredImage, onOpenMediaSelector, onRemoveFeaturedImage, onSave, onDelete, saving, isEdit, contentItem, contentData, slug}) => {
    return (
        <div className="editorLateral" id="editorLateral">
            {/* Panel de Publicacion */}
            <Panel title="Publicación">
                <Select
                    label="Estado"
                    value={status}
                    onChange={e => onStatusChange(e.target.value)}
                    options={[
                        {value: 'published', label: 'Publicado'},
                        {value: 'draft', label: 'Borrador'}
                    ]}
                    className="selectEstado"
                />

                {isEdit && contentItem && (
                    <div className="infoPublicacion">
                        <p>
                            <strong>Tipo:</strong> {contentItem.type?.charAt(0).toUpperCase() + contentItem.type?.slice(1)}
                        </p>
                        <p>
                            <strong>ID:</strong> {contentItem.id}
                        </p>
                        <p>
                            <strong>Creado:</strong> {contentItem.created_at ? new Date(contentItem.created_at).toLocaleString('es-ES') : '-'}
                        </p>
                        <p>
                            <strong>Actualizado:</strong> {contentItem.updated_at ? new Date(contentItem.updated_at).toLocaleString('es-ES') : '-'}
                        </p>
                    </div>
                )}

                <div className="accionesPanelLateral">
                    <Button onClick={() => onSave()} className="botonGuardar" disabled={saving}>
                        {isEdit ? 'Actualizar' : 'Publicar'}
                    </Button>
                    <Button variant="secondary" onClick={() => onSave('draft')} className="botonBorrador" disabled={saving}>
                        Guardar borrador
                    </Button>
                </div>
            </Panel>

            {/* Panel Imagen Destacada */}
            <div className="panelLateral">
                <h3 className="tituloPanelLateral">Imagen destacada</h3>
                <div className="contenidoPanelLateral">
                    <div className="campoImagenDestacada" id="campoImagenDestacada">
                        <div className="previewImagenDestacada" id="previewImagenDestacada">
                            {featuredImage ? (
                                <img src={featuredImage.url} alt="Imagen destacada" />
                            ) : (
                                <div className="placeholderImagen">
                                    <span className="iconoPlaceholder">&#128444;</span>
                                    <span>Sin imagen</span>
                                </div>
                            )}
                        </div>
                        <div className="accionesImagenDestacada">
                            <button type="button" className="botonSecundario" id="botonSeleccionarImagen" onClick={onOpenMediaSelector}>
                                {featuredImage ? 'Cambiar' : 'Seleccionar'}
                            </button>
                            <button type="button" className={`botonSecundario ${!featuredImage ? 'oculto' : ''}`} id="botonQuitarImagen" onClick={onRemoveFeaturedImage}>
                                Quitar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Paneles solo visibles en modo edicion */}
            {isEdit && (
                <>
                    <div className="panelLateral">
                        <h3 className="tituloPanelLateral">Vista Previa</h3>
                        <div className="contenidoPanelLateral">
                            <a href={`/${slug}`} target="_blank" className="botonSecundario botonVistaPrevia" rel="noopener noreferrer" data-no-spa>
                                Ver en el sitio
                            </a>
                        </div>
                    </div>

                    <div className="panelLateral">
                        <h3 className="tituloPanelLateral">Datos JSON</h3>
                        <div className="contenidoPanelLateral">
                            <details className="detallesJson">
                                <summary>Ver content_data completo</summary>
                                <pre className="preJsonData">{contentData ? JSON.stringify(contentData, null, 2) : '{}'}</pre>
                            </details>
                        </div>
                    </div>

                    <div className="panelLateral panelPeligro">
                        <h3 className="tituloPanelLateral tituloPeligro">Zona de Peligro</h3>
                        <div className="contenidoPanelLateral">
                            <p className="advertenciaPeligro">Esta accion no se puede deshacer.</p>
                            <button type="button" className="botonPeligro botonEliminarContenido" id="botonEliminarContenido" onClick={onDelete}>
                                Mover a papelera
                            </button>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
};
