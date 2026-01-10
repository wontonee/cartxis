<script setup lang="ts">
import { ref, computed } from 'vue';
import { useCurrency } from '@/composables/useCurrency';

const { formatPrice } = useCurrency();

interface Product {
  id: number;
  name: string;
  slug: string;
  sku: string;
  description?: string | null;
  short_description?: string | null;
  price: number | string;
  special_price?: number | string | null;
  quantity: number;
  stock_status: string;
  main_image?: {
    id: number;
    path: string;
    thumbnail_path: string;
    url: string;
  };
  images?: Array<{
    id: number;
    path: string;
    thumbnail_path: string;
    alt_text: string | null;
    url: string;
  }>;
  categories: Array<{ id: number; name: string }>;
  main_image_id?: number | null;
  images_count?: number;
}

interface Props {
  product: Product | null;
  show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'edit', 'delete']);

// State
const currentImageIndex = ref(0);
const isZoomed = ref(false);

// Computed
const currentImage = computed(() => {
  // If product has an images array, use it
  if (props.product?.images && props.product.images.length > 0) {
    return props.product.images[currentImageIndex.value];
  }
  // Otherwise, use main_image
  if (props.product?.main_image) {
    return props.product.main_image;
  }
  return null;
});

const hasMultipleImages = computed(() => {
  return props.product?.images && props.product.images.length > 1;
});

const finalPrice = computed(() => {
  if (!props.product) return '0.00';
  const price = props.product.special_price || props.product.price;
  return typeof price === 'number' ? price.toFixed(2) : parseFloat(price.toString()).toFixed(2);
});

const hasDiscount = computed(() => {
  if (!props.product || !props.product.special_price) return false;
  const special = typeof props.product.special_price === 'number' 
    ? props.product.special_price 
    : parseFloat(props.product.special_price.toString());
  const regular = typeof props.product.price === 'number'
    ? props.product.price
    : parseFloat(props.product.price.toString());
  return special < regular;
});

const discountPercentage = computed(() => {
  if (!hasDiscount.value || !props.product) return 0;
  const original = typeof props.product.price === 'number'
    ? props.product.price
    : parseFloat(props.product.price.toString());
  const special = typeof props.product.special_price === 'number'
    ? props.product.special_price
    : parseFloat(props.product.special_price?.toString() || '0');
  return Math.round(((original - special) / original) * 100);
});

const stockStatusColor = computed(() => {
  if (!props.product) return 'gray';
  if (props.product.stock_status === 'in_stock' && props.product.quantity > 10) return 'green';
  if (props.product.stock_status === 'in_stock' && props.product.quantity > 0) return 'yellow';
  return 'red';
});

const stockStatusText = computed(() => {
  if (!props.product) return 'Unknown';
  if (props.product.stock_status === 'out_of_stock') return 'Out of Stock';
  if (props.product.quantity === 0) return 'Out of Stock';
  if (props.product.quantity <= 5) return 'Low Stock';
  return 'In Stock';
});

// Methods
const nextImage = () => {
  if (!props.product || !props.product.images) return;
  currentImageIndex.value = (currentImageIndex.value + 1) % props.product.images.length;
};

const prevImage = () => {
  if (!props.product || !props.product.images) return;
  currentImageIndex.value = currentImageIndex.value === 0 ? props.product.images.length - 1 : currentImageIndex.value - 1;
};

const selectImage = (index: number) => {
  currentImageIndex.value = index;
};

const toggleZoom = () => {
  isZoomed.value = !isZoomed.value;
};

const close = () => {
  currentImageIndex.value = 0;
  isZoomed.value = false;
  emit('close');
};

const editProduct = () => {
  emit('edit', props.product);
  close();
};

