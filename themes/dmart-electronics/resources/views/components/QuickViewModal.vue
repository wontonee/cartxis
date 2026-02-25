<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { X, Minus, Plus, Star, Heart, ShoppingCart } from 'lucide-vue-next';
import { useCart } from '@/composables/useCart';
import { useCurrency } from '@/composables/useCurrency';
import { useWishlist } from '@/composables/useWishlist';
import axios from 'axios';

interface Props {
    slug: string | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const { addToCart, loading: cartLoading } = useCart();
const { formatPrice } = useCurrency();
const { toggleWishlist, isInWishlist } = useWishlist();

const product = ref<any>(null);
const isLoading = ref(false);
const quantity = ref(1);
const selectedImage = ref(0);
const addedToCart = ref(false);

watch(() => props.slug, async (newSlug) => {
    if (!newSlug) { product.value = null; return; }
    isLoading.value = true;
    quantity.value = 1;
    addedToCart.value = false;
    try {
        const { data } = await axios.get(`/products/${newSlug}/quick-view`);
        product.value = data;
        selectedImage.value = 0;
    } catch { product.value = null; }
    isLoading.value = false;
}, { immediate: true });

const handleAddToCart = async () => {
    if (!product.value) return;
    await addToCart(product.value.id, quantity.value);
    addedToCart.value = true;
    setTimeout(() => { addedToCart.value = false; }, 2000);
};
</script>

<template>
    <Transition name="fade">
        <div v-if="slug" class="fixed inset-0 bg-black/60 z-[80] flex items-center justify-center p-4" @click.self="$emit('close')">
            <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-auto shadow-2xl">
                <!-- Loading -->
                <div v-if="isLoading" class="flex items-center justify-center py-24">
                    <div class="w-10 h-10 border-4 border-gray-200 border-t-theme-1 rounded-full animate-spin"></div>
                </div>

                <!-- Content -->
                <div v-else-if="product" class="grid md:grid-cols-2 gap-0">
                    <!-- Images -->
                    <div class="bg-gray-50 p-8 relative">
                        <button @click="$emit('close')" class="absolute top-4 right-4 p-2 rounded-full bg-white shadow z-10 md:hidden"><X class="w-4 h-4" /></button>
                        <img :src="product.images?.[selectedImage]?.url || product.image" :alt="product.name" class="w-full aspect-square object-contain" />
                        <div v-if="product.images?.length > 1" class="flex gap-2 mt-4 justify-center">
                            <button
                                v-for="(img, i) in product.images.slice(0, 4)"
                                :key="i"
                                @click="selectedImage = i"
                                class="w-14 h-14 rounded-lg border-2 p-1 overflow-hidden transition-colors"
                                :class="selectedImage === i ? 'border-theme-1' : 'border-gray-200'"
                            >
                                <img :src="img.url" class="w-full h-full object-contain" />
                            </button>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-6 md:p-8 relative">
                        <button @click="$emit('close')" class="absolute top-4 right-4 p-2 rounded-full hover:bg-gray-100 hidden md:block"><X class="w-4 h-4" /></button>
                        <h2 class="text-xl font-bold pr-10 text-title font-title">{{ product.name }}</h2>

                        <div class="flex items-center gap-2 mt-2" v-if="product.rating > 0">
                            <div class="dmart-stars">
                                <Star v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.round(product.rating) ? 'star-filled' : 'star-empty'" :fill="i <= Math.round(product.rating) ? 'currentColor' : 'none'" />
                            </div>
                            <span class="text-sm text-gray-500">({{ product.reviews_count }} reviews)</span>
                        </div>

                        <div class="flex items-center gap-3 mt-4">
                            <span class="text-2xl font-bold text-theme-1">
                                {{ formatPrice(product.special_price ?? product.price) }}
                            </span>
                            <span v-if="product.special_price" class="text-lg text-gray-400 line-through">
                                {{ formatPrice(product.price) }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-600 mt-4 line-clamp-3">{{ product.short_description || product.description }}</p>

                        <div class="flex items-center gap-4 mt-6">
                            <div class="dmart-qty-selector">
                                <button @click="quantity = Math.max(1, quantity - 1)"><Minus class="w-3.5 h-3.5" /></button>
                                <input v-model.number="quantity" type="number" min="1" />
                                <button @click="quantity++"><Plus class="w-3.5 h-3.5" /></button>
                            </div>
                            <button
                                @click="handleAddToCart"
                                class="dmart-btn dmart-btn-primary flex-1"
                                :disabled="cartLoading || !product.in_stock"
                            >
                                <ShoppingCart class="w-4 h-4" />
                                {{ addedToCart ? 'Added!' : product.in_stock ? 'Add to Cart' : 'Out of Stock' }}
                            </button>
                            <button
                                @click="toggleWishlist(product.id)"
                                class="p-3 rounded-lg border-2 transition-colors"
                                :class="isInWishlist(product.id) ? 'border-theme-1 text-theme-1' : 'border-gray-200 text-gray-600'"
                            >
                                <Heart class="w-5 h-5" :fill="isInWishlist(product.id) ? 'currentColor' : 'none'" />
                            </button>
                        </div>

                        <Link :href="`/product/${product.slug}`" class="block mt-4 text-sm font-medium text-center hover:text-theme-1 transition-colors underline">
                            View Full Details →
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
