<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useCartStore } from '@/Stores/cartStore';
import ThemeLayout from '@/../../themes/vortex-default/resources/views/layouts/ThemeLayout.vue';
import ProductCard from '@/../../themes/vortex-default/resources/views/components/ProductCard.vue';
import { useCurrency } from '@/composables/useCurrency';

const { formatPrice } = useCurrency();

interface Brand {
    id: number;
    name: string;
    slug: string;
}

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface ProductImage {
    id: number;
    url: string;
    path: string;
}

interface AttributeOption {
    id: number;
    attribute_id: number;
    label: string;
    value: string;
    swatch_value?: string;
    sort_order: number;
}

interface ConfigurableAttribute {
    id: number;
    name: string;
    code: string;
    type: string;
    is_required: boolean;
    options: AttributeOption[];
}

interface Product {
    id: number;
    name: string;
    slug: string;
    sku: string;
    type: string;
    description: string;
    short_description: string;
    price: number;
    discount_price: number | null;
    quantity: number;
    stock_status: string;
    status: string;
    image: string;
    rating: number;
    reviews_count: number;
    in_stock: boolean;
    brand: Brand;
    categories: Category[];
    images: ProductImage[];
}

const props = defineProps<{
    product: Product;
    relatedProducts: Product[];
    configurableAttributes: ConfigurableAttribute[];
}>();

const cartStore = useCartStore();

// Image gallery state
const selectedImage = ref(0);
const isZoomed = ref(false);
const zoomPosition = ref({ x: 50, y: 50 });
const mainImageRef = ref<HTMLElement | null>(null);

// Add to cart state
const quantity = ref(1);
const isAddingToCart = ref(false);

// Selected attribute values (for configurable products)
const selectedAttributes = ref<Record<string, string>>({});

// All images (main + gallery)
const allImages = computed(() => {
    const images: string[] = [];
    if (props.product.image) {
        images.push(props.product.image);
    }
    if (props.product.images && props.product.images.length > 0) {
        props.product.images.forEach((img) => {
            if (img.url && !images.includes(img.url)) {
                images.push(img.url);
            }
        });
    }
    return images;
});

const currentImage = computed(() => allImages.value[selectedImage.value] || props.product.image || null);
const hasImage = computed(() => currentImage.value !== null);

// Discount calculation
const hasDiscount = computed(() => props.product.discount_price && props.product.discount_price < props.product.price);
const discountPercentage = computed(() => {
    if (!hasDiscount.value || !props.product.discount_price) return 0;
    return Math.round(((props.product.price - props.product.discount_price) / props.product.price) * 100);
});

const displayPrice = computed(() => {
    const price = hasDiscount.value ? props.product.discount_price : props.product.price;
    return typeof price === 'string' ? parseFloat(price) : price;
});

// Quantity controls
const incrementQuantity = () => {
    if (quantity.value < props.product.quantity) {
        quantity.value++;
    }
};

const decrementQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

// Add to cart
const addToCart = async () => {
    // Validate required attributes are selected
    if (props.configurableAttributes && props.configurableAttributes.length > 0) {
        const missingAttributes = props.configurableAttributes
            .filter(attr => attr.is_required && !selectedAttributes.value[attr.code])
            .map(attr => attr.name);

        if (missingAttributes.length > 0) {
            alert(`Please select: ${missingAttributes.join(', ')}`);
            return;
        }
    }

    isAddingToCart.value = true;
    try {
        await cartStore.addToCart(props.product.id, quantity.value, selectedAttributes.value);
        quantity.value = 1;
    } finally {
        isAddingToCart.value = false;
    }
};

// Image zoom handlers
const handleMouseMove = (e: MouseEvent) => {
    if (!mainImageRef.value) return;
    const rect = mainImageRef.value.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    zoomPosition.value = {
        x: Math.max(0, Math.min(100, x)),
        y: Math.max(0, Math.min(100, y)),
    };
};
</script>

