<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ThemeLayout from '@/../../themes/vortex-default/resources/views/layouts/ThemeLayout.vue';
import CartItemSkeleton from '@/../../themes/vortex-default/resources/views/components/CartItemSkeleton.vue';
import { useCart } from '@/composables/useCart';
import { useCurrency } from '@/composables/useCurrency';

// Define props
interface CartSummary {
    subtotal: number;
    taxes: {
        breakdown: Array<{
            tax_class: string;
            tax_class_id: number;
            rate: number;
            rate_id: number;
            amount: number;
            label: string;
        }>;
        total: number;
    };
    shipping: {
        options: Array<{
            id: number;
            code: string | null;
            name: string;
            description: string | null;
            cost: number;
            estimated_days: number | null;
            sort_order: number | null;
        }>;
        selected: any;
        cost: number;
    };
    total: number;
}

defineProps<{
    cartSummary: CartSummary;
}>();

const { 
    items, 
    loading, 
    itemCount, 
    updateQuantity, 
    removeItem, 
    fetchCart 
} = useCart();

// Currency formatting
const { formatPrice } = useCurrency();

// Computed values
const isEmpty = computed(() => items.value.length === 0);

// Load cart on mount
onMounted(async () => {
    await fetchCart();
});

// Local loading states for individual items
const updatingItems = ref<Set<string>>(new Set());
const removingItems = ref<Set<string>>(new Set());

// Check if item is being updated
const isItemUpdating = (itemId: string) => updatingItems.value.has(itemId);
const isItemRemoving = (itemId: string) => removingItems.value.has(itemId);

// Handle quantity change
const handleQuantityChange = async (itemId: string, newQuantity: number) => {
    if (newQuantity < 1) {
        await handleRemove(itemId);
        return;
    }

    // Add to updating set
    updatingItems.value.add(itemId);

    const result = await updateQuantity(itemId, newQuantity);
    
    // Remove from updating set
    updatingItems.value.delete(itemId);

    if (!result.success) {
        alert('Failed to update quantity');
    } else {
        // Reload page to refresh cart summary with new calculations
        window.location.reload();
    }
};

// Handle remove item
const handleRemove = async (itemId: string) => {
    // Add to removing set
    removingItems.value.add(itemId);

    const result = await removeItem(itemId);
    
    // Remove from removing set (will be gone from items array anyway)
    removingItems.value.delete(itemId);

    if (!result.success) {
        alert('Failed to remove item');
    } else {
        // Reload page to refresh cart summary
        window.location.reload();
    }
};
</script>

