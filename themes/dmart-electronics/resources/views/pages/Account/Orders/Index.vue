<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeLayout from '../../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import { useCurrency } from '@/composables/useCurrency';
import { Package, ChevronRight, ShoppingCart } from 'lucide-vue-next';

interface Order {
    id: number; order_number: string; status: string;
    total: number; items_count: number; created_at: string;
}
interface PaginationLink { url: string | null; label: string; active: boolean; }
interface PaginatedOrders {
    data: Order[]; current_page: number; last_page: number;
    total: number; links: PaginationLink[];
}

interface Props {
    orders: PaginatedOrders;
    theme: { name: string; slug: string };
}

const props = defineProps<Props>();
const { formatPrice } = useCurrency();

const statusColor = (status: string) => {
    const map: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-700', processing: 'bg-blue-100 text-blue-700',
        shipped: 'bg-purple-100 text-purple-700', delivered: 'bg-green-100 text-green-700',
        cancelled: 'bg-red-100 text-red-700',
    };
    return map[status.toLowerCase()] || 'bg-gray-100 text-gray-700';
};

const breadcrumbs = [
    { label: 'My Account', url: '/account' },
    { label: 'Orders' },
];
</script>

<template>
    <ThemeLayout>
        <Head title="My Orders" />
        <Breadcrumb :items="breadcrumbs" />

        <section class="py-8 lg:py-12">
            <div class="dmart-container">
                <h1 class="text-2xl font-extrabold mb-6 text-title font-title">My Orders</h1>

                <div v-if="orders.data.length > 0" class="space-y-4">
                    <Link
                        v-for="order in orders.data"
                        :key="order.id"
                        :href="`/account/orders/${order.id}`"
                        class="flex items-center justify-between p-5 border rounded-xl hover:border-theme-1 hover:shadow-md transition-all"
                    >
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center bg-gray-100"><Package class="w-5 h-5 text-gray-500" /></div>
                            <div>
                                <div class="font-bold text-sm text-theme-1">#{{ order.order_number }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ order.created_at }} · {{ order.items_count }} item(s)</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold hidden sm:inline" :class="statusColor(order.status)">{{ order.status }}</span>
                            <span class="font-bold text-sm">{{ formatPrice(order.total) }}</span>
                            <ChevronRight class="w-4 h-4 text-gray-400" />
                        </div>
                    </Link>

                    <nav v-if="orders.last_page > 1" class="flex items-center justify-center gap-1 mt-8">
                        <template v-for="link in orders.links" :key="link.label">
                            <Link v-if="link.url" :href="link.url" class="px-3 py-2 rounded-lg text-sm font-medium" :class="link.active ? 'bg-theme-1 text-white' : 'hover:bg-gray-100 text-gray-600'" v-html="link.label" preserve-state />
                            <span v-else class="px-3 py-2 text-sm text-gray-300" v-html="link.label" />
                        </template>
                    </nav>
                </div>

                <div v-else class="text-center py-20 border rounded-xl">
                    <ShoppingCart class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                    <h3 class="text-xl font-bold mb-2 text-title font-title">No orders yet</h3>
                    <p class="text-gray-500 mb-6">When you place orders, they'll appear here.</p>
                    <Link href="/products" class="dmart-btn dmart-btn-primary">Start Shopping</Link>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>
