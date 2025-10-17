<template>
    <div class="flex items-center gap-1">
        <template v-for="star in 5" :key="star">
            <i
                :class="[
                    'ki-solid',
                    star <= rating ? 'ki-star text-warning' : 'ki-star text-gray-300',
                    sizeClass,
                    interactive ? 'cursor-pointer hover:scale-110 transition-transform' : ''
                ]"
                @click="interactive && $emit('update:rating', star)"
            ></i>
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
            return 'text-sm';
        case 'lg':
            return 'text-2xl';
        default:
            return 'text-base';
    }
});
</script>
