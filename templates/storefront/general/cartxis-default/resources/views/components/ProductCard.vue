<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';
import { useCurrency } from '@/composables/useCurrency';
import { useWishlist } from '@/composables/useWishlist';
import { useThemeSettings } from '@/composables/useThemeSettings';
import { Heart } from 'lucide-vue-next';
import ThemePlaceholderIcon from './ThemePlaceholderIcon.vue';

const { formatPrice } = useCurrency();
const { toggleWishlist, isInWishlist, loading: wishlistLoading } = useWishlist();
const { primary, wishlistEnabled, quickViewEnabled } = useThemeSettings();

interface ProductBadge {
    text: string;
    class?: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    sku: string;
    type?: string;
    price: number;
    special_price: number | null;
    image: string | null;
    rating: number;
    reviews_count: number;
    stock_quantity: number;
    in_stock: boolean;
    has_configurable_attributes: boolean;
    badges?: ProductBadge[];
    brand?: {
        id: number;
        name: string;
        slug: string;
    };
}

const props = withDefaults(defineProps<{
    product: Product;
    compact?: boolean;
}>(), {
    compact: false,
});

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

const hasDiscount = computed(() => props.product.special_price !== null);

const discountPercentage = computed(() => {
    if (!hasDiscount.value) return 0;
    const price = typeof props.product.price === 'string' ? parseFloat(props.product.price) : props.product.price;
    const specialPrice = typeof props.product.special_price === 'string'
        ? parseFloat(props.product.special_price!)
        : props.product.special_price!;
    return Math.round(((price - specialPrice) / price) * 100);
});

const showRatings = computed(() => !props.compact || props.product.reviews_count > 0);

const handleAddToCart = async () => {
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

    for (let i = 0; i < fullStars; i++) stars.push('★');
    if (hasHalfStar) stars.push('☆');
    while (stars.length < 5) stars.push('☆');

    return stars.join('');
};

const handleWishlistToggle = async () => {
    await toggleWishlist(props.product.id);
};
</script>

<template>
    <div class="group relative bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300">
        <Link :href="`/product/${product.slug}`" class="block">
            <div class="relative aspect-square bg-gradient-to-br from-blue-50 to-slate-100 overflow-hidden">
                <img
                    v-if="product.image"
                    :src="product.image"
                    :alt="product.name"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    loading="lazy"
                />
                <div v-else class="absolute inset-0 flex items-center justify-center opacity-50">
                    <ThemePlaceholderIcon />
                </div>

                <div class="absolute top-4 left-4 flex flex-col gap-2">
                    <span
                        v-for="(badge, index) in product.badges ?? []"
                        :key="index"
                        class="px-3 py-1 text-xs font-bold rounded-full"
                        :class="badge.class ?? 'bg-blue-600 text-white'"
                    >
                        {{ badge.text }}
                    </span>

                    <span v-if="hasDiscount" class="px-3 py-1 bg-red-500 text-white text-sm font-bold rounded-full">
                        -{{ discountPercentage }}%
                    </span>

                    <span
                        v-if="product.type && (product.type === 'virtual' || product.type === 'downloadable')"
                        class="px-3 py-1 text-xs font-medium rounded-full inline-flex items-center"
                        :class="{
                            'bg-blue-500 text-white': product.type === 'virtual',
                            'bg-cyan-500 text-white': product.type === 'downloadable',
                        }"
                    >
                        {{ product.type === 'downloadable' ? 'Digital' : 'Virtual' }}
                    </span>
                </div>

                <button
                    v-if="product.in_stock && wishlistEnabled"
                    @click.prevent="handleWishlistToggle"
                    :disabled="wishlistLoading"
                    class="absolute top-4 right-4 p-2 bg-white/90 hover:bg-white rounded-full shadow-md transition-all hover:scale-110 disabled:opacity-50 z-10"
                    title="Add to wishlist"
                >
                    <Heart
                        :class="{ 'fill-red-500 text-red-500': isInWishlist(product.id), 'text-gray-600': !isInWishlist(product.id) }"
                        class="w-5 h-5 transition-colors"
                    />
                </button>

                <div v-if="!product.in_stock" class="absolute top-4 right-4">
                    <span class="px-3 py-1 bg-gray-900 text-white text-sm font-semibold rounded-full">
                        Out of Stock
                    </span>
                </div>

                <div
                    v-if="quickViewEnabled"
                    class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                >
                    <button
                        @click.prevent="emit('quickView', product.slug)"
                        class="px-6 py-3 bg-white text-gray-900 rounded-lg font-semibold hover:bg-gray-100 transition-all transform hover:scale-105 cursor-pointer"
                    >
                        Quick View
                    </button>
                </div>
            </div>
        </Link>

        <div class="p-4">
            <div v-if="product.brand" class="mb-2">
                <Link
                    :href="`/brands/${product.brand.slug}`"
                    class="text-xs text-gray-500 hover:text-gray-700 transition-colors"
                >
                    {{ product.brand.name }}
                </Link>
            </div>

            <Link :href="`/product/${product.slug}`">
                <h3
                    class="font-semibold text-gray-900 mb-2 transition-colors line-clamp-2 min-h-[3rem] group-hover:text-[var(--theme-primary)]"
                >
                    {{ product.name }}
                </h3>
            </Link>

            <div v-if="showRatings" class="flex items-center gap-2 mb-3">
                <div class="flex text-yellow-400 text-sm">
                    {{ renderStars(product.rating) }}
                </div>
                <span class="text-xs text-gray-500">({{ product.reviews_count }})</span>
            </div>

            <div class="mb-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-gray-900">
                        {{ formatPrice(displayPrice) }}
                    </span>
                    <span v-if="hasDiscount" class="text-sm text-gray-500 line-through">
                        {{ formatPrice(typeof product.price === 'string' ? parseFloat(product.price) : product.price) }}
                    </span>
                </div>
            </div>

            <button
                @click="handleAddToCart"
                :disabled="!product.in_stock || addingToCart"
                class="w-full py-3 px-4 rounded-lg font-semibold transition-all disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer text-white hover:shadow-lg"
                :style="product.in_stock ? { backgroundColor: primary } : undefined"
                :class="{ 'bg-gray-300 text-gray-500': !product.in_stock, 'animate-pulse': addingToCart }"
            >
                <template v-if="addingToCart">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span>Adding...</span>
                </template>
                <template v-else-if="showSuccess">
                    <span>Added!</span>
                </template>
                <template v-else-if="product.in_stock">
                    <span>Add to Cart</span>
                </template>
                <template v-else>
                    <span>Out of Stock</span>
                </template>
            </button>
        </div>
    </div>
</template>
