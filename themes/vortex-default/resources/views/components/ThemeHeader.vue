<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CartIcon from './CartIcon.vue';
import { useStorefrontMenu } from '@/composables/useStorefrontMenu';

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

const searchQuery = ref('');
const { menus, loading, getMenuUrl, hasChildren } = useStorefrontMenu();
const activeDropdown = ref<number | null>(null);

const primaryColor = computed(() => props.theme?.settings?.primary_color ?? '#3b82f6');

const toggleDropdown = (itemId: number) => {
    activeDropdown.value = activeDropdown.value === itemId ? null : itemId;
};

const closeDropdown = () => {
    activeDropdown.value = null;
};
</script>

<template>
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <Link href="/" class="flex items-center">
                        <h1 
                            class="text-2xl font-bold"
                            :style="{ color: primaryColor }"
                        >
                            {{ siteConfig.name }}
                        </h1>
                    </Link>
                </div>

                <!-- Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <template v-if="!loading && menus.header.length > 0">
                        <div 
                            v-for="item in menus.header" 
                            :key="item.id"
                            class="relative"
                            @mouseenter="hasChildren(item) ? toggleDropdown(item.id) : null"
                            @mouseleave="closeDropdown"
                        >
                            <!-- Menu Item -->
                            <Link 
                                v-if="!hasChildren(item)"
                                :href="getMenuUrl(item)" 
                                class="text-gray-700 hover:text-gray-900 transition-colors"
                            >
                                {{ item.title }}
                            </Link>

                            <!-- Menu Item with Dropdown -->
                            <button
                                v-else
                                @click="toggleDropdown(item.id)"
                                class="text-gray-700 hover:text-gray-900 transition-colors flex items-center space-x-1"
                            >
                                <span>{{ item.title }}</span>
                                <svg 
                                    class="w-4 h-4 transition-transform"
                                    :class="{ 'rotate-180': activeDropdown === item.id }"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                v-if="hasChildren(item) && activeDropdown === item.id"
                                class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
                            >
                                <div class="py-1">
                                    <Link
                                        v-for="child in item.children"
                                        :key="child.id"
                                        :href="getMenuUrl(child)"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        {{ child.title }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Fallback to hardcoded menu while loading -->
                    <template v-else>
                        <Link href="/" class="text-gray-700 hover:text-gray-900 transition-colors">
                            Shop
                        </Link>
                        <Link href="/" class="text-gray-700 hover:text-gray-900 transition-colors">
                            Categories
                        </Link>
                        <Link href="/" class="text-gray-700 hover:text-gray-900 transition-colors">
                            Deals
                        </Link>
                        <Link href="/" class="text-gray-700 hover:text-gray-900 transition-colors">
                            About
                        </Link>
                    </template>
                </nav>

                <!-- Right Section -->
                <div class="flex items-center space-x-4">
                    <!-- Search -->
                    <div class="relative hidden md:block">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search products..."
                            class="w-64 rounded-lg border border-gray-300 px-4 py-2 pl-10 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <svg 
                            class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path 
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="2" 
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" 
                            />
                        </svg>
                    </div>

                    <!-- Cart Icon (Reusable) -->
                    <CartIcon />

                    <!-- Auth Links -->
                    <Link 
                        href="/admin/login" 
                        class="rounded-lg px-4 py-2 text-sm font-medium text-white hover:opacity-90 transition-opacity"
                        :style="{ backgroundColor: primaryColor }"
                    >
                        Login
                    </Link>
                    <Link 
                        href="/register" 
                        class="rounded-lg border-2 px-4 py-2 text-sm font-medium hover:bg-gray-50 transition-colors"
                        :style="{ borderColor: primaryColor, color: primaryColor }"
                    >
                        Register
                    </Link>
                </div>
            </div>
        </div>
    </header>
</template>
