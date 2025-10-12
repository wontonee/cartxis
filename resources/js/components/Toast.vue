<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, XCircle, AlertTriangle, Info, X } from 'lucide-vue-next';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref<'success' | 'error' | 'warning' | 'info'>('success');

const showToast = (msg: string, toastType: typeof type.value) => {
  message.value = msg;
  type.value = toastType;
  show.value = true;
  
  // Auto hide after 5 seconds
  setTimeout(() => {
    show.value = false;
  }, 5000);
};

const hideToast = () => {
  show.value = false;
};

const checkFlashMessages = () => {
  const flash = page.props.flash as any;
  if (!flash) return;
  
  if (flash.success) {
    showToast(flash.success, 'success');
  } else if (flash.error) {
    showToast(flash.error, 'error');
  } else if (flash.warning) {
    showToast(flash.warning, 'warning');
  } else if (flash.info) {
    showToast(flash.info, 'info');
  }
};

// Check on mount (for page loads after redirects)
onMounted(() => {
  checkFlashMessages();
});

// Watch for changes in flash messages (for same-page operations)
watch(
  () => page.props.flash,
  () => {
    checkFlashMessages();
  },
  { deep: true }
);

const toastConfig = {
  success: {
    icon: CheckCircle,
    bgClass: 'bg-green-50 border-green-500',
    textClass: 'text-green-800',
    iconClass: 'text-green-500',
  },
  error: {
    icon: XCircle,
    bgClass: 'bg-red-50 border-red-500',
    textClass: 'text-red-800',
    iconClass: 'text-red-500',
  },
  warning: {
    icon: AlertTriangle,
    bgClass: 'bg-yellow-50 border-yellow-500',
    textClass: 'text-yellow-800',
    iconClass: 'text-yellow-500',
  },
  info: {
    icon: Info,
    bgClass: 'bg-blue-50 border-blue-500',
    textClass: 'text-blue-800',
    iconClass: 'text-blue-500',
  },
};

const config = computed(() => toastConfig[type.value]);
</script>

<template>
  <Transition
    enter-active-class="transform ease-out duration-300 transition"
    enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transition ease-in duration-100"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="show"
      class="fixed top-4 right-4 z-[9999] max-w-sm w-full shadow-lg rounded-lg pointer-events-auto border-l-4"
      :class="config.bgClass"
    >
      <div class="p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <component :is="config.icon" class="h-6 w-6" :class="config.iconClass" />
          </div>
          <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium" :class="config.textClass">
              {{ message }}
            </p>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button
              @click="hideToast"
              class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
              :class="config.textClass"
            >
              <span class="sr-only">Close</span>
              <X class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>
