<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../components/Breadcrumb.vue';
import ProductCard from '../../components/ProductCard.vue';
import QuickViewModal from '../../components/QuickViewModal.vue';
import { useCart } from '@/composables/useCart';
import { useCurrency } from '@/composables/useCurrency';
import { useWishlist } from '@/composables/useWishlist';
import {
    Star, Heart, Share2, Minus, Plus, ShoppingCart, Truck, Shield, RotateCcw,
    ChevronLeft, ChevronRight, ZoomIn, Check, X,
} from 'lucide-vue-next';

interface ProductImage { id: number; url: string; alt: string; }
interface Attribute { id: number; code: string; label: string; options: Array<{ id: number; label: string; swatch_value?: string }>; }
interface Review { id: number; author: string; rating: number; title: string; comment: string; created_at: string; }
interface Product {
    id: number; name: string; slug: string; sku: string; price: number;
    special_price: number | null; description: string; short_description: string;
    images: ProductImage[]; image: string | null; rating: number; reviews_count: number;
    in_stock: boolean; stock_quantity: number; meta_title?: string; meta_description?: string;
    brand?: { id: number; name: string; slug: string };
    category?: { id: number; name: string; slug: string };
    badges?: Array<{ text: string; class: string }>;
    reviews?: Review[];
    specifications?: Array<{ label: string; value: string }>;
}

interface Props {
    product: Product;
    relatedProducts?: Product[];
    configurableAttributes?: Attribute[];
    theme: { name: string; slug: string };
    siteConfig: { name: string };
}

const props = defineProps<Props>();
const { addToCart } = useCart();
const { formatPrice } = useCurrency();
const { isInWishlist, toggleWishlist } = useWishlist();

// Image Gallery
const allImages = computed(() => {
    if (props.product.images?.length) return props.product.images;
    if (props.product.image) return [{ id: 0, url: props.product.image, alt: props.product.name }];
    return [{ id: 0, url: '/images/placeholder.png', alt: props.product.name }];
});
const activeImage = ref(0);
const zoomActive = ref(false);

// Variants
const selectedOptions = ref<Record<string, number>>({});
const quantity = ref(1);
const incrementQty = () => { if (quantity.value < (props.product.stock_quantity || 99)) quantity.value++; };
const decrementQty = () => { if (quantity.value > 1) quantity.value--; };

// Tabs
type TabKey = 'description' | 'specifications' | 'reviews';
const activeDetailTab = ref<TabKey>('description');
const tabs: Array<{ key: TabKey; label: string }> = [
    { key: 'description', label: 'Description' },
    { key: 'specifications', label: 'Specifications' },
    { key: 'reviews', label: 'Reviews' },
];

// Discount
const discount = computed(() => {
    if (!props.product.special_price || !props.product.price) return 0;
    return Math.round(((props.product.price - props.product.special_price) / props.product.price) * 100);
});

// Price
const effectivePrice = computed(() => props.product.special_price || props.product.price);

// Add to cart
const adding = ref(false);
const handleAddToCart = async () => {
    adding.value = true;
    try {
        const result = await addToCart(props.product.id, quantity.value, selectedOptions.value);
        if (result?.success) {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message: result.message || 'Product added to cart!', type: 'success' }
            }));
        } else {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message: result?.message || 'Failed to add to cart', type: 'error' }
            }));
        }
    } catch (e) {
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: 'Failed to add to cart', type: 'error' }
        }));
    } finally {
        adding.value = false;
    }
};

// Quick View for related products
const quickViewSlug = ref<string | null>(null);

// Breadcrumbs
const breadcrumbs = computed(() => {
    const items = [{ label: 'Shop', url: '/products' }];
    if (props.product.category) items.push({ label: props.product.category.name, url: `/category/${props.product.category.slug}` });
    items.push({ label: props.product.name });
    return items;
});

// Star rendering
const renderStars = (rating: number) => {
    return Array.from({ length: 5 }, (_, i) => i < Math.round(rating) ? 'full' : 'empty');
};
</script>

