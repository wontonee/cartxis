<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import ThemeLayout from '@/../../themes/cartxis-default/resources/views/layouts/ThemeLayout.vue';

interface OrderItem {
  id: number;
  order_number: string;
  status: string;
  total: number;
  created_at: string;
  items_count: number;
}

interface Props {
  recentOrders: OrderItem[];
}

const props = defineProps<Props>();
const page = usePage();
const user = (page.props.auth as any)?.user;

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
    <Head title="My Account Dashboard" />

    <div class="container mx-auto px-4 py-8">
      <div class="max-w-7xl mx-auto">
        <!-- Welcome Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold mb-2">Welcome back, {{ user?.name || 'Customer' }}!</h1>
          <p class="text-gray-600">Manage your orders, profile, and account settings</p>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <!-- Total Orders -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Total Orders</p>
                <p class="text-2xl font-bold">{{ recentOrders.length }}</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Pending Orders -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Pending</p>
                <p class="text-2xl font-bold">
                  {{ recentOrders.filter(o => o.status === 'pending' || o.status === 'processing').length }}
                </p>
              </div>
              <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Delivered Orders -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Delivered</p>
                <p class="text-2xl font-bold">
                  {{ recentOrders.filter(o => o.status === 'delivered').length }}
                </p>
              </div>
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Account Status -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Account</p>
                <p class="text-sm font-semibold text-green-600">Active</p>
              </div>
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Recent Orders Section -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
              <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                  <h2 class="text-xl font-semibold">Recent Orders</h2>
                  <Link
                    href="/account/orders"
                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                  >
                    View All
                  </Link>
                </div>
              </div>

              <div v-if="recentOrders.length > 0" class="divide-y divide-gray-200">
                <div
                  v-for="order in recentOrders"
                  :key="order.id"
                  class="p-6 hover:bg-gray-50 transition-colors"
                >
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                      <span class="font-semibold">Order #{{ order.order_number }}</span>
                      <span
                        :class="[
                          'px-2 py-1 rounded-full text-xs font-medium',
                          getStatusColor(order.status)
                        ]"
                      >
                        {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                      </span>
                    </div>
                    <span class="text-sm text-gray-600">{{ order.created_at }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                      {{ order.items_count }} {{ order.items_count === 1 ? 'item' : 'items' }} • {{ formatPrice(order.total) }}
                    </div>
                    <div class="flex gap-2">
                      <Link
                        :href="`/account/orders/${order.id}`"
                        class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                      >
                        View Details
                      </Link>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">No orders yet</h3>
                <p class="text-gray-600 mb-4">Start shopping to see your orders here</p>
                <Link
                  href="/"
                  class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  Start Shopping
                </Link>
              </div>
            </div>
          </div>

          <!-- Quick Actions Sidebar -->
          <div class="space-y-6">
            <!-- Account Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
              <div class="space-y-3">
                <Link
                  href="/account/orders"
                  class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group"
                >
                  <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="font-medium">My Orders</div>
                    <div class="text-xs text-gray-600">Track & manage orders</div>
                  </div>
                </Link>

                <Link
                  href="/"
                  class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group"
                >
                  <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="font-medium">Continue Shopping</div>
                    <div class="text-xs text-gray-600">Browse products</div>
                  </div>
                </Link>
              </div>
            </div>

            <!-- Account Info Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold mb-4">Account Information</h3>
              <div class="space-y-3 text-sm">
                <div>
                  <p class="text-gray-600">Name</p>
                  <p class="font-medium">{{ user?.name || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-gray-600">Email</p>
                  <p class="font-medium">{{ user?.email || 'N/A' }}</p>
                </div>
                <div class="pt-3 border-t border-gray-200">
                  <Link
                    href="/account/profile"
                    class="text-blue-600 hover:text-blue-700 font-medium text-sm"
                  >
                    Edit Profile →
                  </Link>
                </div>
              </div>
            </div>

            <!-- Help & Support -->
            <div class="bg-blue-50 rounded-lg border border-blue-200 p-6">
              <h3 class="text-lg font-semibold mb-2 text-blue-900">Need Help?</h3>
              <p class="text-sm text-blue-800 mb-4">
                Our support team is here to assist you with any questions.
              </p>
              <a
                href="/contact"
                class="inline-block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
              >
                Contact Support
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </ThemeLayout>
</template>
