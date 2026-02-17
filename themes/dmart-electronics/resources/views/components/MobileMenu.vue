<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { X, ChevronDown, ChevronRight, Home, ShoppingBag, Heart, User, Phone, LogOut } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useStorefrontMenu } from '@/composables/useStorefrontMenu';

interface Props {
    open: boolean;
    categories?: any[];
}

defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const { menus, getMenuUrl, hasChildren } = useStorefrontMenu();
const expandedItems = ref<Set<number>>(new Set());
const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);

const toggleExpand = (id: number) => {
    if (expandedItems.value.has(id)) {
        expandedItems.value.delete(id);
    } else {
        expandedItems.value.add(id);
    }
};
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div v-if="open" class="fixed inset-0 bg-black/50 z-[60]" @click="$emit('close')" />
    </Transition>

    <!-- Drawer -->
    <Transition name="slide-left">
        <div v-if="open" class="fixed left-0 top-0 bottom-0 w-[300px] bg-white z-[70] overflow-y-auto shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b bg-title">
                <span class="text-white font-bold text-lg font-title">Menu</span>
                <button @click="$emit('close')" class="text-white p-1"><X class="w-5 h-5" /></button>
            </div>

            <!-- Nav Links -->
            <nav class="py-2">
                <Link href="/" class="flex items-center gap-3 px-5 py-3 text-gray-800 hover:bg-gray-50 font-medium" @click="$emit('close')">
                    <Home class="w-4 h-4" /> Home
                </Link>
                <Link href="/products" class="flex items-center gap-3 px-5 py-3 text-gray-800 hover:bg-gray-50 font-medium" @click="$emit('close')">
                    <ShoppingBag class="w-4 h-4" /> Shop
                </Link>

                <!-- Categories -->
                <div class="border-t mt-2 pt-2">
                    <span class="px-5 py-2 text-xs font-bold uppercase text-gray-400 block">Categories</span>
                    <template v-for="cat in (categories || []).slice(0, 8)" :key="cat.id">
                        <Link
                            :href="`/category/${cat.slug}`"
                            class="flex items-center justify-between px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50"
                            @click="$emit('close')"
                        >
                            <span>{{ cat.name }}</span>
                            <span class="text-xs text-gray-400">({{ cat.products_count }})</span>
                        </Link>
                    </template>
                </div>

                <!-- Dynamic Menu -->
                <template v-if="menus.shop && menus.shop.length > 0">
                    <div class="border-t mt-2 pt-2">
                        <template v-for="item in menus.shop" :key="item.id">
                            <div v-if="hasChildren(item)">
                                <button
                                    class="flex items-center justify-between w-full px-5 py-3 text-gray-800 font-medium hover:bg-gray-50"
                                    @click="toggleExpand(item.id)"
                                >
                                    <span>{{ item.title }}</span>
                                    <ChevronDown class="w-4 h-4 transition-transform" :class="{ 'rotate-180': expandedItems.has(item.id) }" />
                                </button>
                                <Transition name="expand">
                                    <div v-if="expandedItems.has(item.id)" class="bg-gray-50 pl-8">
                                        <Link
                                            v-for="child in item.children"
                                            :key="child.id"
                                            :href="getMenuUrl(child)"
                                            class="block py-2.5 px-4 text-sm text-gray-600 hover:text-theme-1"
                                            @click="$emit('close')"
                                        >
                                            {{ child.title }}
                                        </Link>
                                    </div>
                                </Transition>
                            </div>
                            <Link v-else :href="getMenuUrl(item)" class="flex items-center px-5 py-3 text-gray-800 font-medium hover:bg-gray-50" @click="$emit('close')">
                                {{ item.title }}
                            </Link>
                        </template>
                    </div>
                </template>

                <!-- Bottom Links -->
                <div class="border-t mt-2 pt-2">
                    <Link href="/account/wishlist" class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50" @click="$emit('close')">
                        <Heart class="w-4 h-4" /> Wishlist
                    </Link>
                    <Link href="/account" class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50" @click="$emit('close')">
                        <User class="w-4 h-4" /> My Account
                    </Link>
                    <Link v-if="user" href="/logout" method="post" as="button" class="w-full flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50 text-left" @click="$emit('close')">
                        <LogOut class="w-4 h-4" /> Logout
                    </Link>
                    <Link href="/contact-us" class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50" @click="$emit('close')">
                        <Phone class="w-4 h-4" /> Contact Us
                    </Link>
                </div>
            </nav>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-left-enter-active, .slide-left-leave-active { transition: transform 0.3s ease; }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }
</style>
