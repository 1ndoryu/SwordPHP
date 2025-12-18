import {useState, useEffect, useCallback} from 'react';
import {Content} from '../types';

interface PostTypeConfig {
    nombreSingular?: string;
    nombrePlural?: string;
    slug?: string;
    [key: string]: any;
}

interface UseContentFetchParams {
    postType: string;
    id?: string;
    isEdit: boolean;
    onContentLoaded?: (item: Content) => void;
}

interface UseContentFetchReturn {
    contentItem: Content | null;
    contentData: Record<string, any> | null;
    config: PostTypeConfig | null;
    loading: boolean;
    error: string | null;
    refetch: () => Promise<void>;
}

export const useContentFetch = ({postType, id, isEdit, onContentLoaded}: UseContentFetchParams): UseContentFetchReturn => {
    const [contentItem, setContentItem] = useState<Content | null>(null);
    const [contentData, setContentData] = useState<Record<string, any> | null>(null);
    const [config, setConfig] = useState<PostTypeConfig | null>(null);
    const [loading, setLoading] = useState(isEdit);
    const [error, setError] = useState<string | null>(null);

    const fetchContent = useCallback(async () => {
        setLoading(true);
        setError(null);

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

                if (onContentLoaded) {
                    onContentLoaded(item);
                }
            } else {
                setConfig(data.postTypeConfig);
            }
        } catch (err: any) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    }, [postType, id, isEdit, onContentLoaded]);

    useEffect(() => {
        fetchContent();
    }, [fetchContent]);

    return {
        contentItem,
        contentData,
        config,
        loading,
        error,
        refetch: fetchContent
    };
};
