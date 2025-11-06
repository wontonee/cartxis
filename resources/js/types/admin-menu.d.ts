export interface AdminMenuItem {
    id: number;
    name: string;
    icon: string | null;
    route: string | null;
    url: string | null;
    parent_id: number | null;
    sort_order: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    parent?: AdminMenuItem;
    children?: AdminMenuItem[];
}

export interface AdminMenuFormData {
    name: string;
    icon: string | null;
    route: string | null;
    url: string | null;
    parent_id: number | null;
    sort_order: number;
    is_active: boolean;
}

export interface AdminMenuReorderItem {
    id: number;
    sort_order: number;
    parent_id: number | null;
}
