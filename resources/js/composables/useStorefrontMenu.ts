import { ref, onMounted } from 'vue';
import axios from 'axios';

export interface MenuItem {
    id: number;
    title: string;
    url: string | null;
    route: string | null;
    icon: string | null;
    children: MenuItem[];
}

export interface MenuData {
    header: MenuItem[];
    footer: MenuItem[];
    mobile: MenuItem[];
}

export function useStorefrontMenu() {
    const menus = ref<MenuData>({
        header: [],
        footer: [],
        mobile: [],
    });
    
    const loading = ref(false);
    const error = ref<string | null>(null);

    const fetchMenus = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get('/api/menus/all');
            menus.value = response.data;
        } catch (err) {
            error.value = 'Failed to load menus';
            console.error('Error fetching menus:', err);
        } finally {
            loading.value = false;
        }
    };

    const fetchMenu = async (type: 'header' | 'footer' | 'mobile') => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(`/api/menus/${type}`);
            menus.value[type] = response.data.items;
        } catch (err) {
            error.value = `Failed to load ${type} menu`;
            console.error(`Error fetching ${type} menu:`, err);
        } finally {
            loading.value = false;
        }
    };

    const getMenuUrl = (item: MenuItem): string => {
        if (item.url) {
            return item.url;
        }
        if (item.route) {
            // For named routes, use route() helper if available
            // For now, return the route as-is
            return item.route;
        }
        return '#';
    };

    const hasChildren = (item: MenuItem): boolean => {
        return item.children && item.children.length > 0;
    };

    // Auto-fetch on mount
    onMounted(() => {
        fetchMenus();
    });

    return {
        menus,
        loading,
        error,
        fetchMenus,
        fetchMenu,
        getMenuUrl,
        hasChildren,
    };
}
