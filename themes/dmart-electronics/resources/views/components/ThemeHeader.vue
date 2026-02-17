<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useStorefrontMenu } from '@/composables/useStorefrontMenu';
import { useWishlist } from '@/composables/useWishlist';
import { useCart } from '@/composables/useCart';

interface Props {
    theme?: any;
    siteConfig?: { name: string; url: string; description: string };
    categories?: any[];
}

const props = withDefaults(defineProps<Props>(), {
    theme: null,
    siteConfig: () => ({ name: 'Cartxis', url: '/', description: 'E-commerce Platform' }),
    categories: () => [],
});

const emit = defineEmits<{
    toggleMobileMenu: [];
    toggleCart: [];
    toggleSearch: [];
}>();

const page = usePage();
const auth = computed(() => page.props.auth as any);
const user = computed(() => auth.value?.user);

const { menus, loading: menusLoading, getMenuUrl, hasChildren } = useStorefrontMenu();
const { wishlistCount, fetchWishlist } = useWishlist();
const { itemCount, fetchCart } = useCart();

// Sticky Header
const isScrolled = ref(false);
const stickyEnabled = computed(() => props.theme?.settings?.['features.sticky_header'] ?? true);
const handleScroll = () => { isScrolled.value = window.scrollY > 100; };
onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    document.addEventListener('click', handleDocumentClick);
    fetchCart();
    if (user.value) fetchWishlist();
});
onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('click', handleDocumentClick);
});

// Category dropdown
const showCategories = ref(false);
let catTimeout: ReturnType<typeof setTimeout> | null = null;
const openCategories = () => { if (catTimeout) clearTimeout(catTimeout); showCategories.value = true; };
const closeCategories = () => { catTimeout = setTimeout(() => { showCategories.value = false; }, 150); };

// Contact info from Inertia shared props (editable via Appearance settings)
const contactInfo = computed(() => (page.props as any).contactInfo ?? {
    phone: '+208-555-0112',
    email: 'example@gmail.com',
    hours: 'Sunday - Fri: 9 AM - 6 PM',
    address: '4517 Washington Ave.',
});

// Top bar user dropdown
const showUserMenu = ref(false);
const userMenuRef = ref<HTMLElement | null>(null);
let userMenuTimeout: ReturnType<typeof setTimeout> | null = null;
const openUserMenu = () => {
    if (userMenuTimeout) clearTimeout(userMenuTimeout);
    showUserMenu.value = true;
};
const closeUserMenu = () => {
    userMenuTimeout = setTimeout(() => {
        showUserMenu.value = false;
    }, 150);
};
const toggleUserMenu = () => {
    if (userMenuTimeout) clearTimeout(userMenuTimeout);
    showUserMenu.value = !showUserMenu.value;
};

const handleDocumentClick = (event: MouseEvent) => {
    if (!showUserMenu.value) return;
    const target = event.target as Node | null;
    if (userMenuRef.value && target && !userMenuRef.value.contains(target)) {
        showUserMenu.value = false;
    }
};

// Menu hover dropdowns
const activeDropdown = ref<string | null>(null);
let dropTimeout: ReturnType<typeof setTimeout> | null = null;
const openDropdown = (key: string) => { if (dropTimeout) clearTimeout(dropTimeout); activeDropdown.value = key; };
const closeDropdown = () => { dropTimeout = setTimeout(() => { activeDropdown.value = null; }, 150); };
</script>

