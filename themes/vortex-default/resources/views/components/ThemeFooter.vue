<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useStorefrontMenu } from '@/composables/useStorefrontMenu';
import { computed } from 'vue';

interface Props {
    theme?: any;
    siteConfig?: {
        name: string;
        url: string;
        description: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    theme: null,
    siteConfig: () => ({
        name: 'Vortex',
        url: '/',
        description: 'E-commerce Platform'
    })
});

const currentYear = new Date().getFullYear();
const { menus, loading, getMenuUrl } = useStorefrontMenu();

// Group footer items by parent
const footerSections = computed(() => {
    if (!menus.value.footer || menus.value.footer.length === 0) {
        return [];
    }
    
    return menus.value.footer.filter(item => item.children && item.children.length > 0);
});
</script>

<template>
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About -->
                <div>
                    <h3 class="text-xl font-bold text-white mb-4">{{ siteConfig.name }}</h3>
                    <p class="text-sm">{{ siteConfig.description }}</p>
                </div>

                <!-- Dynamic Footer Sections -->
                <template v-if="!loading && footerSections.length > 0">
                    <div v-for="section in footerSections" :key="section.id">
                        <h4 class="font-semibold text-white mb-4">{{ section.title }}</h4>
                        <ul class="space-y-2 text-sm">
                            <li v-for="child in section.children" :key="child.id">
                                <Link :href="getMenuUrl(child)" class="hover:text-white transition-colors">
                                    {{ child.title }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                </template>

                <!-- Fallback sections while loading or if no menu -->
                <template v-else>
                    <!-- Company -->
                    <div>
                        <h4 class="font-semibold text-white mb-4">Company</h4>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <Link href="/about" class="hover:text-white transition-colors">
                                    About Us
                                </Link>
                            </li>
                            <li>
                                <Link href="/contact" class="hover:text-white transition-colors">
                                    Contact Us
                                </Link>
                            </li>
                            <li>
                                <Link href="/products" class="hover:text-white transition-colors">
                                    Shop
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Help & Support -->
                    <div>
                        <h4 class="font-semibold text-white mb-4">Help & Support</h4>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <Link href="/shipping-returns" class="hover:text-white transition-colors">
                                    Shipping & Returns
                                </Link>
                            </li>
                            <li>
                                <Link href="/faq" class="hover:text-white transition-colors">
                                    FAQ
                                </Link>
                            </li>
                            <li>
                                <Link href="/contact" class="hover:text-white transition-colors">
                                    Support Center
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h4 class="font-semibold text-white mb-4">Legal</h4>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <Link href="/privacy-policy" class="hover:text-white transition-colors">
                                    Privacy Policy
                                </Link>
                            </li>
                            <li>
                                <Link href="/terms-of-service" class="hover:text-white transition-colors">
                                    Terms of Service
                                </Link>
                            </li>
                        </ul>
                    </div>
                </template>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm">
                <p>&copy; {{ currentYear }} {{ siteConfig.name }}. All rights reserved.</p>
                <p class="mt-2 text-gray-500">
                    Powered by <span class="text-gray-400 font-medium">Vortex E-Commerce Platform</span>
                </p>
            </div>
        </div>
    </footer>
</template>
