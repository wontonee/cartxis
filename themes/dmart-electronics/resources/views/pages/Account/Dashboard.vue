<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../components/Breadcrumb.vue';
import { useCurrency } from '@/composables/useCurrency';
import { Package, MapPin, User, Heart, ShoppingCart, ChevronRight, LogOut } from 'lucide-vue-next';

interface RecentOrder {
    id: number; order_number: string; status: string;
    total: number; items_count: number; created_at: string;
}

interface Props {
    recentOrders?: RecentOrder[];
    ordersCount?: number;
    wishlistCount?: number;
    theme: { name: string; slug: string };
    siteConfig: { name: string };
    auth: { user: { name: string; email: string } };
}

const props = defineProps<Props>();
const { formatPrice } = useCurrency();

const menuItems = [
    { label: 'My Orders', href: '/account/orders', icon: Package, desc: `${props.ordersCount || 0} orders` },
    { label: 'My Addresses', href: '/account/addresses', icon: MapPin, desc: 'Manage shipping addresses' },
    { label: 'My Profile', href: '/account/profile', icon: User, desc: 'Edit personal info' },
    { label: 'My Wishlist', href: '/account/wishlist', icon: Heart, desc: `${props.wishlistCount || 0} items` },
];

const statusColor = (status: string) => {
    const map: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-700', processing: 'bg-blue-100 text-blue-700',
        shipped: 'bg-purple-100 text-purple-700', delivered: 'bg-green-100 text-green-700',
        cancelled: 'bg-red-100 text-red-700',
    };
    return map[status.toLowerCase()] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <ThemeLayout>
        <Head title="My Account" />
        <Breadcrumb :items="[{ label: 'My Account' }]" />

        <section class="py-8 lg:py-12">
            <div class="dmart-container">
                <!-- Welcome -->
                <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-extrabold text-title font-title">
                            Welcome, {{ auth.user.name }}
                        </h1>
                        <p class="text-gray-500 mt-1">{{ auth.user.email }}</p>
                    </div>
                    <Link href="/logout" method="post" as="button" class="dmart-btn dmart-btn-outline text-sm self-start sm:self-auto">
                        <LogOut class="w-4 h-4" /> Logout
                    </Link>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
                    <Link
                        v-for="item in menuItems"
                        :key="item.label"
                        :href="item.href"
                        class="flex items-center gap-4 p-5 border rounded-2xl hover:border-theme-1 hover:shadow-md transition-all group"
                    >
                        <div class="w-11 h-11 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform bg-theme-1 text-white">
                            <component :is="item.icon" class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="font-bold text-sm text-title font-title">{{ item.label }}</div>
                            <div class="text-xs text-gray-400">{{ item.desc }}</div>
                        </div>
                    </Link>
                </div>

                <!-- Recent Orders -->
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-lg font-bold text-title font-title">Recent Orders</h2>
                        <Link href="/account/orders" class="text-sm font-semibold flex items-center gap-1 text-theme-1">
                            View All <ChevronRight class="w-4 h-4" />
                        </Link>
                    </div>

                    <div v-if="recentOrders?.length" class="border rounded-xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left py-3 px-4 font-semibold">Order #</th>
                                    <th class="text-left py-3 px-4 font-semibold hidden sm:table-cell">Date</th>
                                    <th class="text-center py-3 px-4 font-semibold">Status</th>
                                    <th class="text-right py-3 px-4 font-semibold">Total</th>
                                    <th class="w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in recentOrders" :key="order.id" class="border-t hover:bg-gray-50">
                                    <td class="py-3 px-4 font-semibold text-theme-1">#{{ order.order_number }}</td>
                                    <td class="py-3 px-4 text-gray-500 hidden sm:table-cell">{{ order.created_at }}</td>
                                    <td class="py-3 px-4 text-center"><span class="px-2.5 py-1 rounded-full text-xs font-bold" :class="statusColor(order.status)">{{ order.status }}</span></td>
                                    <td class="py-3 px-4 text-right font-semibold">{{ formatPrice(order.total) }}</td>
                                    <td class="py-3 px-2"><Link :href="`/account/orders/${order.id}`"><ChevronRight class="w-4 h-4 text-gray-400" /></Link></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12 border rounded-xl">
                        <ShoppingCart class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                        <p class="text-gray-500">No orders yet.</p>
                        <Link href="/products" class="dmart-btn dmart-btn-primary mt-4">Start Shopping</Link>
                    </div>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>
