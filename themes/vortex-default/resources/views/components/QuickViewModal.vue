<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="close"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>

                <!-- Modal -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden"
                        @click.stop
                    >
                        <!-- Close Button -->
                        <button
                            @click="close"
                            class="absolute top-4 right-4 z-10 p-2 rounded-full bg-white/80 hover:bg-white shadow-lg transition-colors cursor-pointer"
                        >
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <!-- Loading State -->
                        <div v-if="loading" class="p-12 flex items-center justify-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-600 border-t-transparent"></div>
                        </div>

                        <!-- Product Content -->
                        <div v-else-if="product" class="overflow-y-auto max-h-[90vh]">
                            <div class="grid md:grid-cols-2 gap-8 p-8">
                                <!-- Left: Image -->
                                <div class="space-y-4">
                                    <!-- Main Image with Zoom -->
                                    <div
                                        class="relative aspect-square bg-gray-100 rounded-xl overflow-hidden cursor-zoom-in"
                                        @mouseenter="showZoom = true"
                                        @mouseleave="showZoom = false"
                                        @mousemove="handleMouseMove"
                                    >
                                        <img
                                            v-if="currentImage"
                                            ref="mainImageRef"
                                            :src="currentImage"
                                            :alt="product.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                            </svg>
                                        </div>

                                        <!-- Zoom Overlay -->
                                        <div
                                            v-if="showZoom && currentImage"
                                            class="absolute inset-0 pointer-events-none"
                                            :style="{
                                                backgroundImage: `url(${currentImage})`,
                                                backgroundPosition: `${zoomPosition.x}% ${zoomPosition.y}%`,
                                                backgroundSize: '200%',
                                                backgroundRepeat: 'no-repeat'
                                            }"
                                        ></div>
                                    </div>

                                    <!-- Thumbnails -->
                                    <div v-if="allImages.length > 1" class="flex gap-2 overflow-x-auto pb-2">
                                        <button
                                            v-for="(img, index) in allImages"
                                            :key="index"
                                            class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 hover:border-indigo-600 transition-colors cursor-pointer"
                                            :class="selectedImageIndex === index ? 'border-indigo-600' : 'border-gray-200'"
                                            @click="selectedImageIndex = index"
                                        >
                                            <img :src="img" :alt="product.name" class="w-full h-full object-cover" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Right: Details -->
                                <div class="space-y-6">
                                    <!-- Brand -->
                                    <div v-if="product.brand" class="text-sm text-indigo-600 font-medium">
                                        {{ product.brand.name }}
                                    </div>

                                    <!-- Title -->
                                    <h2 class="text-3xl font-bold text-gray-900">
                                        {{ product.name }}
                                    </h2>

                                    <!-- Rating & Reviews -->
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center">
                                            <span class="text-yellow-400 text-lg">
                                                {{ '★'.repeat(Math.floor(product.rating)) }}{{ '☆'.repeat(5 - Math.floor(product.rating)) }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-600">
                                            ({{ product.reviews_count }} reviews)
                                        </span>
                                    </div>

                                    <!-- Price -->
                                    <div class="flex items-baseline gap-3">
                                        <span class="text-4xl font-bold text-gray-900">
                                            ${{ product.special_price || product.price }}
                                        </span>
                                        <span v-if="product.special_price" class="text-xl text-gray-400 line-through">
                                            ${{ product.price }}
                                        </span>
                                        <span v-if="product.special_price" class="px-3 py-1 bg-red-100 text-red-700 text-sm font-semibold rounded-full">
                                            Save ${{ (product.price - product.special_price).toFixed(2) }}
                                        </span>
                                    </div>

                                    <!-- Stock Status -->
                                    <div>
                                        <span
                                            v-if="product.in_stock"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-lg font-medium"
                                        >
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            In Stock
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-lg font-medium"
                                        >
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                            Out of Stock
                                        </span>
                                    </div>

                                    <!-- Short Description -->
                                    <div v-if="product.short_description" class="text-gray-600 leading-relaxed">
                                        {{ product.short_description }}
                                    </div>

                                    <!-- Configurable Attributes -->
                                    <div v-if="configurableAttributes.length > 0" class="space-y-4 py-4 border-t border-gray-200">
                                        <div v-for="attribute in configurableAttributes" :key="attribute.id" class="space-y-2">
                                            <label class="block text-sm font-semibold text-gray-700">
                                                {{ attribute.name }} <span class="text-red-500">*</span>
                                            </label>

                                            <!-- Color Swatches (for color attributes) -->
                                            <div v-if="attribute.name.toLowerCase() === 'color' && attribute.options.some(opt => opt.color_code)" class="flex gap-2 flex-wrap">
                                                <button
                                                    v-for="option in attribute.options"
                                                    :key="option.id"
                                                    @click="selectedAttributes[attribute.id] = option.id; attributeError = null"
                                                    class="w-10 h-10 rounded-full border-2 transition-all hover:scale-110"
                                                    :class="{
                                                        'ring-2 ring-indigo-600 ring-offset-2': selectedAttributes[attribute.id] === option.id,
                                                        'border-gray-300': selectedAttributes[attribute.id] !== option.id
                                                    }"
                                                    :style="{ backgroundColor: option.color_code || '#cccccc' }"
                                                    :title="option.value"
                                                ></button>
                                            </div>

                                            <!-- Buttons (for size and other attributes) -->
                                            <div v-else class="flex gap-2 flex-wrap">
                                                <button
                                                    v-for="option in attribute.options"
                                                    :key="option.id"
                                                    @click="selectedAttributes[attribute.id] = option.id; attributeError = null"
                                                    class="px-4 py-2 border-2 rounded-lg font-medium transition-all hover:border-indigo-600"
                                                    :class="{
                                                        'border-indigo-600 bg-indigo-50 text-indigo-700': selectedAttributes[attribute.id] === option.id,
                                                        'border-gray-300 text-gray-700 hover:bg-gray-50': selectedAttributes[attribute.id] !== option.id
                                                    }"
                                                >
                                                    {{ option.value }}
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Attribute Error Message -->
                                        <div v-if="attributeError" class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded-lg">
                                            {{ attributeError }}
                                        </div>
                                    </div>

                                    <!-- Quantity & Add to Cart -->
                                    <div class="flex items-center gap-4 pt-4">
                                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                            <button
                                                @click="quantity > 1 && quantity--"
                                                class="px-4 py-3 hover:bg-gray-100 transition-colors cursor-pointer"
                                                :disabled="quantity <= 1"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            </button>
                                            <input
                                                v-model.number="quantity"
                                                type="number"
                                                min="1"
                                                class="w-16 text-center py-3 border-x border-gray-300 focus:outline-none"
                                            />
                                            <button
                                                @click="quantity++"
                                                class="px-4 py-3 hover:bg-gray-100 transition-colors cursor-pointer"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>

                                        <button
                                            @click="addToCart"
                                            :disabled="!canAddToCart || addingToCart"
                                            class="flex-1 px-8 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2 cursor-pointer"
                                        >
                                            <svg v-if="addingToCart" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span v-else>Add to Cart</span>
                                        </button>
                                    </div>

                                    <!-- View Full Details Link -->
                                    <div class="pt-4 border-t border-gray-200">
                                        <a
                                            :href="`/products/${product.slug}`"
                                            class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium cursor-pointer"
                                        >
                                            View Full Details
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                            </svg>
                                        </a>
                                    </div>

                                    <!-- Product Meta -->
                                    <div class="pt-4 border-t border-gray-200 space-y-2 text-sm text-gray-600">
                                        <div v-if="product.sku">
                                            <span class="font-medium">SKU:</span> {{ product.sku }}
                                        </div>
                                        <div v-if="product.categories && product.categories.length">
                                            <span class="font-medium">Categories:</span>
                                            <span v-for="(category, index) in product.categories" :key="category.id">
                                                {{ category.name }}<span v-if="index < product.categories.length - 1">, </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Success Toast -->
        <Transition name="slide-up">
            <div
                v-if="showSuccessToast"
                class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[60] px-6 py-3 bg-green-500 text-white rounded-lg text-sm font-medium shadow-lg flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Added to cart successfully!</span>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import { useCartStore } from '@/Stores/cartStore';

