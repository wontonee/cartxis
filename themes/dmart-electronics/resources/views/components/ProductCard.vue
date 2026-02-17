<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';
import { useCurrency } from '@/composables/useCurrency';
import { useWishlist } from '@/composables/useWishlist';

const { formatPrice } = useCurrency();
const { toggleWishlist, isInWishlist, loading: wishlistLoading } = useWishlist();

interface Product {
    id: number;
    name: string;
    slug: string;
    sku?: string;
    type?: string;
    price: number;
    special_price: number | null;
    image: string | null;
    rating: number;
    reviews_count: number;
    stock_quantity?: number;
    in_stock: boolean;
    has_configurable_attributes?: boolean;
    brand?: { id: number; name: string; slug: string };
    badges?: Array<{ text: string; class: string }>;
}

const props = defineProps<{
    product: Product;
    variant?: 'default' | 'featured' | 'list';
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

const hasDiscount = computed(() => props.product.special_price != null && props.product.special_price < props.product.price);

const discountPercent = computed(() => {
    if (!hasDiscount.value) return 0;
    return Math.round(((props.product.price - props.product.special_price!) / props.product.price) * 100);
});

const handleAddToCart = async () => {
    addingToCart.value = true;
    try {
        const result = await addToCart(props.product.id, 1);
        showSuccess.value = true;
        setTimeout(() => { showSuccess.value = false; }, 2000);
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: result?.message || 'Product added to cart!', type: 'success' }
        }));
    } catch (e) {
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: 'Failed to add to cart', type: 'error' }
        }));
    } finally {
        addingToCart.value = false;
    }
};

const starArray = computed(() => {
    const rating = props.product.rating || 0;
    return Array.from({ length: 5 }, (_, i) => i < Math.round(rating));
});
</script>

<template>
    <div class="dmart-product-card group" :class="{ 'flex gap-4': variant === 'list' }">
        <!-- Image -->
        <div class="product-image relative" :class="variant === 'list' ? 'w-48 flex-shrink-0' : 'aspect-square'">
            <Link :href="`/product/${product.slug}`" class="block w-full h-full p-4">
                <img
                    :src="product.image || `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300' fill='%23f3f4f6'%3E%3Crect width='300' height='300'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%239ca3af' font-size='14'%3ENo Image%3C/text%3E%3C/svg%3E`"
                    :alt="product.name"
                    class="w-full h-full object-contain"
                    loading="lazy"
                />
            </Link>

            <!-- Badges -->
            <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10">
                <span v-if="hasDiscount" class="dmart-badge dmart-badge-sale">-{{ discountPercent }}%</span>
                <span v-if="!product.in_stock" class="dmart-badge bg-gray-500 text-white">Sold Out</span>
                <template v-if="product.badges">
                    <span v-for="badge in product.badges" :key="badge.text" class="dmart-badge" :class="badge.class === 'badge-new' ? 'dmart-badge-new' : badge.class === 'badge-hot' ? 'dmart-badge-hot' : ''">
                        {{ badge.text }}
                    </span>
                </template>
            </div>
        </div>

        <!-- Info -->
        <div class="p-4" :class="variant === 'list' ? 'flex-1 py-4 px-0' : ''">
            <!-- Title -->
            <h4>
                <Link :href="`/product/${product.slug}`" class="block font-semibold text-sm leading-snug hover:text-theme-1 transition-colors line-clamp-2 text-title font-title">
                    {{ product.name }}
                </Link>
            </h4>

            <!-- Rating -->
            <div class="dmart-stars mt-2 mb-1.5 flex items-center gap-x-2.5" v-if="product.rating > 0">
                <div>
                    <span v-for="(filled, i) in starArray" :key="i">
                        <i :class="filled ? 'fa-solid fa-star text-sm text-[#F9A000]' : 'fa-solid fa-star text-sm text-[#CFCFCF]'"></i>
                    </span>
                </div>
                <span class="text-[#A1A1A1] text-xs">({{ product.reviews_count }}) Reviews</span>
            </div>

            <!-- Price -->
            <div class="flex items-center gap-x-2.5">
                <span class="text-title font-medium">
                    {{ formatPrice(displayPrice) }}
                </span>
                <span v-if="hasDiscount" class="font-medium line-through text-[#A1A1A1]">
                    {{ formatPrice(product.price) }}
                </span>
            </div>

            <!-- Action Buttons (matching original 36px circle style) -->
            <div class="flex items-center gap-x-2.5 mt-2">
                <button
                    @click.prevent="toggleWishlist(product.id)"
                    class="size-[36px] rounded-full bg-[#F2F2F2] flex items-center justify-center transition-colors hover:bg-theme-1/10 hover:text-theme-1"
                    :class="[isInWishlist(product.id) ? '!bg-theme-1 !text-white' : 'text-title']"
                    :disabled="wishlistLoading"
                >
                    <i :class="isInWishlist(product.id) ? 'fa-solid fa-heart' : 'fa-regular fa-heart'"></i>
                </button>
                <button
                    @click.prevent="handleAddToCart"
                    class="size-[36px] rounded-full text-title bg-[#F2F2F2] flex items-center justify-center transition-colors hover:bg-theme-1/10 hover:text-theme-1"
                    :class="{ '!bg-theme-1 !text-white': showSuccess }"
                    :disabled="addingToCart || !product.in_stock"
                >
                    <i class="fa-solid fa-bag-shopping"></i>
                </button>
            </div>

            <!-- List variant: full add to cart button -->
            <div v-if="variant === 'list'" class="flex items-center gap-3 mt-4">
                <button
                    @click="handleAddToCart"
                    class="dmart-btn dmart-btn-primary text-sm"
                    :disabled="addingToCart || !product.in_stock"
                >
                    <i class="fa-solid fa-bag-shopping mr-2"></i>
                    {{ showSuccess ? 'Added!' : product.in_stock ? 'Add to Cart' : 'Out of Stock' }}
                </button>
            </div>
        </div>
    </div>
</template>
