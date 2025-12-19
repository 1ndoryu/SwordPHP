import {useState, useCallback} from 'react';
import {useNavigate} from 'react-router-dom';
import {Content} from '../types';

interface MetadataParams {
    key: string;
    value: string;
    isJson: boolean;
}

interface EditorFormState {
    title: string;
    slug: string;
    content: string;
    status: string;
    metaData: MetadataParams[];
    featuredImage: {id: string; url: string} | null;
}

interface UseEditorFormParams {
    postType: string;
    id?: string;
    isEdit: boolean;
}

interface UseEditorFormReturn {
    formState: EditorFormState;
    setTitle: (value: string) => void;
    setSlug: (value: string) => void;
    setContent: (value: string) => void;
    setStatus: (value: string) => void;
    setFeaturedImage: (value: {id: string; url: string} | null) => void;
    addMeta: () => void;
    updateMeta: (index: number, field: keyof MetadataParams, value: any) => void;
    removeMeta: (index: number) => void;
    handleSave: (targetStatus?: string) => Promise<void>;
    handleDelete: () => Promise<void>;
    populateForm: (item: Content) => void;
    saving: boolean;
    error: string | null;
}

export const useEditorForm = ({postType, id, isEdit}: UseEditorFormParams): UseEditorFormReturn => {
    const navigate = useNavigate();

    /* Estado del formulario */
    const [title, setTitle] = useState('');
    const [slug, setSlug] = useState('');
    const [content, setContent] = useState('');
    const [status, setStatus] = useState('draft');
    const [metaData, setMetaData] = useState<MetadataParams[]>([]);
    const [featuredImage, setFeaturedImage] = useState<{id: string; url: string} | null>(null);

    /* Estado de UI */
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    /* Poblar formulario desde contenido existente */
    const populateForm = useCallback((item: Content) => {
        setTitle(item.content_data?.title || '');
        setSlug(item.slug || '');
        setContent(item.content_data?.content || '');
        setStatus(item.status || 'draft');

        /* Procesar metadatos */
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
    }, []);

    /* Funciones de metadatos */
    const addMeta = useCallback(() => {
        setMetaData(prev => [...prev, {key: '', value: '', isJson: false}]);
    }, []);

    const updateMeta = useCallback((index: number, field: keyof MetadataParams, value: any) => {
        setMetaData(prev => {
            const newMetas = [...prev];
            (newMetas[index] as any)[field] = value;
            return newMetas;
        });
    }, []);

    const removeMeta = useCallback((index: number) => {
        setMetaData(prev => prev.filter((_, i) => i !== index));
    }, []);

    /* Guardar contenido */
    const handleSave = useCallback(
        async (targetStatus?: string) => {
            setSaving(true);
            setError(null);

            try {
                const formData = new FormData();
                formData.append('title', title);
                formData.append('slug', slug);
                formData.append('content', content);
                formData.append('status', targetStatus || status);

                /* Arrays de metadatos para el controlador PHP */
                metaData.forEach(meta => {
                    formData.append('meta_keys[]', meta.key);
                    formData.append('meta_values[]', meta.value);
                    formData.append('meta_is_json[]', meta.isJson ? '1' : '0');
                });

                if (featuredImage) {
                    formData.append('featured_image_id', featuredImage.id);
                    formData.append('featured_image_url', featuredImage.url);
                }

                /* Method spoofing para update */
                if (isEdit) {
                    formData.append('_method', 'PUT');
                }

                const url = isEdit ? `/admin/${postType}/${id}` : `/admin/${postType}`;

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {Accept: 'application/json'},
                    body: formData
                });

                if (!response.ok) throw new Error('Error guardando contenido');

                const result = await response.json();

                if (!isEdit && result.success) {
                    navigate(`/${result.content.type}/${result.content.id}/edit`, {replace: true});
                } else {
                    alert('Contenido guardado correctamente');
                }
            } catch (err: any) {
                setError(err.message);
            } finally {
                setSaving(false);
            }
        },
        [title, slug, content, status, metaData, featuredImage, isEdit, postType, id, navigate]
    );

    /* Eliminar contenido */
    const handleDelete = useCallback(async () => {
        if (!confirm('¿Estás seguro de que deseas mover este contenido a la papelera?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/${postType}/${id}`, {
                method: 'DELETE',
                headers: {Accept: 'application/json'}
            });

            if (!response.ok) throw new Error('Error al eliminar contenido');

            navigate(`/${postType}`, {replace: true});
        } catch (err: any) {
            setError(err.message);
        }
    }, [postType, id, navigate]);

    return {
        formState: {title, slug, content, status, metaData, featuredImage},
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
        error
    };
};
