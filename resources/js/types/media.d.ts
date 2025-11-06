export interface MediaFile {
    id: number;
    filename: string;
    original_filename: string;
    path: string;
    url: string;
    disk: string;
    mime_type: string;
    size: number;
    formatted_size: string;
    extension: string | null;
    alt_text: string | null;
    title: string | null;
    description: string | null;
    width: number | null;
    height: number | null;
    thumbnails: Record<string, string> | null;
    thumbnail_url: string | null;
    used_count: number;
    is_image: boolean;
    is_video: boolean;
    is_document: boolean;
    folder?: {
        id: number;
        name: string;
        path: string;
    };
    creator?: {
        id: number;
        name: string;
    };
    updater?: {
        id: number;
        name: string;
    };
    usages?: MediaUsage[];
    created_at: string;
    updated_at: string;
}

export interface MediaFolder {
    id: number;
    name: string;
    parent_id: number | null;
    path: string;
    sort_order: number;
    files_count?: number;
    total_files_count?: number;
    children?: MediaFolder[];
    created_at: string;
    updated_at: string;
}

export interface MediaUsage {
    id: number;
    context: string | null;
    usable_type: string;
    usable: {
        id: number;
        title: string;
    } | null;
}

export interface MediaStatistics {
    total_files: number;
    total_images: number;
    total_videos: number;
    total_documents: number;
    total_size: number;
    unused_files: number;
}

export interface MediaFilters {
    folder_id?: number | null;
    type?: 'all' | 'images' | 'videos' | 'documents';
    search?: string;
    sort_by?: 'created_at' | 'original_filename' | 'size';
    sort_order?: 'asc' | 'desc';
}

export interface PaginatedMedia {
    data: MediaFile[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export type MediaViewMode = 'grid' | 'list';

export interface MediaUploadOptions {
    folder_id?: number | null;
    alt_text?: string;
    title?: string;
    description?: string;
}
