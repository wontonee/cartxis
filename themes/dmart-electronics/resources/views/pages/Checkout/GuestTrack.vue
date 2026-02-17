<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import ThemeLayout from '../../layouts/ThemeLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { PackageSearch, Search, Package } from 'lucide-vue-next'

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

    <section class="py-10 lg:py-16">
      <div class="max-w-2xl mx-auto px-4">
        <div class="text-center mb-6">
          <div class="w-14 h-14 rounded-full mx-auto mb-4 flex items-center justify-center bg-blue-50">
            <PackageSearch class="w-7 h-7 text-blue-600" />
          </div>
          <h1 class="text-2xl font-extrabold text-title font-title">Track Your Guest Order</h1>
          <p class="text-sm text-gray-500 mt-1">Enter your order ID to view current status.</p>
        </div>

        <div class="border rounded-xl p-5 bg-white shadow-sm">
          <label class="block text-sm font-medium mb-2 text-gray-700">Order ID</label>
          <div class="flex gap-2">
            <input
              v-model="orderNumber"
              type="text"
              class="flex-1 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="e.g. ORD-260213-257666"
            />
            <button @click="submit" class="dmart-btn dmart-btn-primary text-sm">
              <Search class="w-4 h-4" /> Find
            </button>
          </div>

          <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>
        </div>

        <div v-if="trackedOrder" class="mt-6 border rounded-xl overflow-hidden bg-white shadow-sm">
          <div class="px-5 py-4 border-b bg-gray-50 flex items-center justify-between gap-2">
            <div>
              <div class="text-xs text-gray-500">Order Number</div>
              <div class="text-sm font-bold text-theme-1">#{{ trackedOrder.order_number }}</div>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
              <Package class="w-3 h-3" /> {{ trackedOrder.status }}
            </span>
          </div>

          <div class="px-5 py-4 text-sm space-y-2">
            <div class="flex justify-between"><span class="text-gray-500">Placed On</span><span>{{ trackedOrder.created_at }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Payment Status</span><span>{{ trackedOrder.payment_status }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Items</span><span>{{ trackedOrder.items_count }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Total</span><span class="font-semibold">{{ formatPrice(trackedOrder.total) }}</span></div>
            <div v-if="trackedOrder.shipping_address" class="pt-2 border-t">
              <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Shipping City</p>
              <p>{{ trackedOrder.shipping_address.city }}, {{ trackedOrder.shipping_address.state }} {{ trackedOrder.shipping_address.postal_code }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </ThemeLayout>
</template>