<template>
    <ThemeLayout>
        <Head :title="product.meta_title || product.name" />

        <Breadcrumb :items="breadcrumbs" />

        <!-- Product Detail -->
        <section class="py-8 lg:py-12">
            <div class="dmart-container">
                <div class="lg:grid lg:grid-cols-2 gap-10">
                    <!-- Image Gallery -->
                    <div class="space-y-4">
                        <div class="relative bg-gray-50 rounded-2xl overflow-hidden aspect-square flex items-center justify-center group">
                            <img
                                :src="allImages[activeImage]?.url"
                                :alt="allImages[activeImage]?.alt || product.name"
                                class="max-h-full max-w-full object-contain p-8 transition-transform duration-300"
                                :class="{ 'scale-150 cursor-zoom-out': zoomActive, 'cursor-zoom-in': !zoomActive }"
                                @click="zoomActive = !zoomActive"
                            />
                            <!-- Sale Badge -->
                            <span v-if="discount > 0" class="absolute top-4 left-4 dmart-badge dmart-badge-sale">
                                -{{ discount }}%
                            </span>
                            <!-- Nav Arrows -->
                            <button
                                v-if="allImages.length > 1"
                                @click="activeImage = (activeImage - 1 + allImages.length) % allImages.length"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 shadow flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </button>
                            <button
                                v-if="allImages.length > 1"
                                @click="activeImage = (activeImage + 1) % allImages.length"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 shadow flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                <ChevronRight class="w-5 h-5" />
                            </button>
                        </div>
                        <!-- Thumbnails -->
                        <div v-if="allImages.length > 1" class="flex gap-3 overflow-x-auto pb-2">
                            <button
                                v-for="(img, i) in allImages"
                                :key="img.id"
                                @click="activeImage = i"
                                class="flex-shrink-0 w-20 h-20 rounded-xl overflow-hidden border-2 transition-colors"
                                :class="i === activeImage ? 'border-theme-1' : 'border-transparent'"
                            >
                                <img :src="img.url" :alt="img.alt" class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="mt-6 lg:mt-0 space-y-5">
                        <!-- Brand -->
                        <Link v-if="product.brand" :href="`/products?brand=${product.brand.slug}`" class="text-sm font-semibold uppercase tracking-wider text-theme-1">
                            {{ product.brand.name }}
                        </Link>

                        <h1 class="text-2xl lg:text-3xl font-extrabold text-title font-title">
                            {{ product.name }}
                        </h1>

                        <!-- Rating -->
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-0.5">
                                <Star
                                    v-for="(star, i) in renderStars(product.rating)"
                                    :key="i"
                                    class="w-4 h-4"
                                    :class="star === 'full' ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'"
                                />
                            </div>
                            <span class="text-sm text-gray-500">({{ product.reviews_count }} reviews)</span>
                            <span class="text-sm" :class="product.in_stock ? 'text-green-600' : 'text-red-500'">
                                <Check v-if="product.in_stock" class="w-4 h-4 inline" /> {{ product.in_stock ? 'In Stock' : 'Out of Stock' }}
                            </span>
                        </div>

                        <!-- Price -->
                        <div class="flex items-baseline gap-3">
                            <span class="text-3xl font-extrabold text-theme-1 font-title">
                                {{ formatPrice(effectivePrice) }}
                            </span>
                            <span v-if="product.special_price" class="text-lg line-through text-gray-400">
                                {{ formatPrice(product.price) }}
                            </span>
                            <span v-if="discount > 0" class="dmart-badge dmart-badge-sale text-xs">
                                Save {{ discount }}%
                            </span>
                        </div>

                        <!-- Short Description -->
                        <p v-if="product.short_description" class="text-gray-600 leading-relaxed">
                            {{ product.short_description }}
                        </p>

                        <!-- Configurable Options -->
                        <div v-if="configurableAttributes?.length" class="space-y-4 pt-2">
                            <div v-for="attr in configurableAttributes" :key="attr.id">
                                <label class="block text-sm font-semibold mb-2 text-title">{{ attr.label }}</label>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="opt in attr.options"
                                        :key="opt.id"
                                        @click="selectedOptions[attr.code] = opt.id"
                                        class="px-4 py-2 rounded-lg border text-sm font-medium transition-all"
                                        :class="selectedOptions[attr.code] === opt.id
                                            ? 'border-theme-1 bg-theme-1 text-white'
                                            : 'border-gray-200 hover:border-theme-1'"
                                        :style="opt.swatch_value ? { backgroundColor: opt.swatch_value, width: '36px', height: '36px', padding: 0, borderRadius: '50%' } : {}"
                                    >
                                        <span v-if="!opt.swatch_value">{{ opt.label }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity & Add to Cart -->
                        <div class="flex items-center gap-4 pt-2">
                            <div class="dmart-qty-selector">
                                <button @click="decrementQty" :disabled="quantity <= 1"><Minus class="w-4 h-4" /></button>
                                <input type="number" v-model.number="quantity" min="1" :max="product.stock_quantity || 99" class="w-12 text-center border-0 focus:ring-0" />
                                <button @click="incrementQty"><Plus class="w-4 h-4" /></button>
                            </div>
                            <button
                                @click="handleAddToCart"
                                :disabled="!product.in_stock || adding"
                                class="dmart-btn dmart-btn-primary flex-1 py-3.5 text-base"
                                :class="{ 'opacity-50 cursor-not-allowed': !product.in_stock }"
                            >
                                <ShoppingCart class="w-5 h-5" />
                                {{ adding ? 'Adding...' : product.in_stock ? 'Add to Cart' : 'Out of Stock' }}
                            </button>
                        </div>

                        <!-- Wishlist & Share -->
                        <div class="flex items-center gap-4 pt-1">
                            <button
                                @click="toggleWishlist(product.id)"
                                class="flex items-center gap-2 text-sm font-medium transition-colors"
                                :class="isInWishlist(product.id) ? 'text-red-500' : 'text-gray-500 hover:text-red-500'"
                            >
                                <Heart class="w-5 h-5" :class="{ 'fill-current': isInWishlist(product.id) }" />
                                {{ isInWishlist(product.id) ? 'In Wishlist' : 'Add to Wishlist' }}
                            </button>
                        </div>

                        <!-- Info Badges -->
                        <div class="grid grid-cols-3 gap-3 pt-4 border-t">
                            <div class="text-center p-3 rounded-xl bg-gray-50">
                                <Truck class="w-5 h-5 mx-auto mb-1 text-theme-1" />
                                <div class="text-xs font-semibold">Free Shipping</div>
                            </div>
                            <div class="text-center p-3 rounded-xl bg-gray-50">
                                <RotateCcw class="w-5 h-5 mx-auto mb-1 text-theme-1" />
                                <div class="text-xs font-semibold">Easy Returns</div>
                            </div>
                            <div class="text-center p-3 rounded-xl bg-gray-50">
                                <Shield class="w-5 h-5 mx-auto mb-1 text-theme-1" />
                                <div class="text-xs font-semibold">Secure Pay</div>
                            </div>
                        </div>

                        <!-- SKU / Category -->
                        <div class="text-sm text-gray-500 space-y-1 pt-2">
                            <div>SKU: <span class="text-gray-700">{{ product.sku }}</span></div>
                            <div v-if="product.category">
                                Category: <Link :href="`/category/${product.category.slug}`" class="hover:text-theme-1">{{ product.category.name }}</Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Section -->
                <div class="mt-14">
                    <div class="flex gap-0 border-b overflow-x-auto">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="activeDetailTab = tab.key"
                            class="px-6 py-3 text-sm font-semibold whitespace-nowrap transition-colors border-b-2 font-title"
                            :class="activeDetailTab === tab.key
                                ? 'border-theme-1 text-theme-1'
                                : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >
                            {{ tab.label }}
                            <span v-if="tab.key === 'reviews'" class="ml-1">({{ product.reviews_count }})</span>
                        </button>
                    </div>

                    <div class="py-8">
                        <!-- Description -->
                        <div v-if="activeDetailTab === 'description'" class="prose max-w-none text-gray-600" v-html="product.description || 'No description available.'" />

                        <!-- Specifications -->
                        <div v-if="activeDetailTab === 'specifications'">
                            <table v-if="product.specifications?.length" class="w-full">
                                <tbody>
                                    <tr v-for="(spec, i) in product.specifications" :key="i" :class="i % 2 === 0 ? 'bg-gray-50' : ''">
                                        <td class="px-4 py-3 font-semibold text-sm w-1/3 text-title">{{ spec.label }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ spec.value }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p v-else class="text-gray-500">No specifications available.</p>
                        </div>

                        <!-- Reviews -->
                        <div v-if="activeDetailTab === 'reviews'">
                            <div v-if="product.reviews?.length" class="space-y-6">
                                <div v-for="review in product.reviews" :key="review.id" class="border-b pb-6">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white bg-theme-1">
                                            {{ review.author.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-sm">{{ review.author }}</div>
                                            <div class="flex items-center gap-1">
                                                <Star v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'" />
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-400 ml-auto">{{ review.created_at }}</span>
                                    </div>
                                    <h4 v-if="review.title" class="font-semibold text-sm mb-1">{{ review.title }}</h4>
                                    <p class="text-sm text-gray-600">{{ review.comment }}</p>
                                </div>
                            </div>
                            <p v-else class="text-gray-500">No reviews yet. Be the first to review this product!</p>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <section v-if="relatedProducts?.length" class="mt-14">
                    <h2 class="dmart-section-title mb-8">Related Products</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
                        <ProductCard
                            v-for="rp in relatedProducts.slice(0, 5)"
                            :key="rp.id"
                            :product="rp"
                            @quick-view="quickViewSlug = $event"
                        />
                    </div>
                </section>
            </div>
        </section>

        <QuickViewModal :slug="quickViewSlug" @close="quickViewSlug = null" />
    </ThemeLayout>
</template>
