<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useCurrency } from '@/composables/useCurrency';
import ThemeLayout from '@/../../themes/cartxis-default/resources/views/layouts/ThemeLayout.vue';

interface OrderItem {
  product_name: string;
  product_image?: string;
  quantity: number;
  price: number;
  total: number;
}

interface ShippingAddress {
  first_name: string;
  last_name: string;
  address_line1: string;
  address_line2?: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
  phone: string;
}

interface Order {
  id: number;
  order_number: string;
  status: string;
  subtotal: number;
  tax: number;
  shipping_cost: number;
  total: number;
  customer_email: string;
  customer_phone?: string;
  created_at: string;
  items: OrderItem[];
  shipping_address: ShippingAddress | null;
}

interface Props {
  order: Order;
}

defineProps<Props>();
const page = usePage();

const { formatPrice } = useCurrency();

const isAuthenticated = computed(() => {
  const auth = (page.props as any).auth;
  return !!(auth && auth.user);
});
</script>

<template>
  <ThemeLayout>
    <div class="container mx-auto px-4 py-8">
      <div class="max-w-3xl mx-auto">
        <!-- Success Header -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Confirmed!</h1>
          <p class="text-gray-600">Thank you for your purchase</p>
        </div>

        <!-- Order Details Card -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
          <div class="border-b pb-6 mb-6">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-sm text-gray-600">Order Number</p>
                <p class="text-xl font-bold text-gray-900">{{ order.order_number }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-600">Order Date</p>
                <p class="font-medium">{{ order.created_at }}</p>
              </div>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <p class="text-sm text-blue-800">
                <strong>Confirmation Email Sent</strong>
              </p>
              <p class="text-sm text-blue-700 mt-1">
                We've sent a confirmation email to <strong>{{ order.customer_email }}</strong> with your order details.
              </p>
            </div>
          </div>

          <!-- Order Items -->
          <div class="mb-6">
            <h2 class="text-lg font-bold mb-4">Order Items</h2>
            <div class="space-y-4">
              <div
                v-for="(item, index) in order.items"
                :key="index"
                class="flex items-center gap-4 pb-4 border-b last:border-b-0"
              >
                <img
                  v-if="item.product_image"
                  :src="item.product_image"
                  :alt="item.product_name"
                  class="w-20 h-20 object-cover rounded"
                />
                <div class="flex-1">
                  <p class="font-medium">{{ item.product_name }}</p>
                  <p class="text-sm text-gray-600">Quantity: {{ item.quantity }}</p>
                  <p class="text-sm text-gray-600">{{ formatPrice(item.price) }} each</p>
                </div>
                <div class="text-right">
                  <p class="font-bold">{{ formatPrice(item.total) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Shipping Address -->
          <div v-if="order.shipping_address" class="mb-6">
            <h2 class="text-lg font-bold mb-3">Shipping Address</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="font-medium">
                {{ order.shipping_address.first_name }} {{ order.shipping_address.last_name }}
              </p>
              <p class="text-sm text-gray-600">{{ order.shipping_address.address_line1 }}</p>
              <p v-if="order.shipping_address.address_line2" class="text-sm text-gray-600">
                {{ order.shipping_address.address_line2 }}
              </p>
              <p class="text-sm text-gray-600">
                {{ order.shipping_address.city }}, {{ order.shipping_address.state }} {{ order.shipping_address.postal_code }}
              </p>
              <p class="text-sm text-gray-600">{{ order.shipping_address.country }}</p>
              <p class="text-sm text-gray-600 mt-2">Phone: {{ order.shipping_address.phone }}</p>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="border-t pt-6">
            <h2 class="text-lg font-bold mb-4">Order Summary</h2>
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span>Subtotal</span>
                <span>{{ formatPrice(order.subtotal) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span>Shipping</span>
                <span>{{ order.shipping_cost === 0 ? 'FREE' : formatPrice(order.shipping_cost) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span>Tax</span>
                <span>{{ formatPrice(order.tax) }}</span>
              </div>
              <div class="border-t pt-2 flex justify-between text-lg font-bold">
                <span>Total</span>
                <span>{{ formatPrice(order.total) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4 justify-center">
          <a
            href="/products"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
          >
            Continue Shopping
          </a>
          <a
            v-if="isAuthenticated"
            href="/account/orders"
            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium"
          >
            View Orders
          </a>
        </div>

        <!-- What's Next -->
        <div class="mt-8 bg-gray-50 rounded-lg p-6">
          <h3 class="font-bold mb-3">What's Next?</h3>
          <ul class="space-y-2 text-sm text-gray-700">
            <li class="flex items-start">
              <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <span>You'll receive a confirmation email shortly</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <span>We'll send you tracking information once your order ships</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <span>You can track your order status in your account</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </ThemeLayout>
</template>
