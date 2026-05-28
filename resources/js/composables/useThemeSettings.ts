import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

type ThemeSettings = Record<string, string | number | boolean | null | undefined>

function settingBool(value: unknown, defaultValue = true): boolean {
    if (value === undefined || value === null) return defaultValue
    if (typeof value === 'boolean') return value
    if (value === '0' || value === 0 || value === 'false') return false
    return Boolean(value)
}

export function useThemeSettings() {
    const page = usePage()

    const settings = computed<ThemeSettings>(() => {
        const theme = page.props.theme as { settings?: ThemeSettings } | null | undefined
        return theme?.settings ?? {}
    })

    const primary = computed(() => String(settings.value['colors.primary'] ?? '#2563eb'))
    const secondary = computed(() => String(settings.value['colors.secondary'] ?? '#1e40af'))
    const accent = computed(() => String(settings.value['colors.accent'] ?? '#0ea5e9'))
    const fontFamily = computed(() => String(settings.value['typography.font_family'] ?? 'Inter, sans-serif'))
    const containerWidth = computed(() => String(settings.value['layout.container_width'] ?? '1280px'))
    const stickyHeader = computed(() => settingBool(settings.value['features.sticky_header'], true))
    const backToTop = computed(() => settingBool(settings.value['features.back_to_top'], true))
    const showPlatformBranding = computed(() => settingBool(settings.value['features.show_platform_branding'], false))
    const quickViewEnabled = computed(() => settingBool(settings.value['features.quick_view'], true))
    const wishlistEnabled = computed(() => settingBool(settings.value['features.wishlist'], true))

    const cssVars = computed(() => ({
        '--theme-primary': primary.value,
        '--theme-secondary': secondary.value,
        '--theme-accent': accent.value,
        '--theme-font': fontFamily.value,
        '--theme-container': containerWidth.value,
    }))

    return {
        settings,
        primary,
        secondary,
        accent,
        fontFamily,
        containerWidth,
        stickyHeader,
        backToTop,
        showPlatformBranding,
        quickViewEnabled,
        wishlistEnabled,
        cssVars,
    }
}
