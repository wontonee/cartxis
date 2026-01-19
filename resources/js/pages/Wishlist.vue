<script setup lang="ts">
import { onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useWishlist } from '@/composables/useWishlist'
import ThemeLayout from '@/../../themes/cartxis-default/resources/views/layouts/ThemeLayout.vue'
import { Heart, ShoppingCart, Trash2, Loader2 } from 'lucide-vue-next'

const { wishlistItems, wishlistCount, loading, removeFromWishlist, moveToCart, fetchWishlist } = useWishlist()

const showToast = (message: string, type: 'success' | 'error' = 'success') => {
  window.dispatchEvent(new CustomEvent('show-toast', { 
    detail: { message, type } 
  }))
}

onMounted(async () => {
  await fetchWishlist()
})

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    minimumFractionDigits: 0
  }).format(price)
}

const handleRemove = async (itemId: number) => {
  const result = await removeFromWishlist(itemId)
  if (result.success) {
    showToast('Removed from wishlist', 'success')
  } else {
    showToast(result.message || 'Failed to remove item', 'error')
  }
}

const handleMoveToCart = async (itemId: number) => {
  const result = await moveToCart(itemId)
  if (result.success) {
    showToast('Product moved to cart successfully!', 'success')
  } else {
    showToast(result.message || 'Failed to move to cart', 'error')
  }
}
</script>

<template>
  <Head title="My Wishlist" />
  
  <ThemeLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
          <Heart class="w-8 h-8 text-red-500" />
          My Wishlist
        </h1>
        <p class="text-gray-600">
          {{ wishlistCount }} {{ wishlistCount === 1 ? 'item' : 'items' }} saved
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading && wishlistItems.length === 0" class="text-center py-12">
        <Loader2 class="w-12 h-12 animate-spin text-primary-500 mx-auto" />
        <p class="mt-4 text-gray-600">Loading your wishlist...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="wishlistItems.length === 0" class="text-center py-12">
        <Heart class="w-24 h-24 text-gray-300 mx-auto mb-6" />
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Your wishlist is empty</h2>
        <p class="text-gray-600 mb-8">Save items you love by clicking the heart icon</p>
        <Link 
          href="/products" 
          class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
        >
          Start Shopping
        </Link>
      </div>

      <!-- Wishlist Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="item in wishlistItems"
          :key="item.id"
          class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
        >
          <!-- Product Image -->
          <Link :href="`/products/${item.product.slug}`" class="block aspect-square relative overflow-hidden bg-gray-100">
            <img
              v-if="item.product.image"
              :src="item.product.image"
              :alt="item.product.name"
              class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
              No Image
            </div>
            
            <!-- Stock Badge -->
            <div
              v-if="!item.product.is_in_stock"
              class="absolute top-2 right-2 bg-red-500 text-white text-xs font-semibold px-3 py-1 rounded-full"
            >
              Out of Stock
            </div>
          </Link>

          <!-- Product Details -->
          <div class="p-4">
            <Link 
              :href="`/products/${item.product.slug}`"
              class="text-lg font-semibold text-gray-900 hover:text-blue-600 line-clamp-2 mb-2"
            >
              {{ item.product.name }}
            </Link>

            <!-- Price -->
            <div class="mb-4">
              <div v-if="item.product.special_price" class="flex items-center gap-2">
                <span class="text-xl font-bold text-red-600">
                  {{ formatPrice(item.product.special_price) }}
                </span>
                <span class="text-sm text-gray-500 line-through">
                  {{ formatPrice(item.product.price) }}
                </span>
              </div>
              <div v-else class="text-xl font-bold text-gray-900">
                {{ formatPrice(item.product.price) }}
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
              <button
                @click="handleMoveToCart(item.id)"
                :disabled="!item.product.is_in_stock || loading"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed"
              >
                <ShoppingCart class="w-4 h-4" />
                Add to Cart
              </button>
              <button
                @click="handleRemove(item.id)"
                :disabled="loading"
                class="p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors disabled:opacity-50"
                title="Remove from wishlist"
              >
                <Trash2 class="w-5 h-5" />
              </button>
            </div>

            <!-- Added Date -->
            <p class="text-xs text-gray-500 mt-3">
              Added {{ new Date(item.added_at).toLocaleDateString() }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </ThemeLayout>
</template>
