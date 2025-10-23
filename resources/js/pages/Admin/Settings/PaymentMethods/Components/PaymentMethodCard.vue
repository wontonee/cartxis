<script setup lang="ts">
import { computed } from 'vue'
import type { PaymentMethod } from '@/composables/usePaymentMethods'

interface Props {
  paymentMethod: PaymentMethod
  processing?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  processing: false,
})

const emit = defineEmits<{
  toggle: []
  configure: []
  setDefault: []
}>()

const statusBadgeClass = computed(() => {
  if (!props.paymentMethod.is_active) {
    return 'bg-gray-100 text-gray-800'
  }
  return 'bg-green-100 text-green-800'
})

const statusText = computed(() => {
  return props.paymentMethod.is_active ? 'Active' : 'Inactive'
})

const defaultBadge = computed(() => {
  return props.paymentMethod.is_default ? 'Default' : null
})
</script>

<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
    <!-- Header -->
    <div class="p-4 border-b border-gray-100">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-2">
            <h3 class="text-lg font-semibold text-gray-900">{{ paymentMethod.name }}</h3>
            <span :class="['px-2 py-1 text-xs font-medium rounded-full', statusBadgeClass]">
              {{ statusText }}
            </span>
            <span v-if="defaultBadge" class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
              {{ defaultBadge }}
            </span>
          </div>
          <p class="text-sm text-gray-600">{{ paymentMethod.description }}</p>
        </div>
      </div>
    </div>

    <!-- Body -->
    <div class="p-4 space-y-3">
      <!-- Details -->
      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-gray-600">Type:</span>
          <span class="font-medium text-gray-900 uppercase">{{ paymentMethod.type }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-600">Code:</span>
          <span class="font-mono text-gray-900">{{ paymentMethod.code }}</span>
        </div>
        <div v-if="paymentMethod.instructions" class="flex justify-between">
          <span class="text-gray-600">Instructions:</span>
          <span class="text-gray-900 truncate">{{ paymentMethod.instructions }}</span>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex gap-2">
      <button
        @click="$emit('configure')"
        :disabled="processing"
        class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
      >
        Configure
      </button>
      <button
        @click="$emit('toggle')"
        :disabled="processing"
        :class="[
          'flex-1 px-3 py-2 text-sm font-medium rounded-lg transition-colors disabled:opacity-50',
          paymentMethod.is_active
            ? 'bg-red-100 text-red-700 hover:bg-red-200'
            : 'bg-green-100 text-green-700 hover:bg-green-200'
        ]"
      >
        {{ paymentMethod.is_active ? 'Disable' : 'Enable' }}
      </button>
      <button
        v-if="!paymentMethod.is_default"
        @click="$emit('setDefault')"
        :disabled="processing"
        class="flex-1 px-3 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50"
      >
        Set Default
      </button>
    </div>
  </div>
</template>
