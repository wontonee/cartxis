<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeLayout from '@/../../themes/vortex-default/resources/views/layouts/ThemeLayout.vue';

interface OrderItem {
  id: number;
  order_number: string;
  status: string;
  total: number;
  created_at: string;
  items_count: number;
}

interface Props {
  orders: {
    data: OrderItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

const props = defineProps<Props>();

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    shipped: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    minimumFractionDigits: 2,
  }).format(price);
};
</script>

<template>
  <ThemeLayout>
    <Head title="My Orders" />

    <div class="container mx-auto px-4 py-8">
      <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold mb-2">My Orders</h1>
          <p class="text-gray-600">View and track your orders</p>
        </div>

        <!-- Orders List -->
        <div v-if="orders.data.length > 0" class="space-y-4">
          <div
            v-for="order in orders.data"
            :key="order.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
          >
            <div class="p-6">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Order Info -->
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-lg font-semibold">Order #{{ order.order_number }}</h3>
                    <span
                      :class="[
                        'px-3 py-1 rounded-full text-xs font-medium',
                        getStatusColor(order.status)
                      ]"
                    >
                      {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                    </span>
                  </div>
                  <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <span>{{ order.created_at }}</span>
                    <span>{{ order.items_count }} {{ order.items_count === 1 ? 'item' : 'items' }}</span>
                    <span class="font-semibold text-gray-900">{{ formatPrice(order.total) }}</span>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                  <Link
                    :href="`/account/orders/${order.id}`"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                  >
                    View Details
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <svg
            class="w-16 h-16 mx-auto mb-4 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
            />
          </svg>
          <h3 class="text-xl font-semibold mb-2">No orders yet</h3>
          <p class="text-gray-600 mb-6">Start shopping to see your orders here</p>
          <Link
            href="/products"
            class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Continue Shopping
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="orders.last_page > 1" class="mt-8 flex justify-center gap-2">
          <Link
            v-for="page in orders.last_page"
            :key="page"
            :href="`/account/orders?page=${page}`"
            :class="[
              'px-4 py-2 rounded-lg border',
              page === orders.current_page
                ? 'bg-blue-600 text-white border-blue-600'
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </Link>
        </div>
      </div>
    </div>
  </ThemeLayout>
</template>
