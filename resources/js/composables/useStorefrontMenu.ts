import { ref, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
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

const emptyMenus = (): MenuData => ({
    header: [],
    footer: [],
    mobile: [],
});

export function useStorefrontMenu() {
    const page = usePage();
    const sharedMenus = computed(() => (page.props as { storefrontMenus?: MenuData }).storefrontMenus);

    const fetchedMenus = ref<MenuData>(emptyMenus());
    const menus = computed<MenuData>(() => sharedMenus.value ?? fetchedMenus.value);
    const loading = ref(!sharedMenus.value);
    const error = ref<string | null>(null);

    const applyMenus = (data: MenuData) => {
        fetchedMenus.value = {
            header: data.header ?? [],
            footer: data.footer ?? [],
            mobile: data.mobile ?? [],
        };
    };

    const fetchMenus = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get('/api/menus/all');
            applyMenus(response.data);
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
            fetchedMenus.value = {
                ...fetchedMenus.value,
                [type]: response.data.items,
            };
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
            return item.route;
        }
        return '#';
    };

    const hasChildren = (item: MenuItem): boolean => {
        return item.children && item.children.length > 0;
    };

    onMounted(() => {
        if (!sharedMenus.value) {
            fetchMenus();
        }
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
