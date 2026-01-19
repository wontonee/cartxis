<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col sm:flex-row gap-4 animate-pulse">
        <!-- Product Image Skeleton -->
        <div class="flex-shrink-0">
            <div class="w-24 h-24 bg-gray-200 rounded-md"></div>
        </div>

        <!-- Product Info Skeleton -->
        <div class="flex-1 space-y-3">
            <!-- Title -->
            <div class="h-6 bg-gray-200 rounded w-3/4"></div>
            
            <!-- Attributes -->
            <div class="flex gap-3">
                <div class="h-4 bg-gray-200 rounded w-20"></div>
                <div class="h-4 bg-gray-200 rounded w-24"></div>
            </div>

            <!-- Price -->
            <div class="h-7 bg-gray-200 rounded w-24"></div>

            <!-- Quantity & Remove -->
            <div class="flex items-center gap-4">
                <div class="h-9 bg-gray-200 rounded w-32"></div>
                <div class="h-5 bg-gray-200 rounded w-16"></div>
            </div>
        </div>

        <!-- Item Subtotal Skeleton -->
        <div class="text-right space-y-2">
            <div class="h-4 bg-gray-200 rounded w-16 ml-auto"></div>
            <div class="h-7 bg-gray-200 rounded w-20 ml-auto"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
// No props needed for skeleton
</script>
