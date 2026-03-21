<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ThemeLayout from '@/../../themes/cartxis-default/resources/views/layouts/ThemeLayout.vue';
import { useWishlist } from '@/composables/useWishlist';
import { useCurrency } from '@/composables/useCurrency';
import { Heart, ShoppingCart, Trash2, Loader2 } from 'lucide-vue-next';

const { wishlistItems, wishlistCount, loading, removeFromWishlist, moveToCart, fetchWishlist } = useWishlist();
const { formatPrice } = useCurrency();

onMounted(() => {
  fetchWishlist();
});

const handleRemove = async (itemId: number) => {
  await removeFromWishlist(itemId);
};

const handleMoveToCart = async (itemId: number) => {
  await moveToCart(itemId);
};
</script>

<template>
  <ThemeLayout>
    <Head title="My Wishlist" />

    <div class="container mx-auto px-4 py-8">
      <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8 flex items-center gap-3">
          <Heart class="w-7 h-7 text-red-500" />
          <div>
            <h1 class="text-3xl font-bold">My Wishlist</h1>
            <p class="text-gray-600 text-sm mt-1">
              {{ wishlistCount }} {{ wishlistCount === 1 ? 'item' : 'items' }} saved
            </p>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-20">
          <Loader2 class="w-8 h-8 text-blue-600 animate-spin" />
          <span class="ml-3 text-gray-600">Loading your wishlist…</span>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="!wishlistItems.length"
          class="flex flex-col items-center justify-center py-20 text-center"
        >
          <Heart class="w-16 h-16 text-gray-300 mb-4" />
          <h2 class="text-xl font-semibold text-gray-700 mb-2">Your wishlist is empty</h2>
          <p class="text-gray-500 mb-6">Save products you love and come back to them later.</p>
          <Link
            href="/products"
            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            Browse Products
          </Link>
        </div>

        <!-- Wishlist Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="item in wishlistItems"
            :key="item.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col"
          >
            <!-- Product Image -->
            <Link :href="`/product/${item.product.slug}`" class="block aspect-square overflow-hidden bg-gray-100">
              <img
                v-if="item.product.image"
                :src="item.product.image"
                :alt="item.product.name"
                class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
              />
              <div
                v-else
                class="w-full h-full flex items-center justify-center text-5xl"
              >
                📦
              </div>
            </Link>

            <!-- Product Info -->
            <div class="p-4 flex flex-col flex-1">
              <Link
                :href="`/product/${item.product.slug}`"
                class="font-semibold text-gray-900 hover:text-blue-600 transition-colors line-clamp-2 mb-2"
              >
                {{ item.product.name }}
              </Link>

              <!-- Price -->
              <div class="flex items-baseline gap-2 mb-4 mt-auto">
                <span
                  v-if="item.product.special_price"
                  class="text-lg font-bold text-blue-600"
                >
                  {{ formatPrice(item.product.special_price) }}
                </span>
                <span
                  :class="item.product.special_price ? 'text-sm text-gray-400 line-through' : 'text-lg font-bold text-gray-900'"
                >
                  {{ formatPrice(item.product.price) }}
                </span>
              </div>

              <!-- Out of Stock Badge -->
              <span
                v-if="!item.product.is_in_stock"
                class="inline-block mb-3 text-xs font-medium text-red-600 bg-red-50 border border-red-100 rounded px-2 py-1"
              >
                Out of Stock
              </span>

              <!-- Actions -->
              <div class="flex gap-2 mt-auto">
                <button
                  class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  :disabled="!item.product.is_in_stock || loading"
                  @click="handleMoveToCart(item.id)"
                >
                  <ShoppingCart class="w-4 h-4" />
                  Add to Cart
                </button>
                <button
                  class="p-2 text-gray-400 hover:text-red-500 border border-gray-200 rounded-lg hover:border-red-200 transition-colors"
                  aria-label="Remove from wishlist"
                  :disabled="loading"
                  @click="handleRemove(item.id)"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Back to Account -->
        <div class="mt-10">
          <Link
            href="/account"
            class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors"
          >
            ← Back to My Account
          </Link>
        </div>
      </div>
    </div>
  </ThemeLayout>
</template>
