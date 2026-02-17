<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../components/Breadcrumb.vue';
import { useCart } from '@/composables/useCart';
import { useCurrency } from '@/composables/useCurrency';
import { Minus, Plus, Trash2, ShoppingCart, ArrowRight, Tag } from 'lucide-vue-next';

interface CartItem {
    id: string; product_id: number; product_name: string; product_slug: string; product_image: string | null;
    price: number; quantity: number; attributes?: Record<string, string>; subtotal?: number;
}
interface CartSummary { subtotal: number; taxes: number; shipping: number; discount: number; total: number; }

interface Props {
    cartSummary?: CartSummary;
    theme: { name: string; slug: string };
    siteConfig: { name: string };
}

const props = defineProps<Props>();
const { items, itemCount, subtotal, updateQuantity, removeItem, loading, fetchCart } = useCart();

onMounted(async () => {
    await fetchCart();
});
const { formatPrice } = useCurrency();

const couponCode = ref('');
const couponApplying = ref(false);
const applyCoupon = async () => {
    if (!couponCode.value.trim()) return;
    couponApplying.value = true;
    // Coupon logic handled by backend
    setTimeout(() => { couponApplying.value = false; }, 1000);
};

const summary = computed(() => {
    const cs = props.cartSummary;
    if (!cs) return { subtotal: subtotal.value, taxes: 0, shipping: 0, discount: 0, total: subtotal.value };
    return {
        subtotal: cs.subtotal ?? 0,
        taxes: typeof cs.taxes === 'object' ? (cs.taxes as any)?.total ?? 0 : cs.taxes ?? 0,
        shipping: typeof cs.shipping === 'object' ? (cs.shipping as any)?.cost ?? 0 : cs.shipping ?? 0,
        discount: cs.discount ?? 0,
        total: cs.total ?? 0,
    };
});

const breadcrumbs = [{ label: 'Shopping Cart' }];
</script>

