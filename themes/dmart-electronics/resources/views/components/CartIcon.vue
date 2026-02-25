<script setup lang="ts">
import { onMounted } from 'vue';
import { ShoppingBag } from 'lucide-vue-next';
import { useCart } from '@/composables/useCart';

const { itemCount, fetchCart } = useCart();

// Hydrate cart from server session on mount so items survive page refresh
onMounted(() => {
    fetchCart();
});
</script>

<template>
    <div class="relative">
        <ShoppingBag class="w-5 h-5 text-gray-700" />
        <span
            v-if="itemCount > 0"
            class="absolute -top-2 -right-2 w-5 h-5 rounded-full text-white text-[10px] flex items-center justify-center font-bold bg-theme-1"
        >{{ itemCount > 99 ? '99+' : itemCount }}</span>
    </div>
</template>
