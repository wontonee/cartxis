<template>
    <div class="dmart-product-card animate-pulse">
        <div class="aspect-square bg-gray-200 rounded-t-lg"></div>
        <div class="p-4 space-y-3">
            <div class="h-3 bg-gray-200 rounded w-16"></div>
            <div class="h-4 bg-gray-200 rounded w-full"></div>
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="flex gap-1">
                <div v-for="i in 5" :key="i" class="w-3.5 h-3.5 bg-gray-200 rounded-full"></div>
            </div>
            <div class="h-5 bg-gray-200 rounded w-24"></div>
        </div>
    </div>
</template>
