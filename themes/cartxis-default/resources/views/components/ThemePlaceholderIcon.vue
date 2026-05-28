<script setup lang="ts">
withDefaults(defineProps<{
    variant?: 'product' | 'category'
}>(), {
    variant: 'product',
})
</script>

<template>
    <svg
        class="w-12 h-12 text-gray-300"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.5"
        aria-hidden="true"
    >
        <template v-if="variant === 'category'">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 7l1 12h14l1-12M4 7l2-3h12l2 3" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 11h6" />
        </template>
        <template v-else>
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </template>
    </svg>
</template>
