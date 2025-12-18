import {useState, useEffect, useCallback} from 'react';
import {Media, Pagination} from '../types';

interface UseMediaFetchParams {
    initialPage?: number;
    initialType?: string;
    initialSearch?: string;
}

interface UseMediaFetchReturn {
    files: Media[];
    loading: boolean;
    pagination: Pagination | null;
    filterType: string;
    search: string;
    page: number;
    setFilterType: (type: string) => void;
    setSearch: (search: string) => void;
    setPage: (page: number | ((prev: number) => number)) => void;
    refetch: () => Promise<void>;
}

export const useMediaFetch = ({initialPage = 1, initialType = '', initialSearch = ''}: UseMediaFetchParams = {}): UseMediaFetchReturn => {
    const [files, setFiles] = useState<Media[]>([]);
    const [loading, setLoading] = useState(false);
    const [pagination, setPagination] = useState<Pagination | null>(null);

    /* Filtros */
    const [filterType, setFilterType] = useState(initialType);
    const [search, setSearch] = useState(initialSearch);
    const [page, setPage] = useState(initialPage);

    const fetchMedia = useCallback(async () => {
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
    }, [page, filterType, search]);

    useEffect(() => {
        fetchMedia();
    }, [fetchMedia]);

    return {
        files,
        loading,
        pagination,
        filterType,
        search,
        page,
        setFilterType,
        setSearch,
        setPage,
        refetch: fetchMedia
    };
};
