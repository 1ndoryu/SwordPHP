import React, {useState, useCallback} from 'react';
import {useParams} from 'react-router-dom';

/* Hooks */
import {useEditorForm, useContentFetch} from '../hooks';

/* Componentes */
import {Toolbar} from '../components/structure/Toolbar';
import {Button} from '../components/ui/Button';
import {Input} from '../components/form/Input';
import {Textarea} from '../components/form/Textarea';
import {MediaSelector} from '../components/media/MediaSelector';
import {MetadataEditor, EditorSidebar} from '../components/editor';

export const Editor = () => {
    const {postType, id} = useParams<{postType: string; id?: string}>();
    const isEdit = !!id;

    /* Estado local del modal */
    const [showMediaSelector, setShowMediaSelector] = useState(false);

    /* Hook de formulario */
    const {
        formState,
        setTitle,
        setSlug,
        setContent,
        setStatus,
        setFeaturedImage,
        addMeta,
        updateMeta,
        removeMeta,
        handleSave,
        handleDelete,
        populateForm,
        saving,
        error: formError
    } = useEditorForm({
        postType: postType || 'post',
        id,
        isEdit
    });

    /* Hook de fetch de contenido */
    const {
        contentItem,
        contentData,
        config,
        loading,
        error: fetchError
    } = useContentFetch({
        postType: postType || 'post',
        id,
        isEdit,
        onContentLoaded: useCallback(
            (item: any) => {
                populateForm(item);
            },
            [populateForm]
        )
    });

    const error = formError || fetchError;

    if (loading) return <div className="cargando">Cargando...</div>;

    return (
        <div className="contenedorEditor" id="contenedorEditor">
            <Toolbar
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
                {/* Area principal del editor */}
                <div className="editorPrincipal" id="editorPrincipal">
                    <Input label="Título" value={formState.title} onChange={e => setTitle(e.target.value)} placeholder="Escribe el título aquí..." className="inputTitulo" />

                    <div className="grupoSlug">
                        <Input label="Slug (URL)" value={formState.slug} onChange={e => setSlug(e.target.value)} placeholder="mi-contenido-increible" helpText="Se genera automáticamente si se deja vacío" className="inputSlug" prefix={<span className="prefijoSlug">{window.location.host}/</span>} />
                    </div>

                    <Textarea label="Contenido" value={formState.content} onChange={e => setContent(e.target.value)} rows={15} className="textareaContenido" />

                    <MetadataEditor metaData={formState.metaData} onAdd={addMeta} onUpdate={updateMeta} onRemove={removeMeta} />
                </div>

                {/* Panel lateral */}
                <EditorSidebar status={formState.status} onStatusChange={setStatus} featuredImage={formState.featuredImage} onOpenMediaSelector={() => setShowMediaSelector(true)} onRemoveFeaturedImage={() => setFeaturedImage(null)} onSave={handleSave} onDelete={handleDelete} saving={saving} isEdit={isEdit} contentItem={contentItem} contentData={contentData} slug={formState.slug} />
            </div>

            {/* Modal selector de medios */}
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
    );
};