<template>
    <Head title="Shopping Cart" />

    <ThemeLayout>
        <div class="container mx-auto px-4 py-8 max-w-7xl">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
                <p class="mt-2 text-gray-600">{{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }} in your cart</p>
            </div>

            <!-- Loading State with Skeletons (only on first load) -->
            <div v-if="loading && items.length === 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items Skeletons -->
                <div class="lg:col-span-2 space-y-4">
                    <CartItemSkeleton v-for="i in 3" :key="i" />
                </div>

                <!-- Order Summary Skeleton -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4 animate-pulse">
                        <div class="h-6 bg-gray-200 rounded w-32 mb-4"></div>
                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between">
                                <div class="h-5 bg-gray-200 rounded w-20"></div>
                                <div class="h-5 bg-gray-200 rounded w-16"></div>
                            </div>
                            <div class="flex justify-between">
                                <div class="h-5 bg-gray-200 rounded w-20"></div>
                                <div class="h-5 bg-gray-200 rounded w-16"></div>
                            </div>
                            <div class="flex justify-between">
                                <div class="h-5 bg-gray-200 rounded w-24"></div>
                                <div class="h-5 bg-gray-200 rounded w-16"></div>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between">
                                    <div class="h-6 bg-gray-200 rounded w-16"></div>
                                    <div class="h-6 bg-gray-200 rounded w-20"></div>
                                </div>
                            </div>
                        </div>
                        <div class="h-12 bg-gray-200 rounded mb-4"></div>
                        <div class="h-12 bg-gray-200 rounded"></div>
                    </div>
                </div>
            </div>

            <!-- Empty Cart -->
            <div v-else-if="isEmpty" class="text-center py-12 bg-gray-50 rounded-lg">
                <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h2 class="mt-4 text-2xl font-semibold text-gray-900">Your cart is empty</h2>
                <p class="mt-2 text-gray-600">Add some products to get started!</p>
                <Link
                    href="/products"
                    class="mt-6 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                >
                    Continue Shopping
                </Link>
            </div>

            <!-- Cart Items -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items List -->
                <div class="lg:col-span-2 space-y-4">
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col sm:flex-row gap-4 relative transition-opacity"
                        :class="{ 'opacity-50': isItemRemoving(item.id) }"
                    >
                        <!-- Product Image -->
                        <Link :href="`/products/${item.product_slug}`" class="flex-shrink-0">
                            <div v-if="item.product_image && item.product_image.trim()" class="w-24 h-24 rounded-md overflow-hidden">
                                <img
                                    :src="item.product_image"
                                    :alt="item.product_name"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div v-else class="w-24 h-24 rounded-md bg-gray-100 flex items-center justify-center text-4xl">
                                📦
                            </div>
                        </Link>

                        <!-- Product Info -->
                        <div class="flex-1">
                            <Link
                                :href="`/products/${item.product_slug}`"
                                class="text-lg font-semibold text-gray-900 hover:text-blue-600"
                            >
                                {{ item.product_name }}
                            </Link>

                            <!-- Attributes -->
                            <div v-if="item.attributes && Object.keys(item.attributes).length > 0" class="mt-1 text-sm text-gray-600">
                                <span v-for="(value, key) in item.attributes" :key="key" class="mr-3">
                                    <span class="font-medium">{{ key }}:</span> {{ value }}
                                </span>
                            </div>

                            <!-- Price -->
                            <p class="mt-2 text-xl font-bold text-gray-900">
                                {{ formatPrice(item.price) }}
                            </p>

                            <!-- Quantity & Remove -->
                            <div class="mt-4 flex items-center gap-4">
                                <!-- Quantity Selector -->
                                <div class="flex items-center border border-gray-300 rounded-md relative">
                                    <button
                                        @click="handleQuantityChange(item.id, item.quantity - 1)"
                                        class="px-3 py-1 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                        :disabled="isItemUpdating(item.id) || isItemRemoving(item.id)"
                                    >
                                        −
                                    </button>
                                    <input
                                        type="number"
                                        :value="item.quantity"
                                        @change="(e) => handleQuantityChange(item.id, parseInt((e.target as HTMLInputElement).value))"
                                        class="w-16 text-center border-x border-gray-300 py-1 focus:outline-none disabled:bg-gray-50"
                                        min="1"
                                        :disabled="isItemUpdating(item.id) || isItemRemoving(item.id)"
                                    />
                                    <button
                                        @click="handleQuantityChange(item.id, item.quantity + 1)"
                                        class="px-3 py-1 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                        :disabled="isItemUpdating(item.id) || isItemRemoving(item.id)"
                                    >
                                        +
                                    </button>
                                    
                                    <!-- Item updating spinner -->
                                    <div v-if="isItemUpdating(item.id)" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 rounded-md">
                                        <div class="h-4 w-4 animate-spin rounded-full border-2 border-solid border-blue-600 border-r-transparent"></div>
                                    </div>
                                </div>

                                <!-- Remove Button -->
                                <button
                                    @click="handleRemove(item.id)"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                    :disabled="isItemUpdating(item.id) || isItemRemoving(item.id)"
                                >
                                    <span v-if="isItemRemoving(item.id)">Removing...</span>
                                    <span v-else>Remove</span>
                                </button>
                            </div>
                        </div>

                        <!-- Item Subtotal -->
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Subtotal</p>
                            <p class="text-xl font-bold text-gray-900">
                                {{ formatPrice(item.price * item.quantity) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Order Summary</h2>

                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between text-gray-700">
                                <span>Subtotal</span>
                                <span>{{ formatPrice(cartSummary.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-700">
                                <span>Shipping</span>
                                <span>{{ formatPrice(cartSummary.shipping.cost) }}</span>
                            </div>
                            
                            <!-- Tax Breakdown -->
                            <div v-if="cartSummary.taxes.breakdown && cartSummary.taxes.breakdown.length > 0">
                                <div v-for="taxItem in cartSummary.taxes.breakdown" :key="taxItem.tax_class_id" class="flex justify-between text-gray-700">
                                    <span>{{ taxItem.label }}</span>
                                    <span>{{ formatPrice(taxItem.amount) }}</span>
                                </div>
                            </div>
                            <div v-else class="flex justify-between text-gray-700">
                                <span>Tax</span>
                                <span>{{ formatPrice(cartSummary.taxes.total) }}</span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-bold text-gray-900">
                                    <span>Total</span>
                                    <span>{{ formatPrice(cartSummary.total) }}</span>
                                </div>
                            </div>
                        </div>

                        <Link
                            href="/checkout"
                            class="w-full block text-center bg-blue-600 text-white py-3 px-4 rounded-md font-semibold hover:bg-blue-700 transition-colors cursor-pointer"
                        >
                            Proceed to Checkout
                        </Link>

                        <Link
                            href="/products"
                            class="mt-4 block w-full text-center bg-gray-100 text-gray-900 py-3 px-4 rounded-md font-semibold hover:bg-gray-200 transition-colors cursor-pointer"
                        >
                            Continue Shopping
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </ThemeLayout>
</template>
