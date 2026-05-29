<script setup lang="ts">
/**
 * cartxis-default theme override for ProductsGridBlock.
 * Reuses the shared ProductCard component for consistent storefront polish.
 */
import { ref } from 'vue'
import { useProductsGrid, type GridProduct } from '@/composables/useProductsGrid'
import { useThemeSettings } from '@/composables/useThemeSettings'
import ProductCard from '../resources/views/components/ProductCard.vue'
import QuickViewModal from '../resources/views/components/QuickViewModal.vue'
import ThemePlaceholderIcon from '../resources/views/components/ThemePlaceholderIcon.vue'
import ProductSkeleton from '../resources/views/components/ProductSkeleton.vue'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const {
    products, loading, colsClass,
} = useProductsGrid(props.settings, props.editorMode)

const { quickViewEnabled } = useThemeSettings()
const quickViewSlug = ref<string | null>(null)

function mapProduct(product: GridProduct) {
    return {
        id: product.id,
        name: product.name,
        slug: product.slug,
        sku: '',
        type: 'simple',
        price: product.price,
        special_price: null,
        image: product.thumbnail,
        rating: 0,
        reviews_count: 0,
        stock_quantity: 1,
        in_stock: true,
        has_configurable_attributes: false,
        badges: [],
    }
}

const onQuickView = (slug: string) => {
    if (quickViewEnabled.value) {
        quickViewSlug.value = slug
    }
}
</script>

<template>
    <div class="w-full">
        <div v-if="loading" class="grid gap-6" :class="colsClass(settings.columns)">
            <ProductSkeleton v-for="n in (settings.limit ?? 8)" :key="n" />
        </div>

        <div v-else-if="products.length" class="grid gap-6" :class="colsClass(settings.columns)">
            <ProductCard
                v-for="product in products"
                :key="product.id"
                :product="mapProduct(product)"
                compact
                @quick-view="onQuickView"
            />
        </div>

        <div v-else-if="editorMode" class="grid gap-6" :class="colsClass(settings.columns)">
            <div
                v-for="n in (settings.limit ?? 4)"
                :key="n"
                class="rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-2 py-12 text-slate-400"
            >
                <ThemePlaceholderIcon />
                <span class="text-xs">Product {{ n }}</span>
            </div>
        </div>

        <QuickViewModal
            :is-open="!!quickViewSlug"
            :product-slug="quickViewSlug"
            @close="quickViewSlug = null"
        />
    </div>
</template>
