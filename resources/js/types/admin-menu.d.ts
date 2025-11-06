/**
 * MenuItem interface matching Vortex\Core\Models\MenuItem
 * Used for admin menu management
 */
export interface MenuItem {
    id: number;
    key: string | null;
    title: string;
    icon: string | null;
    route: string | null;
    url: string | null;
    parent_id: number | null;
    order: number;
    permission: string | null;
    location: 'admin' | 'storefront';
    active: boolean;
    meta: Record<string, any> | null;
    extension_code: string | null;
    created_at: string;
    updated_at: string;
    full_url?: string;
    parent?: MenuItem;
    children?: MenuItem[];
}

/**
 * Form data for creating/updating menu items
 */
export interface MenuFormData {
    key?: string;
    title: string;
    icon: string | null;
    route: string | null;
    url: string | null;
    parent_id: number | null;
    order: number;
    permission: string | null;
    location: 'admin' | 'storefront';
    active: boolean;
    meta?: Record<string, any>;
    extension_code?: string;
}

/**
 * Reorder item structure for drag-drop
 */
export interface MenuReorderItem {
    id: number;
    order: number;
    parent_id: number | null;
}
