<script setup lang="ts">
import { computed } from 'vue'
import { Settings, Power, Check, Info, CreditCard, Wallet, Banknote, Globe } from 'lucide-vue-next'

// Local interface definition to avoid import issues if the composable path is incorrect
interface PaymentMethod {
  id: number
  code: string
  name: string
  description: string
  type: string
  is_active: boolean
  is_default: boolean
  sort_order: number
  instructions: string
  configuration: Record<string, any>
  created_at: string
  updated_at: string
}

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
    return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 border border-gray-200 dark:border-gray-600'
  }
  return 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800'
})

const defaultBadgeClass = computed(() => {
   return 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800' 
})

const iconComponent = computed(() => {
  const code = props.paymentMethod.code.toLowerCase()
  if (code.includes('card') || code.includes('stripe')) return CreditCard
  if (code.includes('paypal')) return Wallet
  if (code.includes('bank') || code.includes('transfer')) return Banknote
  if (code.includes('cod')) return Banknote
  return Globe
})
</script>

<template>
  <div class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:border-blue-100 dark:hover:border-gray-600 transition-all duration-300 flex flex-col h-full relative overflow-hidden">
    
    <!-- Active Indicator Stripe -->
    <div 
      class="absolute top-0 left-0 w-1 h-full transition-colors duration-300"
      :class="paymentMethod.is_active ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700'"
    ></div>

    <div class="p-6 flex-1 flex flex-col">
      <!-- Header Section -->
      <div class="flex justify-between items-start mb-4 pl-3">
        <div class="flex items-center space-x-3">
          <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 group-hover:bg-blue-50 group-hover:text-blue-600 dark:group-hover:bg-blue-900/20 dark:group-hover:text-blue-400 transition-colors">
            <component :is="iconComponent" class="w-6 h-6" />
          </div>
          <div>
            <h3 class="font-bold text-gray-900 dark:text-white text-lg leading-tight">{{ paymentMethod.name }}</h3>
            <span class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5 block">{{ paymentMethod.code }}</span>
          </div>
        </div>
      </div>

      <!-- Description -->
      <p class="text-sm text-gray-600 dark:text-gray-400 pl-3 mb-6 flex-1 text-pretty group-hover:text-gray-900 dark:group-hover:text-gray-300 transition-colors">
        {{ paymentMethod.description || 'No description provided.' }}
      </p>

      <!-- Status Badges -->
      <div class="flex flex-wrap gap-2 pl-3 mb-6">
        <span 
          :class="['px-2.5 py-1 text-xs font-semibold rounded-full flex items-center space-x-1.5', statusBadgeClass]"
        >
          <span class="w-1.5 h-1.5 rounded-full" :class="paymentMethod.is_active ? 'bg-green-500' : 'bg-gray-400'"></span>
          <span>{{ paymentMethod.is_active ? 'Active' : 'Inactive' }}</span>
        </span>
        
        <span 
          v-if="paymentMethod.is_default" 
          :class="['px-2.5 py-1 text-xs font-semibold rounded-full flex items-center space-x-1.5', defaultBadgeClass]"
        >
          <Check class="w-3 h-3" />
          <span>Default Method</span>
        </span>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-3 pl-3 pt-4 border-t border-gray-50 dark:border-gray-700/50 mt-auto">
        <button
          @click="$emit('configure')"
          :disabled="processing"
          class="flex-1 flex items-center justify-center px-4 py-2 bg-blue-600 dark:bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 dark:hover:bg-blue-500 transition-all focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 dark:focus:ring-blue-500 disabled:opacity-50 shadow-sm"
        >
          <Settings class="w-4 h-4 mr-2" />
          Configure
        </button>
        
        <button
          @click="$emit('toggle')"
          :disabled="processing"
          class="p-2 rounded-lg border transition-all disabled:opacity-50 hover:shadow-sm focus:ring-2 focus:ring-offset-2"
          :class="[
            paymentMethod.is_active
              ? 'border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-red-50 hover:text-red-600 hover:border-red-100 dark:hover:bg-red-900/20 dark:hover:text-red-400 dark:hover:border-red-900/30'
              : 'border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/40'
          ]"
          :title="paymentMethod.is_active ? 'Deactivate' : 'Activate'"
        >
          <Power class="w-4 h-4" />
        </button>

        <button
          v-if="!paymentMethod.is_default && paymentMethod.is_active"
          @click="$emit('setDefault')"
          :disabled="processing"
          class="p-2 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-100 dark:hover:bg-blue-900/20 dark:hover:text-blue-400 dark:hover:border-blue-900/30 transition-all disabled:opacity-50 hover:shadow-sm"
          title="Set as Default"
        >
          <Check class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>
