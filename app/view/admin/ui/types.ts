export interface PostTypeDef {
    nombre: string;
    description?: string;
    icono?: string;
}

export interface SwordState {
    user: string;
    postTypes: Record<string, PostTypeDef>;
    currentRoute: string;
    currentPostType: string | null;
    title: string;
}

declare global {
    interface Window {
        sword?: SwordState;
    }
}

export interface Content {
    id: number;
    title: string;
    slug: string;
    type: string;
    status: 'published' | 'draft' | 'trash';
    created_at: string;
    updated_at?: string;
    user?: {
        username: string;
    };
    content_data?: any;
    deleted_at?: string;
}

export interface Pagination {
    current: number;
    total_pages: number;
    total_items: number;
    per_page: number;
}

export interface Media {
    id: number;
    path: string;
    mime_type: string;
    metadata: {
        original_name: string;
        size_bytes: number;
        alt_text?: string;
        title?: string;
        description?: string;
    };
    created_at: string;
    user?: {
        username: string;
    };
}