const deleteProduct = () => {
  emit('delete', props.product);
  close();
};
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show && product"
        class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 z-50 flex items-center justify-center p-4"
        @click.self="close"
      >
        <Transition
          enter-active-class="transition-all duration-300"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition-all duration-300"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="show && product"
            class="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto"
            @click.stop
          >
            <!-- Header -->
            <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-900">Product Quick View</h2>
              <button
                @click="close"
                class="text-gray-400 hover:text-gray-600 transition-colors cursor-pointer"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Content -->
            <div class="p-6">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Image Gallery -->
                <div class="space-y-4">
                  <!-- Main Image -->
                  <div class="relative bg-gray-100 rounded-lg overflow-hidden aspect-square">
                    <img
                      v-if="currentImage"
                      :src="currentImage.url"
                      :alt="currentImage.alt_text || product.name"
                      :class="[
                        'w-full h-full object-contain transition-transform duration-300',
                        isZoomed ? 'scale-150 cursor-zoom-out' : 'cursor-zoom-in'
                      ]"
                      @click="toggleZoom"
                    />
                    <div
                      v-else
                      class="w-full h-full flex items-center justify-center text-gray-400"
                    >
                      <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>

                    <!-- Navigation Arrows -->
                    <template v-if="hasMultipleImages">
                      <button
                        @click="prevImage"
                        class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 hover:bg-white rounded-full shadow-lg flex items-center justify-center transition-all cursor-pointer"
                      >
                        <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                      </button>
                      <button
                        @click="nextImage"
                        class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 hover:bg-white rounded-full shadow-lg flex items-center justify-center transition-all cursor-pointer"
                      >
                        <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                      </button>
                    </template>

                    <!-- Discount Badge -->
                    <div
                      v-if="hasDiscount"
                      class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold"
                    >
                      -{{ discountPercentage }}%
                    </div>
                  </div>

                  <!-- Thumbnail Gallery -->
                  <div v-if="hasMultipleImages" class="flex gap-2 overflow-x-auto pb-2">
                    <button
                      v-for="(image, index) in product.images"
                      :key="image.id"
                      @click="selectImage(index)"
                      :class="[
                        'flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all cursor-pointer',
                        currentImageIndex === index ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-300'
                      ]"
                    >
                      <img
                        :src="image.url"
                        :alt="image.alt_text || product.name"
                        class="w-full h-full object-cover"
                      />
                    </button>
                  </div>
                </div>

                <!-- Product Info -->
                <div class="space-y-6">
                  <!-- Title & SKU -->
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ product.name }}</h3>
                    <p class="text-sm text-gray-500">SKU: {{ product.sku }}</p>
                  </div>

                  <!-- Categories -->
                  <div v-if="product.categories && product.categories.length > 0" class="flex flex-wrap gap-2">
                    <span
                      v-for="category in product.categories"
                      :key="category.id"
                      class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full"
                    >
                      {{ category.name }}
                    </span>
                  </div>

                  <!-- Price -->
                  <div class="border-t border-b border-gray-200 py-4">
                    <div class="flex items-baseline gap-3">
                      <span class="text-3xl font-bold text-gray-900">{{ formatPrice(finalPrice) }}</span>
                      <span v-if="hasDiscount" class="text-xl text-gray-500 line-through">{{ formatPrice(product.price) }}</span>
                    </div>
                  </div>

                  <!-- Stock Status -->
                  <div class="flex items-center gap-4">
                    <span
                      :class="`px-4 py-2 rounded-full text-sm font-medium bg-${stockStatusColor}-100 text-${stockStatusColor}-800`"
                    >
                      {{ stockStatusText }}
                    </span>
                    <span class="text-sm text-gray-600">{{ product.quantity }} units available</span>
                  </div>

                  <!-- Short Description -->
                  <div v-if="product.short_description" class="text-gray-700">
                    <p class="text-sm leading-relaxed">{{ product.short_description }}</p>
                  </div>

                  <!-- Description -->
                  <div v-if="product.description" class="border-t border-gray-200 pt-6">
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Description</h4>
                    <div class="text-sm text-gray-700 leading-relaxed max-h-40 overflow-y-auto" v-html="product.description"></div>
                  </div>

                  <!-- Actions -->
                  <div class="border-t border-gray-200 pt-6 flex gap-3">
                    <button
                      @click="editProduct"
                      class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors cursor-pointer font-medium"
                    >
                      Edit Product
                    </button>
                    <button
                      @click="deleteProduct"
                      class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors cursor-pointer font-medium"
                    >
                      Delete
                    </button>
                  </div>

                  <!-- Additional Info -->
                  <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="bg-gray-50 rounded-lg p-3">
                      <p class="text-gray-500 mb-1">Views</p>
                      <p class="text-gray-900 font-semibold">0</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                      <p class="text-gray-500 mb-1">Sales</p>
                      <p class="text-gray-900 font-semibold">0</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