interface AttributeOption {
    id: number;
    value: string;
    color_code: string | null;
}

interface Attribute {
    id: number;
    name: string;
    type: string;
    options: AttributeOption[];
}

interface Product {
    id: number;
    name: string;
    slug: string;
    sku: string;
    price: number;
    special_price: number | null;
    short_description: string | null;
    image: string | null;
    rating: number;
    reviews_count: number;
    in_stock: boolean;
    has_configurable_attributes: boolean;
    brand?: {
        id: number;
        name: string;
    };
    categories?: Array<{
        id: number;
        name: string;
    }>;
    images?: Array<{
        url: string;
    }>;
}

interface Props {
    isOpen: boolean;
    productSlug: string | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
}>();

const cartStore = useCartStore();

const loading = ref(false);
const product = ref<Product | null>(null);
const configurableAttributes = ref<Attribute[]>([]);
const selectedAttributes = ref<Record<number, number>>({});
const quantity = ref(1);
const selectedImageIndex = ref(0);
const addingToCart = ref(false);
const showZoom = ref(false);
const zoomPosition = ref({ x: 50, y: 50 });
const mainImageRef = ref<HTMLImageElement | null>(null);
const attributeError = ref<string | null>(null);

// Computed properties for images
const allImages = computed(() => {
    if (!product.value) return [];
    
    const images: string[] = [];
    
    // Add main image first
    if (product.value.image) {
        images.push(product.value.image);
    }
    
    // Add additional images
    if (product.value.images && product.value.images.length > 0) {
        product.value.images.forEach(img => {
            if (img.url && !images.includes(img.url)) {
                images.push(img.url);
            }
        });
    }
    
    return images;
});

