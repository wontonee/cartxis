<template>
    <div class="flex items-center gap-1">
        <template v-for="star in 5" :key="star">
            <svg
                :class="[
                    'inline-block',
                    star <= rating ? 'text-yellow-400 fill-current' : 'text-gray-300',
                    sizeClass,
                    interactive ? 'cursor-pointer hover:scale-110 transition-transform' : ''
                ]"
                @click="interactive && $emit('update:rating', star)"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
        </template>
        <span v-if="showCount" class="text-sm text-gray-600 dark:text-gray-400 ml-1">
            ({{ count }})
        </span>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    rating: number;
    count?: number;
    showCount?: boolean;
    size?: 'sm' | 'md' | 'lg';
    interactive?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    rating: 0,
    count: 0,
    showCount: false,
    size: 'md',
    interactive: false
});

defineEmits<{
    'update:rating': [value: number];
}>();

const sizeClass = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'w-4 h-4';
        case 'lg':
            return 'w-6 h-6';
        default:
            return 'w-5 h-5';
    }
});
</script>
