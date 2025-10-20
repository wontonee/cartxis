<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useCart } from '@/Composables/useCart';

interface Product {
    id: number;
    name: string;
    slug: string;
    sku: string;
    price: number;
    special_price: number | null;
    image: string | null;
    rating: number;
    reviews_count: number;
    stock_quantity: number;
    in_stock: boolean;
    has_configurable_attributes: boolean;
    brand?: {
        id: number;
        name: string;
        slug: string;
    };
}

const props = defineProps<{
    product: Product;
}>();

const emit = defineEmits<{
    quickView: [slug: string];
}>();

const { addToCart, loading } = useCart();

const addingToCart = ref(false);
const showSuccess = ref(false);

const displayPrice = computed(() => {
    const price = props.product.special_price ?? props.product.price;
    return typeof price === 'string' ? parseFloat(price) : price;
});

const hasDiscount = computed(() => {
    return props.product.special_price !== null;
});

const discountPercentage = computed(() => {
    if (!hasDiscount.value) return 0;
    const price = typeof props.product.price === 'string' ? parseFloat(props.product.price) : props.product.price;
    const specialPrice = typeof props.product.special_price === 'string' ? parseFloat(props.product.special_price!) : props.product.special_price!;
    return Math.round(((price - specialPrice) / price) * 100);
});

const handleAddToCart = async () => {
    // If product has configurable attributes, open quick view modal to select attributes
    if (props.product.has_configurable_attributes) {
        emit('quickView', props.product.slug);
        return;
    }

    addingToCart.value = true;
    
    const result = await addToCart(props.product.id, 1);
    
    if (result.success) {
        showSuccess.value = true;
        setTimeout(() => {
            showSuccess.value = false;
        }, 2000);
    }
    
    addingToCart.value = false;
};

const renderStars = (rating: number) => {
    const stars = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    
    for (let i = 0; i < fullStars; i++) {
        stars.push('★');
    }
    if (hasHalfStar) {
        stars.push('☆');
    }
    while (stars.length < 5) {
        stars.push('☆');
    }
    
    return stars.join('');
};
</script>

<template>
    <div class="group relative bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300">
        <!-- Product Image -->
        <Link :href="`/product/${product.slug}`" class="block">
            <div class="relative aspect-square bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                <img
                    v-if="product.image"
                    :src="product.image"
                    :alt="product.name"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    loading="lazy"
                />
                <div v-else class="absolute inset-0 flex items-center justify-center text-6xl opacity-50">
                    📦
                </div>
                
                <!-- Discount Badge -->
                <div v-if="hasDiscount" class="absolute top-4 left-4">
                    <span class="px-3 py-1 bg-red-500 text-white text-sm font-bold rounded-full">
                        -{{ discountPercentage }}%
                    </span>
                </div>
                
                <!-- Stock Status -->
                <div v-if="!product.in_stock" class="absolute top-4 right-4">
                    <span class="px-3 py-1 bg-gray-900 text-white text-sm font-semibold rounded-full">
                        Out of Stock
                    </span>
                </div>
                
                <!-- Quick View Overlay -->
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <button
                        @click.prevent="emit('quickView', product.slug)"
                        class="px-6 py-3 bg-white text-gray-900 rounded-lg font-semibold hover:bg-gray-100 transition-all transform hover:scale-105 cursor-pointer"
                    >
                        Quick View
                    </button>
                </div>
            </div>
        </Link>

        <!-- Product Info -->
        <div class="p-4">
            <!-- Brand -->
            <div v-if="product.brand" class="mb-2">
                <Link
                    :href="`/brands/${product.brand.slug}`"
                    class="text-xs text-gray-500 hover:text-gray-700 transition-colors"
                >
                    {{ product.brand.name }}
                </Link>
            </div>

            <!-- Product Name -->
            <Link :href="`/product/${product.slug}`">
                <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors line-clamp-2 min-h-[3rem]">
                    {{ product.name }}
                </h3>
            </Link>

            <!-- Rating & Reviews -->
            <div class="flex items-center gap-2 mb-3">
                <div class="flex text-yellow-400 text-sm">
                    {{ renderStars(product.rating) }}
                </div>
                <span class="text-xs text-gray-500">({{ product.reviews_count }})</span>
            </div>

            <!-- Price -->
            <div class="mb-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-gray-900">
                        ${{ displayPrice.toFixed(2) }}
                    </span>
                    <span v-if="hasDiscount" class="text-sm text-gray-500 line-through">
                        ${{ (typeof product.price === 'string' ? parseFloat(product.price) : product.price).toFixed(2) }}
                    </span>
                </div>
            </div>

            <!-- Add to Cart / Select Options Button -->
            <button
                @click="handleAddToCart"
                :disabled="!product.in_stock || addingToCart"
                class="w-full py-3 px-4 rounded-lg font-semibold transition-all disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer"
                :class="{
                    'bg-indigo-600 hover:bg-indigo-700 text-white hover:shadow-lg': product.in_stock,
                    'bg-gray-300 text-gray-500': !product.in_stock,
                    'animate-pulse': addingToCart
                }"
            >
                <!-- Loading State -->
                <template v-if="addingToCart">
                    <svg
                        class="animate-spin h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Adding...</span>
                </template>

                <!-- Success State -->
                <template v-else-if="showSuccess">
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Added!</span>
                </template>

                <!-- Add to Cart Button -->
                <template v-else-if="product.in_stock">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Add to Cart</span>
                </template>

                <!-- Out of Stock -->
                <template v-else>
                    <span>Out of Stock</span>
                </template>
            </button>
        </div>

        <!-- Success Toast -->
        <Transition name="slide-up">
            <div
                v-if="showSuccess"
                class="absolute bottom-4 left-4 right-4 px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-medium text-center shadow-lg"
            >
                Added to cart! ✓
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.3s ease;
}

.slide-up-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.slide-up-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}
</style>