const currentImage = computed(() => {
    return allImages.value[selectedImageIndex.value] || null;
});

// Check if all required attributes are selected
const canAddToCart = computed(() => {
    if (!product.value || !product.value.in_stock) return false;
    if (!product.value.has_configurable_attributes) return true;
    
    // All attributes must be selected
    return configurableAttributes.value.every(attr => 
        selectedAttributes.value[attr.id] !== undefined
    );
});

// Watch for modal open and fetch product data
watch(() => props.isOpen, async (isOpen) => {
    if (isOpen && props.productSlug) {
        loading.value = true;
        product.value = null;
        configurableAttributes.value = [];
        selectedAttributes.value = {};
        quantity.value = 1;
        selectedImageIndex.value = 0;
        showZoom.value = false;
        attributeError.value = null;

        try {
            const response = await axios.get(`/products/${props.productSlug}/quick-view`);
            product.value = response.data.product;
            configurableAttributes.value = response.data.configurableAttributes || [];
        } catch (error) {
            console.error('Failed to load product:', error);
            close();
        } finally {
            loading.value = false;
        }
    }
});

const handleMouseMove = (e: MouseEvent) => {
    if (!mainImageRef.value) return;
    
    const rect = mainImageRef.value.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    
    zoomPosition.value = {
        x: Math.max(0, Math.min(100, x)),
        y: Math.max(0, Math.min(100, y))
    };
};

const close = () => {
    emit('close');
};

const showSuccessToast = ref(false);

const addToCart = async () => {
    if (!product.value || addingToCart.value) return;

    // Check if all attributes are selected for configurable products
    if (product.value.has_configurable_attributes && !canAddToCart.value) {
        attributeError.value = 'Please select all required options';
        return;
    }

    addingToCart.value = true;
    attributeError.value = null;
    
    try {
        const result = await cartStore.addItem(
            product.value.id, 
            quantity.value,
            product.value.has_configurable_attributes ? selectedAttributes.value : undefined
        );
        
        if (result.success) {
            // Show success toast
            showSuccessToast.value = true;
            setTimeout(() => {
                showSuccessToast.value = false;
                close();
            }, 1500);
        } else {
            attributeError.value = result.message || 'Failed to add to cart. Please try again.';
        }
    } catch (error) {
        console.error('Failed to add to cart:', error);
        attributeError.value = 'Failed to add to cart. Please try again.';
    } finally {
        addingToCart.value = false;
    }
};

// Close on escape key
const handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.isOpen) {
        close();
    }
};

// Add/remove escape listener
watch(() => props.isOpen, (isOpen) => {
    if (isOpen) {
        document.addEventListener('keydown', handleEscape);
        document.body.style.overflow = 'hidden';
    } else {
        document.removeEventListener('keydown', handleEscape);
        document.body.style.overflow = '';
    }
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.95);
    opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.3s ease;
}

.slide-up-enter-from {
    opacity: 0;
    transform: translateY(20px) translateX(-50%);
}

.slide-up-leave-to {
    opacity: 0;
    transform: translateY(-20px) translateX(-50%);
}
</style>
