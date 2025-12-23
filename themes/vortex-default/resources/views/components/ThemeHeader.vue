<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import CartIcon from './CartIcon.vue';
import { useStorefrontMenu } from '@/composables/useStorefrontMenu';
import axios from 'axios';

interface SearchSuggestion {
    id: number;
    name: string;
    slug: string;
    price: number;
    image: string;
}

interface Props {
    theme?: any;
    siteConfig?: {
        name: string;
        url: string;
        description: string;
    };
    categories?: any[];
}

const props = withDefaults(defineProps<Props>(), {
    theme: null,
    siteConfig: () => ({
        name: 'Vortex',
        url: '/',
        description: 'E-commerce Platform'
    }),
    categories: () => []
});

const page = usePage();
const auth = computed(() => page.props.auth as any);
const user = computed(() => auth.value?.user);

const searchQuery = ref('');
const suggestions = ref<SearchSuggestion[]>([]);
const showSuggestions = ref(false);
const selectedIndex = ref(-1);
const searchInputRef = ref<HTMLInputElement | null>(null);
const isSearching = ref(false);
let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

const { menus, loading, getMenuUrl, hasChildren } = useStorefrontMenu();
const activeDropdown = ref<number | null>(null);
const showUserMenu = ref(false);
const showCategoriesDropdown = ref(false);
let closeTimeout: ReturnType<typeof setTimeout> | null = null;
let userMenuTimeout: ReturnType<typeof setTimeout> | null = null;
let categoriesTimeout: ReturnType<typeof setTimeout> | null = null;

const primaryColor = computed(() => props.theme?.settings?.primary_color ?? '#3b82f6');

const toggleDropdown = (itemId: number) => {
    if (closeTimeout) {
        clearTimeout(closeTimeout);
        closeTimeout = null;
    }
    activeDropdown.value = activeDropdown.value === itemId ? null : itemId;
};

const openDropdown = (itemId: number) => {
    if (closeTimeout) {
        clearTimeout(closeTimeout);
        closeTimeout = null;
    }
    activeDropdown.value = itemId;
};

const closeDropdown = () => {
    if (closeTimeout) {
        clearTimeout(closeTimeout);
    }
    closeTimeout = setTimeout(() => {
        activeDropdown.value = null;
        closeTimeout = null;
    }, 150);
};

const toggleUserMenu = () => {
    if (userMenuTimeout) {
        clearTimeout(userMenuTimeout);
        userMenuTimeout = null;
    }
    showUserMenu.value = !showUserMenu.value;
};

const closeUserMenu = () => {
    if (userMenuTimeout) {
        clearTimeout(userMenuTimeout);
    }
    userMenuTimeout = setTimeout(() => {
        showUserMenu.value = false;
        userMenuTimeout = null;
    }, 150);
};

const openUserMenu = () => {
    if (userMenuTimeout) {
        clearTimeout(userMenuTimeout);
        userMenuTimeout = null;
    }
    showUserMenu.value = true;
};

const toggleCategoriesDropdown = () => {
    if (categoriesTimeout) {
        clearTimeout(categoriesTimeout);
        categoriesTimeout = null;
    }
    showCategoriesDropdown.value = !showCategoriesDropdown.value;
};

const closeCategoriesDropdown = () => {
    if (categoriesTimeout) {
        clearTimeout(categoriesTimeout);
    }
    categoriesTimeout = setTimeout(() => {
        showCategoriesDropdown.value = false;
        categoriesTimeout = null;
    }, 150);
};

const openCategoriesDropdown = () => {
    if (categoriesTimeout) {
        clearTimeout(categoriesTimeout);
        categoriesTimeout = null;
    }
    showCategoriesDropdown.value = true;
};

const fetchSuggestions = async () => {
    if (searchQuery.value.trim().length < 2) {
        suggestions.value = [];
        showSuggestions.value = false;
        isSearching.value = false;
        return;
    }

    try {
        isSearching.value = true;
        const response = await axios.get(`/search/suggestions?q=${encodeURIComponent(searchQuery.value.trim())}&limit=8`);
        suggestions.value = response.data.suggestions || [];
        showSuggestions.value = suggestions.value.length > 0;
        selectedIndex.value = -1;
    } catch (error) {
        console.error('Error fetching search suggestions:', error);
        suggestions.value = [];
        showSuggestions.value = false;
    } finally {
        isSearching.value = false;
    }
};