<template>
    <header class="w-full">
        <!-- Top bar -->
        <div class="w-full bg-title text-sm text-white" id="header-top">
            <div class="dmart-container h-[50px] flex items-center justify-between">
                <span class="hidden sm:inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-phone"></i>
                    <a :href="'tel:' + contactInfo.phone" class="text-white hover:text-theme-1 transition-colors">
                        {{ contactInfo.phone }}
                    </a>
                </span>
                <span class="hidden md:inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-tag"></i>
                    59% <span class="text-theme-1">discount</span> for all items
                </span>
                <div class="flex w-full sm:w-auto items-center gap-x-7 justify-between sm:justify-evenly">
                    <div class="hidden sm:flex items-center gap-x-1">
                        <i class="fa-solid fa-earth-americas"></i>
                        <select class="text-white bg-transparent border-0 focus:outline-none focus:ring-0 py-1 font-medium text-sm">
                            <option value="en" class="bg-title">English</option>
                            <option value="ur" class="bg-title">Urdu</option>
                            <option value="sp" class="bg-title">Spanish</option>
                        </select>
                    </div>
                    <div class="h-4 w-px bg-icon hidden sm:block"></div>
                    <div class="flex items-center gap-x-4">
                        <div v-if="user" ref="userMenuRef" class="relative" @mouseenter="openUserMenu" @mouseleave="closeUserMenu">
                            <button
                                @click="toggleUserMenu"
                                class="inline-flex items-center gap-x-2 rounded-full border border-white/20 bg-white/5 pl-2 pr-3 py-1 text-white font-medium hover:border-white/40 hover:bg-white/10 transition-colors"
                            >
                                <span class="w-5 h-5 rounded-full bg-white/15 text-[10px] font-semibold flex items-center justify-center uppercase">
                                    {{ user.name?.charAt(0) }}
                                </span>
                                <span class="max-w-[120px] truncate">{{ user.name }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="{ 'rotate-180': showUserMenu }"></i>
                            </button>

                            <Transition name="dropdown">
                                <div
                                    v-if="showUserMenu"
                                    class="absolute right-0 mt-2 w-56 rounded-lg border border-gray-100 bg-white shadow-xl z-[120] overflow-hidden"
                                    @mouseenter="openUserMenu"
                                    @mouseleave="closeUserMenu"
                                >
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                                    </div>
                                    <div class="py-1">
                                        <Link href="/account" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 transition-colors">
                                            <i class="fa-regular fa-user w-4"></i>
                                            <span>My Account</span>
                                        </Link>
                                        <Link href="/account/orders" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 transition-colors">
                                            <i class="fa-solid fa-box-archive w-4"></i>
                                            <span>My Orders</span>
                                        </Link>
                                        <Link href="/account/wishlist" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 transition-colors">
                                            <i class="fa-regular fa-heart w-4"></i>
                                            <span>Wishlist</span>
                                        </Link>
                                        <Link href="/account/addresses" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 transition-colors">
                                            <i class="fa-solid fa-location-dot w-4"></i>
                                            <span>Addresses</span>
                                        </Link>
                                    </div>
                                    <div class="border-t border-gray-100 py-1">
                                        <Link href="/logout" method="post" as="button" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <i class="fa-solid fa-right-from-bracket w-4"></i>
                                            <span>Logout</span>
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                        <Link v-else href="/account" class="inline-flex items-center gap-x-2.5 text-white font-medium hover:text-theme-1 transition-colors">
                            <i class="fa-solid fa-user"></i>
                            <span>My Account</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header: Logo + Menu + Icons -->
        <div
            class="w-full bg-white transition-all duration-300"
            :class="{ 'dmart-header-sticky scrolled': stickyEnabled && isScrolled }"
        >
            <div class="dmart-container flex items-center justify-between py-6">
                <!-- Logo + Categories -->
                <div class="flex items-center gap-x-14">
                    <Link href="/" class="flex-shrink-0">
                        <span class="text-2xl font-bold font-title text-title">
                            {{ siteConfig?.name || 'Cartxis' }}
                        </span>
                    </Link>

                    <!-- Categories Dropdown -->
                    <div
                        class="header-cataegory-item hidden md:block relative"
                        @mouseenter="openCategories"
                        @mouseleave="closeCategories"
                    >
                        <ul class="header-cataegory">
                            <li>
                                <a href="#" @click.prevent>
                                    <span class="left-icon mr-2"><i class="fa-solid fa-border-all"></i></span>
                                    All Categories
                                    <span class="right-icon ml-2"><i class="fa-solid fa-chevron-down"></i></span>
                                </a>
                            </li>
                        </ul>
                        <Transition name="dropdown">
                            <ul
                                v-if="showCategories"
                                class="sub-cataegory"
                                style="visibility: visible; opacity: 1; transform: translateY(0);"
                            >
                                <li v-for="cat in (categories || []).slice(0, 10)" :key="cat.id" :class="{ 'sub-has-dropdown': cat.children?.length }">
                                    <Link :href="`/category/${cat.slug}`">
                                        {{ cat.name }}
                                        <i v-if="cat.children?.length" class="fas fa-angle-right"></i>
                                    </Link>
                                    <ul v-if="cat.children?.length" class="sub-cataegory">
                                        <li v-for="child in cat.children" :key="child.id">
                                            <Link :href="`/category/${child.slug}`">{{ child.name }}</Link>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </Transition>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="flex items-center gap-x-32">
                    <nav class="hidden xl:block">
                        <menu class="text-title text-base font-medium flex items-center gap-x-10 header-menu">
                            <!-- Home with mega menu -->
                            <li class="menu-item relative" @mouseenter="openDropdown('home')" @mouseleave="closeDropdown">
                                <Link href="/" class="flex items-center gap-1 hover:text-theme-1 transition-colors py-2">
                                    Home
                                </Link>
                            </li>
                            <!-- Shop -->
                            <li class="menu-item relative" @mouseenter="openDropdown('shop')" @mouseleave="closeDropdown">
                                <Link href="/products" class="flex items-center gap-1 hover:text-theme-1 transition-colors py-2">
                                    Shop <i class="fa-solid fa-plus text-[8px] ml-1"></i>
                                </Link>
                                <Transition name="dropdown">
                                    <div v-if="activeDropdown === 'shop'" class="dmart-sub-menu absolute left-0 top-full mt-0 w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50">
                                        <Link href="/products" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 font-medium">Shop All</Link>
                                        <Link href="/cart" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 font-medium">Cart</Link>
                                        <Link href="/account/wishlist" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 font-medium">Wishlist</Link>
                                        <Link href="/checkout" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 font-medium">Checkout</Link>
                                    </div>
                                </Transition>
                            </li>
                            <!-- Pages -->
                            <li class="menu-item relative" @mouseenter="openDropdown('pages')" @mouseleave="closeDropdown">
                                <a href="#" class="flex items-center gap-1 hover:text-theme-1 transition-colors py-2">
                                    Pages <i class="fa-solid fa-plus text-[8px] ml-1"></i>
                                </a>
                                <Transition name="dropdown">
                                    <div v-if="activeDropdown === 'pages'" class="dmart-sub-menu absolute left-0 top-full mt-0 w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50">
                                        <Link href="/about-us" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 font-medium">About Us</Link>
                                        <Link href="/faqs" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 font-medium">FAQs</Link>
                                        <Link href="/login" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 font-medium">Login</Link>
                                        <Link href="/register" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 font-medium">Register</Link>
                                    </div>
                                </Transition>
                            </li>
                            <!-- Dynamic menus -->
                            <template v-if="!menusLoading && menus.shop">
                                <li
                                    v-for="item in menus.shop.slice(0, 3)"
                                    :key="item.id"
                                    class="menu-item relative"
                                    @mouseenter="hasChildren(item) ? openDropdown('menu-' + item.id) : null"
                                    @mouseleave="hasChildren(item) ? closeDropdown() : null"
                                >
                                    <Link
                                        :href="getMenuUrl(item)"
                                        class="flex items-center gap-1 hover:text-theme-1 transition-colors py-2"
                                    >
                                        {{ item.title }}
                                        <i v-if="hasChildren(item)" class="fa-solid fa-plus text-[8px] ml-1"></i>
                                    </Link>
                                    <Transition name="dropdown">
                                        <div
                                            v-if="hasChildren(item) && activeDropdown === 'menu-' + item.id"
                                            class="dmart-sub-menu absolute left-0 top-full mt-0 w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50"
                                        >
                                            <Link
                                                v-for="child in item.children"
                                                :key="child.id"
                                                :href="getMenuUrl(child)"
                                                class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-theme-1 transition-colors font-medium"
                                            >
                                                {{ child.title }}
                                            </Link>
                                        </div>
                                    </Transition>
                                </li>
                            </template>
                            <!-- Contact -->
                            <li>
                                <Link href="/contact-us" class="hover:text-theme-1 transition-colors py-2">Contact</Link>
                            </li>
                        </menu>
                    </nav>

                    <!-- Right Icons -->
                    <div class="flex items-center gap-x-8 md:gap-x-10 lg:gap-x-14">
                        <!-- Search -->
                        <button class="hover:text-theme-1 transition-colors text-lg hidden md:inline" @click="$emit('toggleSearch')">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        <!-- Wishlist -->
                        <Link href="/account/wishlist" class="hover:text-theme-1 transition-colors text-lg hidden md:inline relative">
                            <i class="fa-regular fa-heart"></i>
                            <span
                                v-if="wishlistCount > 0"
                                class="absolute -top-2 -right-3 bg-theme-1 rounded-full text-xs text-white w-4 h-4 flex items-center justify-center"
                            >{{ wishlistCount }}</span>
                        </Link>
                        <!-- Cart -->
                        <button class="hover:text-theme-1 transition-colors text-lg relative" @click="$emit('toggleCart')">
                            <i class="fa-solid fa-bag-shopping"></i>
                            <span
                                v-if="itemCount > 0"
                                class="absolute top-0 -right-2 bg-theme-1 rounded-full text-xs text-white w-4 h-4 flex items-center justify-center"
                            >{{ itemCount > 99 ? '99+' : itemCount }}</span>
                        </button>
                        <!-- Mobile menu toggle -->
                        <button class="text-theme-1 lg:hidden" @click="$emit('toggleMobileMenu')">
                            <i class="fa-solid fa-bars-staggered text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(8px);
}
</style>