<template>
    <ThemeLayout>
        <Head title="Shopping Cart" />

        <Breadcrumb :items="breadcrumbs" />

        <section class="py-8 lg:py-12">
            <div class="dmart-container">
                <h1 class="text-2xl lg:text-3xl font-extrabold mb-8 text-title font-title">
                    Shopping Cart
                </h1>

                <div v-if="items.length > 0" class="lg:grid lg:grid-cols-[1fr_380px] gap-8">
                    <!-- Cart Items -->
                    <div>
                        <!-- Desktop Table -->
                        <div class="hidden md:block">
                            <table class="dmart-cart-table w-full">
                                <thead>
                                    <tr>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-title font-title">Product</th>
                                        <th class="text-center py-3 px-4 text-sm font-semibold">Price</th>
                                        <th class="text-center py-3 px-4 text-sm font-semibold">Quantity</th>
                                        <th class="text-right py-3 px-4 text-sm font-semibold">Subtotal</th>
                                        <th class="w-12"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in items" :key="item.id" class="border-b">
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-4">
                                                <Link :href="`/product/${item.product_slug}`" class="w-20 h-20 rounded-xl overflow-hidden bg-gray-50 flex-shrink-0">
                                                    <img :src="item.product_image || '/images/placeholder.png'" :alt="item.product_name" class="w-full h-full object-cover" />
                                                </Link>
                                                <div>
                                                    <Link :href="`/product/${item.product_slug}`" class="font-semibold text-sm hover:text-theme-1 transition-colors text-title">
                                                        {{ item.product_name }}
                                                    </Link>
                                                    <div v-if="item.attributes" class="text-xs text-gray-400 mt-1">
                                                        <span v-for="(val, key) in item.attributes" :key="key">{{ key }}: {{ val }} </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center py-4 px-4 font-semibold text-sm">{{ formatPrice(item.price) }}</td>
                                        <td class="text-center py-4 px-4">
                                            <div class="dmart-qty-selector inline-flex">
                                                <button @click="updateQuantity(item.id, item.quantity - 1)" :disabled="item.quantity <= 1 || loading"><Minus class="w-3.5 h-3.5" /></button>
                                                <span class="w-10 text-center text-sm">{{ item.quantity }}</span>
                                                <button @click="updateQuantity(item.id, item.quantity + 1)" :disabled="loading"><Plus class="w-3.5 h-3.5" /></button>
                                            </div>
                                        </td>
                                        <td class="text-right py-4 px-4 font-bold text-sm text-theme-1">{{ formatPrice(item.subtotal || item.price * item.quantity) }}</td>
                                        <td class="py-4 px-2">
                                            <button @click="removeItem(item.id)" class="text-gray-400 hover:text-red-500 transition-colors"><Trash2 class="w-4 h-4" /></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards -->
                        <div class="md:hidden space-y-4">
                            <div v-for="item in items" :key="item.id" class="flex gap-4 p-4 border rounded-xl">
                                <Link :href="`/product/${item.product_slug}`" class="w-20 h-20 rounded-lg overflow-hidden bg-gray-50 flex-shrink-0">
                                    <img :src="item.product_image || '/images/placeholder.png'" :alt="item.product_name" class="w-full h-full object-cover" />
                                </Link>
                                <div class="flex-1 min-w-0">
                                    <Link :href="`/product/${item.product_slug}`" class="font-semibold text-sm line-clamp-2">{{ item.product_name }}</Link>
                                    <div class="font-bold mt-1 text-theme-1">{{ formatPrice(item.price) }}</div>
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="dmart-qty-selector inline-flex">
                                            <button @click="updateQuantity(item.id, item.quantity - 1)" :disabled="item.quantity <= 1"><Minus class="w-3 h-3" /></button>
                                            <span class="w-8 text-center text-xs">{{ item.quantity }}</span>
                                            <button @click="updateQuantity(item.id, item.quantity + 1)"><Plus class="w-3 h-3" /></button>
                                        </div>
                                        <button @click="removeItem(item.id)" class="text-gray-400 hover:text-red-500"><Trash2 class="w-4 h-4" /></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coupon -->
                        <div class="mt-6 flex gap-3">
                            <div class="relative flex-1 max-w-xs">
                                <Tag class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input
                                    v-model="couponCode"
                                    type="text"
                                    placeholder="Coupon code"
                                    class="w-full pl-10 pr-4 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0"
                                />
                            </div>
                            <button @click="applyCoupon" :disabled="couponApplying" class="dmart-btn dmart-btn-outline px-6 py-3 text-sm">
                                {{ couponApplying ? 'Applying...' : 'Apply Coupon' }}
                            </button>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="mt-8 lg:mt-0">
                        <div class="rounded-2xl border p-6 sticky top-24 space-y-4">
                            <h3 class="text-lg font-bold text-title font-title">
                                Order Summary
                            </h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between"><span class="text-gray-500">Subtotal ({{ itemCount }} items)</span><span class="font-semibold">{{ formatPrice(summary.subtotal) }}</span></div>
                                <div v-if="summary.shipping > 0" class="flex justify-between"><span class="text-gray-500">Shipping</span><span class="font-semibold">{{ formatPrice(summary.shipping) }}</span></div>
                                <div v-else class="flex justify-between"><span class="text-gray-500">Shipping</span><span class="font-semibold text-green-600">Free</span></div>
                                <div v-if="summary.taxes > 0" class="flex justify-between"><span class="text-gray-500">Tax</span><span class="font-semibold">{{ formatPrice(summary.taxes) }}</span></div>
                                <div v-if="summary.discount > 0" class="flex justify-between"><span class="text-gray-500">Discount</span><span class="font-semibold text-green-600">-{{ formatPrice(summary.discount) }}</span></div>
                            </div>
                            <div class="border-t pt-4 flex justify-between">
                                <span class="text-lg font-bold text-title">Total</span>
                                <span class="text-lg font-extrabold text-theme-1">{{ formatPrice(summary.total) }}</span>
                            </div>
                            <Link href="/checkout" class="dmart-btn dmart-btn-primary w-full py-3.5 text-base justify-center">
                                Proceed to Checkout <ArrowRight class="w-4 h-4" />
                            </Link>
                            <Link href="/products" class="block text-center text-sm font-semibold text-theme-1">
                                Continue Shopping
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty Cart -->
                <div v-else class="text-center py-20">
                    <ShoppingCart class="w-20 h-20 mx-auto mb-4 text-gray-300" />
                    <h3 class="text-xl font-bold mb-2 text-title font-title">Your cart is empty</h3>
                    <p class="text-gray-500 mb-6">Looks like you haven't added anything to your cart yet.</p>
                    <Link href="/products" class="dmart-btn dmart-btn-primary">Start Shopping</Link>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>
