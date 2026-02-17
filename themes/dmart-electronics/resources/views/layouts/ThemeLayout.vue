<script setup lang="ts">
import { usePage, Head } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import ThemeHeader from '../components/ThemeHeader.vue';
import ThemeFooter from '../components/ThemeFooter.vue';
import MobileMenu from '../components/MobileMenu.vue';
import CartDrawer from '../components/CartDrawer.vue';
import SearchModal from '../components/SearchModal.vue';
import BackToTop from '../components/BackToTop.vue';
import Toast from '@/components/Toast.vue';
import '../../css/theme.css';

// Inject Font Awesome 6.5.1 CDN for icon parity with original template
onMounted(() => {
    document.documentElement.classList.remove('dark');

    if (!document.getElementById('fa-icons-cdn')) {
        const link = document.createElement('link');
        link.id = 'fa-icons-cdn';
        link.rel = 'stylesheet';
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';
        link.crossOrigin = 'anonymous';
        document.head.appendChild(link);
    }
});

const page = usePage();

const theme = computed(() => page.props.theme as any);
const siteConfig = computed(() => page.props.siteConfig as any);
const categories = computed(() => (page.props.categories as any[]) || []);

// Inject CSS custom properties from theme settings
const themeStyle = computed(() => {
    const s = theme.value?.settings ?? {};
    const containerMap: Record<string, string> = {
        '1140px': '1140px', '1320px': '1320px', '1536px': '1536px',
        '1600px': '1600px', '1800px': '1800px', '100%': '100%',
    };
    const containerWidth = s['layout.container_width'] || '1800px';
    return {
        '--dmart-container': containerMap[containerWidth] || containerWidth,
        '--dmart-primary': s['colors.primary_color'] || '#FF4035',
        '--dmart-dark': s['colors.secondary_color'] || '#0A111E',
        '--dmart-accent': s['colors.accent_color'] || '#FFD43B',
        '--dmart-success': s['colors.success_color'] || '#10B981',
        '--dmart-danger': s['colors.danger_color'] || '#EF4444',
        '--dmart-font-body': s['typography.body_font'] || "'Jost', sans-serif",
        '--dmart-font-title': s['typography.heading_font'] || "'Albert Sans', sans-serif",
    };
});

const mobileMenuOpen = ref(false);
const cartDrawerOpen = ref(false);
const searchModalOpen = ref(false);

const toggleMobileMenu = () => { mobileMenuOpen.value = !mobileMenuOpen.value; };
const toggleCartDrawer = () => { cartDrawerOpen.value = !cartDrawerOpen.value; };
const toggleSearchModal = () => { searchModalOpen.value = !searchModalOpen.value; };

// Lock body scroll when overlay is open
const lockScroll = computed(() => mobileMenuOpen.value || cartDrawerOpen.value || searchModalOpen.value);
onMounted(() => {
    const observer = new MutationObserver(() => {
        document.body.style.overflow = lockScroll.value ? 'hidden' : '';
    });
});
onUnmounted(() => {
    document.body.style.overflow = '';
});

// Provide/inject for deeply nested components
import { provide } from 'vue';
provide('toggleCartDrawer', toggleCartDrawer);
provide('toggleSearchModal', toggleSearchModal);
</script>

<template>
    <div class="theme-dmart min-h-screen flex flex-col bg-white" :style="themeStyle">
        <ThemeHeader
            :theme="theme"
            :site-config="siteConfig"
            :categories="categories"
            @toggle-mobile-menu="toggleMobileMenu"
            @toggle-cart="toggleCartDrawer"
            @toggle-search="toggleSearchModal"
        />

        <main class="flex-1">
            <slot />
        </main>

        <ThemeFooter :theme="theme" :site-config="siteConfig" />

        <!-- Overlays -->
        <MobileMenu
            :open="mobileMenuOpen"
            :categories="categories"
            @close="mobileMenuOpen = false"
        />
        <CartDrawer
            :open="cartDrawerOpen"
            @close="cartDrawerOpen = false"
        />
        <SearchModal
            :open="searchModalOpen"
            @close="searchModalOpen = false"
        />
        <BackToTop />
        <Toast />
    </div>
</template>
