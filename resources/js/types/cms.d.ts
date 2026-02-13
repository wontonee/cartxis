export type PageStatus = 'draft' | 'published' | 'disabled';

export interface Page {
    id: number;
    title: string;
    url_key: string;
    content: string;
    meta_title: string | null;
    meta_description: string | null;
    meta_keywords: string | null;
    status: PageStatus;
    created_by: number | null;
    updated_by: number | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    url: string;
    creator?: {
        id: number;
        name: string;
        email: string;
    };
    updater?: {
        id: number;
        name: string;
        email: string;
    };
}

export interface PageFormData {
    title: string;
    url_key: string;
    content: string;
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    status: PageStatus;
}

export interface PageFilters {
    search?: string;
    status?: PageStatus | 'all';
    sort_by?: 'title' | 'status' | 'created_at' | 'updated_at';
    sort_order?: 'asc' | 'desc';
}

export interface PaginatedPages {
    data: Page[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export interface PageStatistics {
    total: number;
    published: number;
    draft: number;
    disabled: number;
}
