<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { X, Minus, Plus, Trash2, ShoppingBag } from 'lucide-vue-next';
import { useCart } from '@/composables/useCart';
import { useCurrency } from '@/composables/useCurrency';

interface Props {
    open: boolean;
}

defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const { items, loading, itemCount, subtotal, updateQuantity, removeItem } = useCart();
const { formatPrice } = useCurrency();
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div v-if="open" class="fixed inset-0 bg-black/50 z-[60]" @click="$emit('close')" />
    </Transition>

    <!-- Drawer -->
    <Transition name="slide-right">
        <div v-if="open" class="fixed right-0 top-0 bottom-0 w-[380px] max-w-[90vw] bg-white z-[70] flex flex-col shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b bg-title">
                <div class="flex items-center gap-2 text-white">
                    <ShoppingBag class="w-5 h-5" />
                    <span class="font-bold text-lg font-title">
                        Cart ({{ itemCount }})
                    </span>
                </div>
                <button @click="$emit('close')" class="text-white p-1"><X class="w-5 h-5" /></button>
            </div>

            <!-- Items -->
            <div class="flex-1 overflow-y-auto py-4 px-5">
                <template v-if="items.length > 0">
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="flex gap-4 py-4 border-b border-gray-100 last:border-0"
                    >
                        <Link :href="`/product/${item.product_slug || item.slug}`" class="w-20 h-20 flex-shrink-0 bg-gray-50 rounded-lg overflow-hidden" @click="$emit('close')">
                            <img :src="item.product_image || item.image" :alt="item.product_name || item.name" class="w-full h-full object-contain" />
                        </Link>
                        <div class="flex-1 min-w-0">
                            <Link
                                :href="`/product/${item.product_slug || item.slug}`"
                                class="text-sm font-semibold text-gray-900 hover:text-theme-1 line-clamp-2 transition-colors"
                                @click="$emit('close')"
                            >
                                {{ item.product_name || item.name }}
                            </Link>
                            <div class="text-sm font-bold mt-1 text-theme-1">
                                {{ formatPrice(item.price) }}
                            </div>
                            <div class="flex items-center gap-3 mt-2">
                                <div class="dmart-qty-selector">
                                    <button @click="updateQuantity(item.id, item.quantity - 1)" :disabled="item.quantity <= 1 || loading">
                                        <Minus class="w-3 h-3" />
                                    </button>
                                    <input :value="item.quantity" readonly class="!bg-transparent" />
                                    <button @click="updateQuantity(item.id, item.quantity + 1)" :disabled="loading">
                                        <Plus class="w-3 h-3" />
                                    </button>
                                </div>
                                <button @click="removeItem(item.id)" class="text-gray-400 hover:text-red-500 transition-colors">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                <div v-else class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <ShoppingBag class="w-16 h-16 mb-4" />
                    <p class="text-lg font-semibold text-gray-700">Your cart is empty</p>
                    <p class="text-sm mt-1">Add some products to get started</p>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="items.length > 0" class="border-t px-5 py-4 space-y-3">
                <div class="flex items-center justify-between text-lg font-bold text-title font-title">
                    <span>Subtotal</span>
                    <span>{{ formatPrice(subtotal) }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <Link
                        href="/cart"
                        class="dmart-btn dmart-btn-outline text-center text-sm"
                        @click="$emit('close')"
                    >
                        View Cart
                    </Link>
                    <Link
                        href="/checkout"
                        class="dmart-btn dmart-btn-primary text-center text-sm"
                        @click="$emit('close')"
                    >
                        Checkout
                    </Link>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.3s ease; }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }
</style>