<template>
    <Head :title="product.name" />

    <ThemeLayout>
        <!-- Breadcrumb -->
        <div class="bg-gray-50 border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <nav class="flex items-center space-x-2 text-sm text-gray-500">
                    <Link href="/" class="hover:text-gray-700">Home</Link>
                    <span>/</span>
                    <Link href="/products" class="hover:text-gray-700">Products</Link>
                    <span>/</span>
                    <span
                        v-if="product.categories && product.categories.length > 0"
                        class="text-gray-700"
                    >
                        {{ product.categories[0].name }}
                    </span>
                </nav>
            </div>
        </div>

        <!-- Product Details -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Image Gallery -->
                <div class="space-y-4">
                    <!-- Main Image -->
                    <div class="relative bg-white rounded-lg border overflow-hidden aspect-square">
                        <div
                            v-if="hasImage"
                            ref="mainImageRef"
                            class="relative w-full h-full cursor-zoom-in"
                            @mouseenter="isZoomed = true"
                            @mouseleave="isZoomed = false"
                            @mousemove="handleMouseMove"
                        >
                            <img
                                :src="currentImage"
                                :alt="product.name"
                                class="w-full h-full object-contain"
                            />
                            <!-- Zoom Overlay -->
                            <div
                                v-if="isZoomed"
                                class="absolute inset-0 bg-white pointer-events-none overflow-hidden"
                            >
                                <div
                                    class="w-full h-full"
                                    :style="{
                                        backgroundImage: `url('${currentImage}')`,
                                        backgroundPosition: `${zoomPosition.x}% ${zoomPosition.y}%`,
                                        backgroundSize: '200%',
                                        backgroundRepeat: 'no-repeat',
                                    }"
                                ></div>
                            </div>
                        </div>
                        
                        <!-- Default Image Placeholder -->
                        <div v-else class="w-full h-full flex items-center justify-center bg-gray-100 text-9xl">
                            📦
                        </div>
                    </div>

                    <!-- Thumbnails -->
                    <div v-if="allImages.length > 1" class="flex gap-2 overflow-x-auto">
                        <button
                            v-for="(image, index) in allImages"
                            :key="index"
                            @click="selectedImage = index"
                            class="flex-shrink-0 w-20 h-20 rounded-lg border-2 overflow-hidden transition-all cursor-pointer"
                            :class="selectedImage === index ? 'border-indigo-600' : 'border-gray-200 hover:border-gray-300'"
                        >
                            <img
                                :src="image"
                                :alt="`${product.name} - Image ${index + 1}`"
                                class="w-full h-full object-contain"
                            />
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="space-y-6">
                    <!-- Brand -->
                    <div v-if="product.brand">
                        <Link
                            :href="`/products?brand=${product.brand.slug}`"
                            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                        >
                            {{ product.brand.name }}
                        </Link>
                    </div>

                    <!-- Title -->
                    <h1 class="text-3xl font-bold text-gray-900">{{ product.name }}</h1>

                    <!-- Rating & Reviews -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center">
                            <div class="flex items-center">
                                <svg
                                    v-for="i in 5"
                                    :key="i"
                                    class="w-5 h-5"
                                    :class="i <= Math.round(product.rating) ? 'text-yellow-400' : 'text-gray-300'"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <span class="ml-2 text-sm text-gray-600">{{ product.rating.toFixed(1) }}</span>
                        </div>
                        <span class="text-sm text-gray-500">({{ product.reviews_count }} reviews)</span>
                    </div>

                    <!-- Price -->
                    <div class="flex items-baseline gap-3">
                        <span class="text-3xl font-bold text-gray-900">{{ formatPrice(displayPrice) }}</span>
                        <span v-if="hasDiscount" class="text-xl text-gray-500 line-through">{{ formatPrice(product.price) }}</span>
                        <span v-if="hasDiscount" class="text-sm font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">
                            Save {{ discountPercentage }}%
                        </span>
                    </div>

                    <!-- Stock Status -->
                    <div class="flex items-center gap-2">
                        <span
                            v-if="product.in_stock"
                            class="inline-flex items-center text-sm font-medium text-green-600"
                        >
                            <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            In Stock ({{ product.quantity }} available)
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center text-sm font-medium text-red-600"
                        >
                            <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            Out of Stock
                        </span>
                    </div>

                    <!-- SKU & Product Type -->
                    <div class="flex items-center gap-3">
                        <div class="text-sm text-gray-500">
                            SKU: <span class="font-medium text-gray-700">{{ product.sku }}</span>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                            'bg-gray-100 text-gray-800': product.type === 'simple',
                            'bg-purple-100 text-purple-800': product.type === 'configurable',
                            'bg-blue-100 text-blue-800': product.type === 'virtual',
                            'bg-cyan-100 text-cyan-800': product.type === 'downloadable',
                        }">
                            <svg v-if="product.type === 'virtual' || product.type === 'downloadable'" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z" />
                                <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z" />
                                <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z" />
                            </svg>
                            {{ product.type.charAt(0).toUpperCase() + product.type.slice(1) }}
                        </span>
                    </div>

                    <!-- Digital Product Info -->
                    <div v-if="product.type === 'virtual' || product.type === 'downloadable'" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-900">
                                    {{ product.type === 'downloadable' ? 'Digital Download' : 'Virtual Product' }}
                                </p>
                                <p class="text-sm text-blue-700 mt-1">
                                    {{ product.type === 'downloadable' 
                                        ? 'This is a digital product. Download link will be available after purchase.' 
                                        : 'This is a virtual product. No physical shipping required.' 
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Short Description -->
                    <div v-if="product.short_description" class="text-gray-600 leading-relaxed">
                        {{ product.short_description }}
                    </div>

                    <!-- Product Attributes (Color, Size, etc.) -->
                    <div v-if="configurableAttributes && configurableAttributes.length > 0" class="space-y-4 border-t pt-6">
                        <div v-for="attribute in configurableAttributes" :key="attribute.id" class="space-y-2">
                            <label class="block text-sm font-medium text-gray-900">
                                {{ attribute.name }}
                                <span v-if="attribute.is_required" class="text-red-500">*</span>
                            </label>

                            <!-- Color Swatches -->
                            <div v-if="attribute.type === 'color'" class="space-y-3">
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        v-for="option in attribute.options"
                                        :key="option.id"
                                        @click="selectedAttributes[attribute.code] = option.value"
                                        class="group relative cursor-pointer"
                                        :title="option.value"
                                    >
                                        <!-- Color Swatch Circle -->
                                        <div
                                            class="w-12 h-12 rounded-full border-2 transition-all flex items-center justify-center overflow-hidden cursor-pointer"
                                            :class="selectedAttributes[attribute.code] === option.value 
                                                ? 'border-indigo-600 ring-2 ring-indigo-200 ring-offset-2' 
                                                : 'border-gray-300 hover:border-gray-400'"
                                            :style="option.color_code ? { backgroundColor: option.color_code } : { backgroundColor: '#f3f4f6' }"
                                        >
                                            <!-- Show text if no color code -->
                                            <span v-if="!option.color_code" class="text-xs font-semibold text-gray-700 uppercase">
                                                {{ option.value.substring(0, 3) }}
                                            </span>
                                        </div>
                                        
                                        <!-- Selected Checkmark -->
                                        <svg
                                            v-if="selectedAttributes[attribute.code] === option.value"
                                            class="absolute -top-1 -right-1 w-5 h-5 text-white bg-indigo-600 rounded-full p-0.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                        
                                        <!-- Color Name Label on Hover -->
                                        <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-xs font-medium text-gray-700 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                            {{ option.value }}
                                        </span>
                                    </button>
                                </div>
                                
                                <!-- Selected Color Display -->
                                <div v-if="selectedAttributes[attribute.code]" class="text-sm text-gray-600">
                                    Selected: <span class="font-semibold text-gray-900">{{ selectedAttributes[attribute.code] }}</span>
                                </div>
                            </div>

                            <!-- Size/Other Options -->
                            <div v-else class="flex flex-wrap gap-2">
                                <button
                                    v-for="option in attribute.options"
                                    :key="option.id"
                                    @click="selectedAttributes[attribute.code] = option.value"
                                    class="px-4 py-2 border rounded-lg text-sm font-medium transition-all cursor-pointer"
                                    :class="selectedAttributes[attribute.code] === option.value 
                                        ? 'border-indigo-600 bg-indigo-50 text-indigo-700' 
                                        : 'border-gray-300 text-gray-700 hover:border-gray-400'"
                                >
                                    {{ option.value }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quantity & Add to Cart -->
                    <div v-if="product.in_stock" class="space-y-4">
                        <!-- Quantity Selector - Hidden for downloadable products -->
                        <div v-if="product.type !== 'downloadable'" class="flex items-center gap-4">
                            <span class="text-sm font-medium text-gray-700">Quantity:</span>
                            <div class="flex items-center border rounded-lg">
                                <button
                                    @click="decrementQuantity"
                                    :disabled="quantity <= 1"
                                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                >
                                    -
                                </button>
                                <input
                                    v-model.number="quantity"
                                    type="number"
                                    min="1"
                                    :max="product.quantity"
                                    class="w-16 text-center border-x py-2 focus:outline-none"
                                />
                                <button
                                    @click="incrementQuantity"
                                    :disabled="quantity >= product.quantity"
                                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- Add to Cart / Buy Now Button -->
                        <button
                            @click="addToCart"
                            :disabled="isAddingToCart"
                            class="w-full bg-indigo-600 text-white px-8 py-3 rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium cursor-pointer flex items-center justify-center gap-2"
                        >
                            <svg v-if="product.type === 'downloadable' && !isAddingToCart" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span v-if="!isAddingToCart">
                                {{ product.type === 'downloadable' ? 'Buy Now & Download' : 'Add to Cart' }}
                            </span>
                            <span v-else>Adding...</span>
                        </button>
                    </div>

                    <!-- Categories -->
                    <div v-if="product.categories && product.categories.length > 0" class="border-t pt-6">
                        <span class="text-sm font-medium text-gray-700">Categories: </span>
                        <Link
                            v-for="(category, index) in product.categories"
                            :key="category.id"
                            :href="`/products?category=${category.slug}`"
                            class="text-sm text-indigo-600 hover:text-indigo-800"
                        >
                            {{ category.name }}<span v-if="index < product.categories.length - 1">, </span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Full Description -->
            <div v-if="product.description" class="mt-12 border-t pt-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Product Description</h2>
                <div class="prose max-w-none text-gray-600 leading-relaxed">
                    {{ product.description }}
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts.length > 0" class="mt-12 border-t pt-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <ProductCard
                        v-for="relatedProduct in relatedProducts"
                        :key="relatedProduct.id"
                        :product="relatedProduct"
                    />
                </div>
            </div>
        </div>
    </ThemeLayout>
</template>
