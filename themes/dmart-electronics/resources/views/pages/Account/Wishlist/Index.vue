<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ThemeLayout from '../../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import { useWishlist } from '../../../../../../../resources/js/composables/useWishlist';
import { Heart, ShoppingCart, Trash2, Loader2 } from 'lucide-vue-next';
import { useCurrency } from '../../../../../../../resources/js/composables/useCurrency';

const { wishlistItems, wishlistCount, loading, removeFromWishlist, moveToCart, fetchWishlist } = useWishlist();
const { formatPrice } = useCurrency();

const showToast = (message: string, type: 'success' | 'error' = 'success') => {
    window.dispatchEvent(new CustomEvent('show-toast', {
        detail: { message, type },
    }));
};

onMounted(async () => {
    await fetchWishlist();
});

const handleRemove = async (itemId: number) => {
    const result = await removeFromWishlist(itemId);

    if (result.success) {
        showToast('Removed from wishlist', 'success');
        return;
    }

    showToast(result.message || 'Failed to remove item', 'error');
};

const handleMoveToCart = async (itemId: number) => {
    const result = await moveToCart(itemId);

    if (result.success) {
        showToast('Product moved to cart successfully!', 'success');
        return;
    }

    showToast(result.message || 'Failed to move to cart', 'error');
};

const breadcrumbs = [
    { label: 'My Account', url: '/account' },
    { label: 'Wishlist' },
];
</script>

<template>
    <ThemeLayout>
        <Head title="My Wishlist" />
        <Breadcrumb :items="breadcrumbs" />

        <section class="py-8 lg:py-12">
            <div class="dmart-container">
                <div class="mb-6">
                    <h1 class="text-2xl font-extrabold flex items-center gap-2 text-title font-title">
                        <Heart class="w-6 h-6 text-theme-1" />
                        My Wishlist
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ wishlistCount }} {{ wishlistCount === 1 ? 'item' : 'items' }} saved
                    </p>
                </div>

                <div v-if="loading && wishlistItems.length === 0" class="text-center py-16 border rounded-2xl">
                    <Loader2 class="w-10 h-10 animate-spin mx-auto text-theme-1" />
                    <p class="text-sm text-gray-500 mt-3">Loading your wishlist...</p>
                </div>

                <div v-else-if="wishlistItems.length === 0" class="text-center py-16 border rounded-2xl">
                    <Heart class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                    <p class="text-gray-500 mb-4">Your wishlist is empty.</p>
                    <Link href="/products" class="dmart-btn dmart-btn-primary">Start Shopping</Link>
                </div>

                <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="item in wishlistItems"
                        :key="item.id"
                        class="border rounded-2xl p-4 flex gap-4"
                    >
                        <Link :href="item.product.slug ? `/product/${item.product.slug}` : '/products'" class="w-24 h-24 rounded-xl overflow-hidden bg-gray-50 flex-shrink-0">
                            <img
                                v-if="item.product.image"
                                :src="item.product.image"
                                :alt="item.product.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-xs text-gray-400">No image</div>
                        </Link>

                        <div class="flex-1 min-w-0">
                            <Link :href="item.product.slug ? `/product/${item.product.slug}` : '/products'" class="font-semibold text-sm hover:text-theme-1 line-clamp-2">
                                {{ item.product.name }}
                            </Link>
                            <div class="mt-2 font-bold text-theme-1">
                                {{ formatPrice(item.product.special_price || item.product.price) }}
                            </div>
                            <div class="mt-3 flex gap-2">
                                <button
                                    @click="handleMoveToCart(item.id)"
                                    :disabled="!item.product.is_in_stock || loading"
                                    class="dmart-btn dmart-btn-primary text-xs px-3 py-2 disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    <ShoppingCart class="w-3.5 h-3.5" />
                                    Add to Cart
                                </button>
                                <button
                                    @click="handleRemove(item.id)"
                                    :disabled="loading"
                                    class="dmart-btn dmart-btn-outline text-xs px-3 py-2 text-red-600 border-red-200 hover:bg-red-50"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                    Remove
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-2" v-if="!item.product.is_in_stock">Currently out of stock</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>