const onSearchInput = () => {
    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }
    
    debounceTimeout = setTimeout(() => {
        fetchSuggestions();
    }, 300);
};

const handleSearch = (suggestion?: SearchSuggestion) => {
    if (suggestion) {
        window.location.href = `/product/${suggestion.slug}`;
    } else if (searchQuery.value.trim().length > 0) {
        window.location.href = `/search?q=${encodeURIComponent(searchQuery.value.trim())}`;
    }
    showSuggestions.value = false;
};

const handleKeyDown = (event: KeyboardEvent) => {
    if (!showSuggestions.value || suggestions.value.length === 0) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            selectedIndex.value = Math.min(selectedIndex.value + 1, suggestions.value.length - 1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            selectedIndex.value = Math.max(selectedIndex.value - 1, -1);
            break;
        case 'Enter':
            event.preventDefault();
            if (selectedIndex.value >= 0 && selectedIndex.value < suggestions.value.length) {
                handleSearch(suggestions.value[selectedIndex.value]);
            } else {
                handleSearch();
            }
            break;
        case 'Escape':
            showSuggestions.value = false;
            selectedIndex.value = -1;
            break;
    }
};

const closeSuggestions = () => {
    setTimeout(() => {
        showSuggestions.value = false;
        selectedIndex.value = -1;
    }, 200);
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (searchInputRef.value && !searchInputRef.value.contains(target) && !target.closest('.search-suggestions')) {
        showSuggestions.value = false;
        selectedIndex.value = -1;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }
});
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
                            @mouseenter="hasChildren(item) ? openDropdown(item.id) : null"
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
                                @mouseenter="openDropdown(item.id)"
                                @mouseleave="closeDropdown"
                            >
                                <div class="py-1">
                                    <!-- Use categories prop for "Categories" menu item, otherwise use children -->
                                    <template v-if="item.title === 'Categories' && categories && categories.length > 0">
                                        <Link
                                            v-for="category in categories"
                                            :key="category.id"
                                            :href="`/category/${category.slug}`"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                        >
                                            {{ category.name }}
                                        </Link>
                                    </template>
                                    <template v-else>
                                        <Link
                                            v-for="child in item.children"
                                            :key="child.id"
                                            :href="getMenuUrl(child)"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                        >
                                            {{ child.title }}
                                        </Link>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Fallback to hardcoded menu while loading -->
                    <template v-else>
                        <Link href="/products" class="text-gray-700 hover:text-gray-900 transition-colors">
                            Shop
                        </Link>
                        
                        <!-- Categories Dropdown -->
                        <div class="relative" @mouseenter="openCategoriesDropdown" @mouseleave="closeCategoriesDropdown">
                            <button 
                                @click="toggleCategoriesDropdown"
                                class="text-gray-700 hover:text-gray-900 transition-colors flex items-center space-x-1"
                            >
                                <span>Categories</span>
                                <svg 
                                    class="w-4 h-4 transition-transform duration-200" 
                                    :class="{ 'rotate-180': showCategoriesDropdown }"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <!-- Categories Dropdown Menu -->
                            <div 
                                v-show="showCategoriesDropdown"
                                class="absolute left-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
                            >
                                <div class="py-2">
                                    <template v-if="categories && categories.length > 0">
                                        <Link
                                            v-for="category in categories"
                                            :key="category.id"
                                            :href="`/category/${category.slug}`"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                        >
                                            <div class="flex items-center justify-between">
                                                <span>{{ category.name }}</span>
                                                <span v-if="category.children && category.children.length > 0" class="text-xs text-gray-400">
                                                    ({{ category.children.length }})
                                                </span>
                                            </div>
                                        </Link>
                                        <hr class="my-2" />
                                        <Link
                                            href="/products"
                                            class="block px-4 py-2 text-sm text-indigo-600 hover:bg-gray-100 font-medium transition-colors"
                                        >
                                            View All Categories →
                                        </Link>
                                    </template>
                                    <div v-else class="px-4 py-3 text-sm text-gray-500">
                                        No categories available
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <Link href="/deals" class="text-gray-700 hover:text-gray-900 transition-colors">
                            Deals
                        </Link>
                        <Link href="/about-us" class="text-gray-700 hover:text-gray-900 transition-colors">
                            About
                        </Link>
                    </template>
                </nav>

                <!-- Right Section -->
                <div class="flex items-center space-x-4">
                    <!-- Search -->
                    <div class="relative hidden md:block">
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search products..."
                            class="w-64 rounded-lg border border-gray-300 px-4 py-2 pl-10 pr-10 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            @input="onSearchInput"
                            @keydown="handleKeyDown"
                            @focus="searchQuery.length >= 2 && fetchSuggestions()"
                            @blur="closeSuggestions"
                            autocomplete="off"
                        />
                        <!-- Loading Spinner -->
                        <svg 
                            v-if="isSearching"
                            class="absolute left-3 top-2.5 h-5 w-5 text-indigo-500 animate-spin" 
                            fill="none" 
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <!-- Search Icon -->
                        <svg 
                            v-else
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
                        <button
                            v-if="searchQuery"
                            @click="handleSearch()"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 cursor-pointer"
                            type="button"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>

                        <!-- Search Suggestions Dropdown -->
                        <div
                            v-if="showSuggestions && suggestions.length > 0"
                            class="search-suggestions absolute left-0 right-0 top-full mt-2 bg-white rounded-lg shadow-lg border border-gray-200 max-h-96 overflow-y-auto z-50"
                        >
                            <div
                                v-for="(suggestion, index) in suggestions"
                                :key="suggestion.id"
                                @click="handleSearch(suggestion)"
                                :class="[
                                    'flex items-center gap-3 px-4 py-3 cursor-pointer transition-colors',
                                    selectedIndex === index ? 'bg-indigo-50' : 'hover:bg-gray-50'
                                ]"
                            >
                                <!-- Product Image -->
                                <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded overflow-hidden">
                                    <img
                                        v-if="suggestion.image"
                                        :src="suggestion.image"
                                        :alt="suggestion.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xl">
                                        📦
                                    </div>
                                </div>
                                
                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ suggestion.name }}
                                    </p>
                                    <p class="text-sm text-indigo-600 font-semibold">
                                        ${{ Number(suggestion.price).toFixed(2) }}
                                    </p>
                                </div>

                                <!-- Arrow Icon -->
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Icon (Reusable) -->
                    <CartIcon />

                    <!-- Auth Links - Show if NOT logged in -->
                    <template v-if="!user">
                        <Link 
                            href="/login" 
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
                    </template>

                    <!-- User Menu - Show if logged in -->
                    <div v-else class="relative" @mouseenter="openUserMenu" @mouseleave="closeUserMenu">
                        <button
                            @click="toggleUserMenu"
                            class="flex items-center space-x-2 rounded-lg px-3 py-2 hover:bg-gray-100 transition-colors"
                        >
                            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-medium">
                                {{ user.name?.charAt(0).toUpperCase() }}
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ user.name }}</span>
                            <svg 
                                class="w-4 h-4 text-gray-500 transition-transform"
                                :class="{ 'rotate-180': showUserMenu }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-if="showUserMenu"
                            class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-[100]"
                            @mouseenter="openUserMenu"
                            @mouseleave="closeUserMenu"
                        >
                            <div class="py-1">
                                <Link
                                    href="/account"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center space-x-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    <span>Dashboard</span>
                                </Link>
                                <Link
                                    href="/account/orders"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center space-x-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span>My Orders</span>
                                </Link>
                                <Link
                                    href="/account/profile"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center space-x-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>Profile</span>
                                </Link>
                                <Link
                                    href="/account/addresses"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center space-x-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Addresses</span>
                                </Link>
                                <div class="border-t border-gray-100 my-1"></div>
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center space-x-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Logout</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
