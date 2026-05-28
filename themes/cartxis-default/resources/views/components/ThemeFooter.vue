<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useStorefrontMenu } from '@/composables/useStorefrontMenu';
import { useThemeSettings } from '@/composables/useThemeSettings';
import { computed } from 'vue';
import { CreditCard, Facebook, Instagram, Twitter } from 'lucide-vue-next';

interface Props {
    theme?: any;
    siteConfig?: {
        name: string;
        url: string;
        description: string;
    };
    containerStyle?: Record<string, string>;
}

const props = withDefaults(defineProps<Props>(), {
    theme: null,
    siteConfig: () => ({
        name: 'Cartxis',
        url: '/',
        description: 'E-commerce Platform',
    }),
    containerStyle: () => ({}),
});

const currentYear = new Date().getFullYear();
const { menus, loading, getMenuUrl } = useStorefrontMenu();
const { showPlatformBranding, accent } = useThemeSettings();

const footerSections = computed(() => {
    if (!menus.value.footer || menus.value.footer.length === 0) {
        return [];
    }

    return menus.value.footer.filter(item => item.children && item.children.length > 0);
});
</script>

<template>
    <footer class="bg-slate-900 text-slate-300 py-12">
        <div class="mx-auto px-4 sm:px-6 lg:px-8" :style="containerStyle">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold text-white mb-4">{{ siteConfig.name }}</h3>
                    <p class="text-sm leading-relaxed mb-4">{{ siteConfig.description }}</p>
                    <div class="flex items-center gap-3">
                        <a href="#" class="p-2 rounded-full bg-slate-800 hover:bg-blue-600 transition-colors" aria-label="Facebook">
                            <Facebook class="w-4 h-4" />
                        </a>
                        <a href="#" class="p-2 rounded-full bg-slate-800 hover:bg-blue-600 transition-colors" aria-label="Instagram">
                            <Instagram class="w-4 h-4" />
                        </a>
                        <a href="#" class="p-2 rounded-full bg-slate-800 hover:bg-blue-600 transition-colors" aria-label="Twitter">
                            <Twitter class="w-4 h-4" />
                        </a>
                    </div>
                </div>

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

                <template v-else>
                    <div>
                        <h4 class="font-semibold text-white mb-4">Company</h4>
                        <ul class="space-y-2 text-sm">
                            <li><Link href="/about-us" class="hover:text-white transition-colors">About Us</Link></li>
                            <li><Link href="/careers" class="hover:text-white transition-colors">Careers</Link></li>
                            <li><Link href="/contact-us" class="hover:text-white transition-colors">Contact</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white mb-4">Customer Service</h4>
                        <ul class="space-y-2 text-sm">
                            <li><Link href="/help" class="hover:text-white transition-colors">Help Center</Link></li>
                            <li><Link href="/shipping-and-returns" class="hover:text-white transition-colors">Shipping & Returns</Link></li>
                            <li><Link href="/checkout/track-order" class="hover:text-white transition-colors">Track Order</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white mb-4">Legal</h4>
                        <ul class="space-y-2 text-sm">
                            <li><Link href="/privacy-policy" class="hover:text-white transition-colors">Privacy Policy</Link></li>
                            <li><Link href="/terms-and-conditions" class="hover:text-white transition-colors">Terms of Service</Link></li>
                        </ul>
                    </div>
                </template>
            </div>

            <div class="border-t border-slate-800 mt-8 pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-slate-500">
                    <CreditCard class="w-4 h-4" :style="{ color: accent }" />
                    <span class="text-xs">Visa · Mastercard · PayPal · Stripe</span>
                </div>
                <div class="text-center text-sm">
                    <p>&copy; {{ currentYear }} {{ siteConfig.name }}. All rights reserved.</p>
                    <p v-if="showPlatformBranding" class="mt-1 text-slate-500">
                        Powered by <span class="text-slate-400 font-medium">Cartxis</span>
                    </p>
                </div>
            </div>
        </div>
    </footer>
</template>
