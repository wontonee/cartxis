<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeLayout from '../../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import { useCurrency } from '@/composables/useCurrency';
import { Package, Truck, CheckCircle, Clock, MapPin, CreditCard, ArrowLeft } from 'lucide-vue-next';

interface OrderItem {
    id: number; name: string; slug: string; image: string | null;
    price: number; quantity: number; subtotal: number; options?: Record<string, string>;
}
interface Order {
    id: number; order_number: string; status: string; total: number;
    subtotal: number; shipping: number; taxes: number; discount: number;
    items: OrderItem[]; created_at: string; payment_method?: string;
    shipping_address?: { first_name: string; last_name: string; address_line_1: string; city: string; state: string; postal_code: string; country: string; phone?: string };
    billing_address?: { first_name: string; last_name: string; address_line_1: string; city: string; state: string; postal_code: string; country: string };
}

interface Props {
    order: Order;
    theme: { name: string; slug: string };
}

const props = defineProps<Props>();
const { formatPrice } = useCurrency();

const statusSteps = ['pending', 'processing', 'shipped', 'delivered'];
const currentStepIndex = statusSteps.indexOf(props.order.status.toLowerCase());
const stepIcons = [Clock, Package, Truck, CheckCircle];

const breadcrumbs = [
    { label: 'My Account', url: '/account' },
    { label: 'Orders', url: '/account/orders' },
    { label: `#${props.order.order_number}` },
];
</script>

<template>
    <ThemeLayout>
        <Head :title="`Order #${order.order_number}`" />
        <Breadcrumb :items="breadcrumbs" />

        <section class="py-8 lg:py-12">
            <div class="dmart-container max-w-4xl">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-extrabold text-title font-title">
                            Order #{{ order.order_number }}
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">Placed on {{ order.created_at }}</p>
                    </div>
                    <Link href="/account/orders" class="flex items-center gap-1 text-sm font-semibold text-theme-1">
                        <ArrowLeft class="w-4 h-4" /> Back to Orders
                    </Link>
                </div>

                <!-- Status Tracker -->
                <div v-if="currentStepIndex >= 0" class="border rounded-2xl p-6 mb-8">
                    <div class="flex items-center justify-between relative">
                        <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200 -z-10" />
                        <div class="absolute top-5 left-0 h-0.5 -z-10 transition-all bg-theme-1" :style="{ width: `${(currentStepIndex / (statusSteps.length - 1)) * 100}%` }" />
                        <div v-for="(step, i) in statusSteps" :key="step" class="flex flex-col items-center gap-2 z-10">
                            <div
                                class="w-10 h-10 rounded-full flex items-center justify-center"
                                :class="i <= currentStepIndex ? 'text-white bg-theme-1' : 'bg-gray-200 text-gray-400'"
                            >
                                <component :is="stepIcons[i]" class="w-5 h-5" />
                            </div>
                            <span class="text-xs font-semibold capitalize" :class="i <= currentStepIndex ? 'text-theme-1' : 'text-gray-400'">{{ step }}</span>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="border rounded-2xl overflow-hidden mb-8">
                    <div class="p-5 border-b bg-gray-50">
                        <h3 class="font-bold font-title">Items ({{ order.items.length }})</h3>
                    </div>
                    <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4 p-5 border-b last:border-0">
                        <Link :href="item.slug ? `/product/${item.slug}` : '/shop'" class="w-16 h-16 rounded-xl overflow-hidden bg-gray-50 flex-shrink-0">
                            <img :src="item.image || '/images/placeholder.png'" :alt="item.name" class="w-full h-full object-cover" />
                        </Link>
                        <div class="flex-1 min-w-0">
                            <Link :href="item.slug ? `/product/${item.slug}` : '/shop'" class="font-semibold text-sm hover:text-theme-1">{{ item.name }}</Link>
                            <div v-if="item.options" class="text-xs text-gray-400 mt-0.5">
                                <span v-for="(val, key) in item.options" :key="key">{{ key }}: {{ val }} </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">{{ formatPrice(item.price) }} × {{ item.quantity }}</div>
                        </div>
                        <div class="font-bold text-sm text-theme-1">{{ formatPrice(item.subtotal) }}</div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Addresses -->
                    <div class="space-y-6">
                        <div v-if="order.shipping_address" class="border rounded-2xl p-5">
                            <h3 class="font-bold flex items-center gap-2 mb-3 font-title">
                                <MapPin class="w-4 h-4 text-theme-1" /> Shipping Address
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ order.shipping_address.first_name }} {{ order.shipping_address.last_name }}<br />
                                {{ order.shipping_address.address_line_1 }}<br />
                                {{ order.shipping_address.city }}, {{ order.shipping_address.state }} {{ order.shipping_address.postal_code }}<br />
                                {{ order.shipping_address.country }}
                                <br v-if="order.shipping_address.phone" /><span v-if="order.shipping_address.phone">{{ order.shipping_address.phone }}</span>
                            </p>
                        </div>
                        <div v-if="order.payment_method" class="border rounded-2xl p-5">
                            <h3 class="font-bold flex items-center gap-2 mb-3 font-title">
                                <CreditCard class="w-4 h-4 text-theme-1" /> Payment
                            </h3>
                            <p class="text-sm text-gray-600">{{ order.payment_method }}</p>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="border rounded-2xl p-5 space-y-3 text-sm">
                        <h3 class="font-bold font-title">Order Summary</h3>
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>{{ formatPrice(order.subtotal) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Shipping</span><span>{{ order.shipping ? formatPrice(order.shipping) : 'Free' }}</span></div>
                        <div v-if="order.taxes" class="flex justify-between"><span class="text-gray-500">Tax</span><span>{{ formatPrice(order.taxes) }}</span></div>
                        <div v-if="order.discount" class="flex justify-between"><span class="text-gray-500">Discount</span><span class="text-green-600">-{{ formatPrice(order.discount) }}</span></div>
                        <div class="flex justify-between border-t pt-3 text-base">
                            <span class="font-bold text-title">Total</span>
                            <span class="font-extrabold text-theme-1">{{ formatPrice(order.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>
