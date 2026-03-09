import { ref, onMounted } from 'vue'
import axios from 'axios'

export interface GridCategory {
    id: number
    name: string
    slug: string
    image_url: string | null
    products_count: number
}

/**
 * Shared logic composable for CategoriesGrid blocks.
 * All themes share the same data-fetching logic.
 * Only the presentation (template) differs per theme.
 */
export function useCategoriesGrid(settings: Record<string, unknown>) {
    const categories = ref<GridCategory[]>([])
    const loading    = ref(false)
    const error      = ref<string | null>(null)

    onMounted(async () => {
        loading.value = true
        try {
            const res = await axios.get('/api/shop/categories', {
                params: { limit: settings.limit ?? 8 },
            })
            categories.value = res.data.data ?? []
        } catch (e) {
            categories.value = []
            error.value = 'Failed to load categories'
            if (import.meta.env.DEV) console.error('[useCategoriesGrid]', e)
        } finally {
            loading.value = false
        }
    })

    const colsClass = (cols: unknown) => {
        const n = Number(cols) || 4
        return `grid-cols-${Math.min(n, 6)}`
    }

    return { categories, loading, error, colsClass }
}
