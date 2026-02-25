<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import ThemeLayout from '@/../../themes/cartxis-default/resources/views/layouts/ThemeLayout.vue'

interface TrackedOrder {
  id: number
  order_number: string
  status: string
  payment_status: string
  total: number
  created_at: string
  items_count: number
  shipping_address: {
    first_name: string
    last_name: string
    city: string
    state: string
    postal_code: string
    country: string
  } | null
}

const props = defineProps<{
  lookup: { order_number: string }
  trackedOrder: TrackedOrder | null
  error?: string
}>()

const orderNumber = ref(props.lookup?.order_number ?? '')

const formatPrice = (amount: number) => `₹${Number(amount || 0).toFixed(2)}`

const submit = () => {
  router.post('/checkout/track-order', {
    order_number: orderNumber.value,
  })
}
</script>

<template>
  <ThemeLayout>
    <Head title="Track Guest Order" />

    <div class="container mx-auto px-4 py-8 max-w-2xl">
      <h1 class="text-2xl font-bold mb-2">Track Guest Order</h1>
      <p class="text-gray-600 mb-6">Enter your order ID to check order status.</p>

      <div class="bg-white rounded-lg shadow p-5">
        <label class="block text-sm font-medium mb-2">Order ID</label>
        <div class="flex gap-2">
          <input v-model="orderNumber" type="text" class="flex-1 border rounded px-3 py-2" placeholder="ORD-..." />
          <button @click="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Track</button>
        </div>
        <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>
      </div>

      <div v-if="trackedOrder" class="bg-white rounded-lg shadow p-5 mt-6">
        <h2 class="text-lg font-semibold mb-3">Order #{{ trackedOrder.order_number }}</h2>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between"><span class="text-gray-600">Status</span><span class="font-medium">{{ trackedOrder.status }}</span></div>
          <div class="flex justify-between"><span class="text-gray-600">Payment Status</span><span>{{ trackedOrder.payment_status }}</span></div>
          <div class="flex justify-between"><span class="text-gray-600">Placed On</span><span>{{ trackedOrder.created_at }}</span></div>
          <div class="flex justify-between"><span class="text-gray-600">Items</span><span>{{ trackedOrder.items_count }}</span></div>
          <div class="flex justify-between"><span class="text-gray-600">Total</span><span class="font-semibold">{{ formatPrice(trackedOrder.total) }}</span></div>
        </div>
      </div>
    </div>
  </ThemeLayout>
</template>
