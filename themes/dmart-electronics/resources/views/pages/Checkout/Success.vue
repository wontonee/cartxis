<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { CheckCircle, Package, ArrowRight } from 'lucide-vue-next';

// Match the exact shape from CheckoutController@success
interface OrderItem {
    product_name: string; product_image: string | null;
    quantity: number; price: number; total: number;
}
interface Order {
    id: number; order_number: string; status: string;
    total: number; subtotal: number; shipping_cost: number; tax: number;
    customer_email?: string; customer_phone?: string;
    items: OrderItem[]; created_at: string;
    shipping_address?: {
        first_name: string; last_name: string;
        address_line1: string; address_line2?: string;
        city: string; state: string; postal_code: string; country: string;
        phone?: string;
    };
    payment_method?: string;
    is_guest?: boolean;
}

const props = defineProps<{ order: Order }>();
const { formatPrice } = useCurrency();
</script>

<template>
    <ThemeLayout>
        <Head title="Order Confirmed" />

        <section class="py-10 lg:py-16">
            <div class="max-w-[580px] mx-auto px-4">
                <!-- Success Message -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center bg-green-50">
                        <CheckCircle class="w-8 h-8 text-green-600" />
                    </div>
                    <h1 class="text-xl lg:text-2xl font-extrabold mb-2 text-title font-title">
                        Thank You For Your Order!
                    </h1>
                    <p class="text-sm text-gray-500">Your order has been placed successfully. We'll send you a confirmation email shortly.</p>
                </div>

                <!-- Order Details Card -->
                <div class="border rounded-xl overflow-hidden shadow-sm">
                    <div class="px-5 py-4 border-b bg-gray-50">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-xs text-gray-500">Order Number</div>
                                <div class="text-sm font-bold text-theme-1">#{{ order.order_number }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Date</div>
                                <div class="text-xs font-semibold">{{ order.created_at }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Status</div>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                    <Package class="w-3 h-3" /> {{ order.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="px-5 py-4 border-b">
                        <h3 class="text-sm font-bold mb-3 font-title">Items Ordered</h3>
                        <div v-for="(item, idx) in order.items" :key="idx" class="flex items-center gap-3 py-2 border-b last:border-0">
                            <img v-if="item.product_image" :src="item.product_image" :alt="item.product_name" class="w-12 h-12 rounded-lg object-cover bg-gray-100 shrink-0" />
                            <div v-else class="w-12 h-12 rounded-lg bg-gray-100 shrink-0 flex items-center justify-center">
                                <Package class="w-5 h-5 text-gray-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium truncate">{{ item.product_name }}</div>
                                <div class="text-xs text-gray-400">Qty: {{ item.quantity }} × {{ formatPrice(item.price) }}</div>
                            </div>
                            <span class="text-sm font-semibold shrink-0">{{ formatPrice(item.total) }}</span>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="px-5 py-4 border-b space-y-1.5 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>{{ formatPrice(order.subtotal) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Shipping</span><span>{{ order.shipping_cost ? formatPrice(order.shipping_cost) : 'Free' }}</span></div>
                        <div v-if="order.tax" class="flex justify-between"><span class="text-gray-500">Tax</span><span>{{ formatPrice(order.tax) }}</span></div>
                        <div class="flex justify-between border-t pt-2 text-base">
                            <span class="font-bold text-title">Total</span>
                            <span class="font-extrabold text-theme-1">{{ formatPrice(order.total) }}</span>
                        </div>
                    </div>

                    <!-- Shipping & Payment -->
                    <div class="px-5 py-4 grid sm:grid-cols-2 gap-4">
                        <div v-if="order.shipping_address">
                            <h4 class="text-xs font-bold mb-1 uppercase tracking-wide text-gray-500">Shipping Address</h4>
                            <p class="text-sm text-gray-700 leading-relaxed">
                                {{ order.shipping_address.first_name }} {{ order.shipping_address.last_name }}<br />
                                {{ order.shipping_address.address_line1 }}
                                <template v-if="order.shipping_address.address_line2"><br />{{ order.shipping_address.address_line2 }}</template><br />
                                {{ order.shipping_address.city }}, {{ order.shipping_address.state }} {{ order.shipping_address.postal_code }}<br />
                                {{ order.shipping_address.country }}
                            </p>
                        </div>
                        <div v-if="order.payment_method">
                            <h4 class="text-xs font-bold mb-1 uppercase tracking-wide text-gray-500">Payment Method</h4>
                            <p class="text-sm text-gray-700">{{ order.payment_method }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-6">
                    <Link :href="order.is_guest ? `/checkout/track-order?order_number=${encodeURIComponent(order.order_number)}` : `/account/orders/${order.id}`" class="dmart-btn dmart-btn-primary text-sm">
                        <Package class="w-4 h-4" /> Track Order
                    </Link>
                    <Link href="/products" class="dmart-btn dmart-btn-outline text-sm">
                        Continue Shopping <ArrowRight class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>
