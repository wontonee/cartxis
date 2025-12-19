<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';

const { itemCount, fetchCart } = useCart();

const hasItems = computed(() => itemCount.value > 0);

// Fetch cart on component mount
onMounted(() => {
    fetchCart();
});
</script>

<template>
    <Link
        href="/cart"
        class="relative flex items-center justify-center w-10 h-10 hover:bg-gray-100 rounded-lg transition-colors"
    >
        <!-- Cart Icon -->
        <svg
            class="w-6 h-6 text-gray-700"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
            />
        </svg>

        <!-- Item Count Badge -->
        <Transition name="bounce">
            <span
                v-if="hasItems"
                class="absolute -top-1 -right-1 flex items-center justify-center min-w-[20px] h-5 px-1 bg-red-500 text-white text-xs font-bold rounded-full"
                :key="itemCount"
            >
                {{ itemCount > 99 ? '99+' : itemCount }}
            </span>
        </Transition>
    </Link>
</template>

<style scoped>
.bounce-enter-active {
    animation: bounce-in 0.5s;
}

.bounce-leave-active {
    animation: bounce-out 0.3s;
}

@keyframes bounce-in {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.3);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes bounce-out {
    0% {
        transform: scale(1);
    }
    100% {
        transform: scale(0);
    }
}
</style>
