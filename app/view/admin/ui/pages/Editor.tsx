import React, {useEffect, useState, FormEvent} from 'react';
import {useParams, useNavigate} from 'react-router-dom';
import {Toolbar} from '../components/structure/Toolbar';
import {Button} from '../components/ui/Button';
import {Panel} from '../components/ui/Panel';
import {Input} from '../components/form/Input';
import {Textarea} from '../components/form/Textarea';
import {Select} from '../components/form/Select';
import {Content} from '../types';
import {MediaSelector} from '../components/media/MediaSelector';

interface MetadataParams {
    key: string;
    value: string;
    isJson: boolean;
}

export const Editor = () => {
    const {postType, id} = useParams<{postType: string; id?: string}>();
    const navigate = useNavigate();
    const isEdit = !!id;

    // Form State
    const [title, setTitle] = useState('');
    const [slug, setSlug] = useState('');
    const [content, setContent] = useState('');
    const [status, setStatus] = useState('draft');
    const [metaData, setMetaData] = useState<MetadataParams[]>([]);
    const [featuredImage, setFeaturedImage] = useState<{id: string; url: string} | null>(null);
    const [showMediaSelector, setShowMediaSelector] = useState(false);
    const [contentData, setContentData] = useState<Record<string, any> | null>(null);
    const [contentItem, setContentItem] = useState<Content | null>(null);

    // Metadata & Config State
    const [config, setConfig] = useState<any>(null);
    const [loading, setLoading] = useState(isEdit);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetchContent();
    }, [id, isEdit]);

    const fetchContent = async () => {
        setLoading(true);
        try {
            const endpoint = isEdit ? `/admin/${postType}/${id}/edit` : `/admin/${postType}/create`;
            const response = await fetch(endpoint, {
                headers: {Accept: 'application/json'}
            });
            if (!response.ok) throw new Error('Error cargando datos');

            const data = await response.json();

            if (isEdit) {
                const item: Content = data.content;
                setConfig(data.postTypeConfig);
                setContentItem(item);
                setContentData(item.content_data || null);

                // Populate form
                setTitle(item.content_data?.title || '');
                setSlug(item.slug || '');
                setContent(item.content_data?.content || '');
                setStatus(item.status || 'draft');

                // Process metadata
                const metas: MetadataParams[] = [];
                if (item.content_data) {
                    Object.entries(item.content_data).forEach(([key, value]) => {
                        if (key !== 'title' && key !== 'content' && key !== 'featured_image') {
                            const isJson = typeof value === 'object';
                            metas.push({
                                key,
                                value: isJson ? JSON.stringify(value, null, 2) : String(value),
                                isJson
                            });
                        }
                    });
                }
                setMetaData(metas);

                if (item.content_data?.featured_image) {
                    setFeaturedImage(item.content_data.featured_image);
                }
            } else {
                // Create mode: just set config
                setConfig(data.postTypeConfig);
                // Can set defaults here if config has them
            }
        } catch (err: any) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleSave = async (targetStatus?: string) => {
        setSaving(true);
        setError(null);

        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('slug', slug);
            formData.append('content', content);
            formData.append('status', targetStatus || status);

            // Metadata arrays for PHP controller
            metaData.forEach(meta => {
                formData.append('meta_keys[]', meta.key);
                formData.append('meta_values[]', meta.value);
                formData.append('meta_is_json[]', meta.isJson ? '1' : '0');
            });

            if (featuredImage) {
                formData.append('featured_image_id', featuredImage.id);
                formData.append('featured_image_url', featuredImage.url);
            }

            // Method spoofing for update
            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            const url = isEdit ? `/admin/${postType}/${id}` : `/admin/${postType}`;

            const response = await fetch(url, {
                method: 'POST', // Always POST, method spoofing handles PUT
                headers: {Accept: 'application/json'},
                body: formData
            });

            if (!response.ok) throw new Error('Error guardando contenido');

            const result = await response.json();

            if (!isEdit && result.success) {
                // Redirect to edit page of new item
                navigate(`/admin/${result.content.type}/${result.content.id}/edit`, {replace: true});
            } else {
                // Refresh data to ensure sync
                // fetchContent(); // Optional, or just show success msg
                alert('Contenido guardado correctamente');
            }
        } catch (err: any) {
            setError(err.message);
        } finally {
            setSaving(false);
        }
    };

    const addMeta = () => {
        setMetaData([...metaData, {key: '', value: '', isJson: false}]);
    };

    const updateMeta = (index: number, field: keyof MetadataParams, val: any) => {
        const newMetas = [...metaData];
        (newMetas[index] as any)[field] = val;
        setMetaData(newMetas);
    };

    const removeMeta = (index: number) => {
        setMetaData(metaData.filter((_, i) => i !== index));
    };

    const handleDelete = async () => {
        if (!confirm('¿Estás seguro de que deseas mover este contenido a la papelera?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/${postType}/${id}`, {
                method: 'DELETE',
                headers: {Accept: 'application/json'}
            });

            if (!response.ok) throw new Error('Error al eliminar contenido');

            navigate(`/admin/${postType}`, {replace: true});
        } catch (err: any) {
            setError(err.message);
        }
    };

    if (loading) return <div className="cargando">Cargando...</div>;

    return (
        <div className="contenedorEditor">
            <Toolbar
                left={
                    <h1 className="tituloPagina">
                        {isEdit ? 'Editar' : 'Componer'} {config?.nombreSingular || 'Contenido'}
                    </h1>
                }
                right={
                    <div className="grupoAcciones">
                        <Button variant="secondary" onClick={() => handleSave('draft')} disabled={saving}>
                            Guardar Borrador
                        </Button>
                        <Button onClick={() => handleSave()} disabled={saving}>
                            {isEdit ? 'Actualizar' : 'Publicar'}
                        </Button>
                    </div>
                }
            />

            {error && <div className="alertaError">{error}</div>}

            <div className="editorGrid">
                <div className="editorPrincipal">
                    <Input label="Título" value={title} onChange={e => setTitle(e.target.value)} placeholder="Escribe el título aquí..." className="inputTitulo" />

                    <div className="grupoSlug">
                        <Input label="Slug (URL)" value={slug} onChange={e => setSlug(e.target.value)} placeholder="mi-contenido-incleible" helpText="Se genera automáticamente si se deja vacío" className="inputSlug" prefix={<span className="prefijoSlug">{window.location.host}/</span>} />
                    </div>

                    <Textarea label="Contenido" value={content} onChange={e => setContent(e.target.value)} rows={15} className="textareaContenido" />

                    <div className="seccionMetadatos">
                        <div className="encabezadoMetadatos">
                            <h3 className="tituloSeccion">Metadatos</h3>
                            <Button type="button" className="botonAgregarMeta" onClick={addMeta}>
                                + Agregar campo
                            </Button>
                        </div>

                        <div className="listaMetadatos">
                            {metaData.length === 0 ? (
                                <div className="mensajeSinMetadatos">
                                    <p>No hay metadatos adicionales. Haz clic en "Agregar campo" para crear uno.</p>
                                </div>
                            ) : (
                                metaData.map((meta, index) => (
                                    <div key={index} className="filaMetadato">
                                        <div className="campoMetaClave">
                                            <input type="text" className="inputMetaClave" placeholder="Clave" value={meta.key} onChange={e => updateMeta(index, 'key', e.target.value)} />
                                        </div>
                                        <div className="campoMetaValor">
                                            <textarea placeholder="Valor" value={meta.value} onChange={e => updateMeta(index, 'value', e.target.value)} className="inputMetaValor textareaMetaValor" />
                                        </div>
                                        <div className="accionesMetadato">
                                            <button type="button" className="botonEditarClave">
                                                Editar
                                            </button>
                                            <button type="button" className="botonEliminarMeta" onClick={() => removeMeta(index)}>
                                                X
                                            </button>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>

                <div className="editorLateral">
                    <Panel title="Publicación">
                        <Select
                            label="Estado"
                            value={status}
                            onChange={e => setStatus(e.target.value)}
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
                                    <strong>ID:</strong> {id}
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
                            {/* Actions duplicated here to match PHP sidebar actions? PHP puts save buttons inside sidebar */}
                            <Button onClick={() => handleSave()} className="botonGuardar">
                                {isEdit ? 'Actualizar' : 'Publicar'}
                            </Button>
                            <Button variant="secondary" onClick={() => handleSave('draft')} className="botonBorrador">
                                Guardar borrador
                            </Button>
                        </div>
                    </Panel>

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
                                    <button type="button" className="botonSecundario" id="botonSeleccionarImagen" onClick={() => setShowMediaSelector(true)}>
                                        {featuredImage ? 'Cambiar' : 'Seleccionar'}
                                    </button>
                                    <button type="button" className="botonSecundario" id="botonQuitarImagen" onClick={() => setFeaturedImage(null)} style={{display: featuredImage ? undefined : 'none'}}>
                                        Quitar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                    <button type="button" className="botonPeligro botonEliminarContenido" id="botonEliminarContenido" onClick={() => handleDelete()}>
                                        Mover a papelera
                                    </button>
                                </div>
                            </div>
                        </>
                    )}

                    <MediaSelector
                        isOpen={showMediaSelector}
                        onClose={() => setShowMediaSelector(false)}
                        onSelect={media => {
                            setFeaturedImage({
                                id: String(media.id),
                                url: '/' + media.path
                            });
                        }}
                    />
                </div>
            </div>
        </div>
    );
};
