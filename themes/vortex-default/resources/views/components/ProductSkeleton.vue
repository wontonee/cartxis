<template>
    <div class="bg-white rounded-2xl shadow-md overflow-hidden animate-pulse">
        <!-- Image Skeleton -->
        <div class="aspect-square bg-gray-200"></div>

        <!-- Content Skeleton -->
        <div class="p-4">
            <!-- Brand -->
            <div class="h-3 bg-gray-200 rounded w-1/4 mb-2"></div>

            <!-- Title -->
            <div class="space-y-2 mb-3">
                <div class="h-4 bg-gray-200 rounded w-full"></div>
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            </div>

            <!-- Rating -->
            <div class="flex items-center gap-2 mb-3">
                <div class="h-4 bg-gray-200 rounded w-24"></div>
                <div class="h-3 bg-gray-200 rounded w-16"></div>
            </div>

            <!-- Price -->
            <div class="flex items-center justify-between mb-4">
                <div class="h-6 bg-gray-200 rounded w-20"></div>
                <div class="h-4 bg-gray-200 rounded w-16"></div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-2">
                <div class="flex-1 h-10 bg-gray-200 rounded-lg"></div>
                <div class="w-10 h-10 bg-gray-200 rounded-lg"></div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
// No props needed for skeleton
</script>
