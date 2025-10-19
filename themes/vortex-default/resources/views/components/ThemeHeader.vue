<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CartIcon from './CartIcon.vue';

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

const primaryColor = computed(() => props.theme?.settings?.primary_color ?? '#3b82f6');
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
