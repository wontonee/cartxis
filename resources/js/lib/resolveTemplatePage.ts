import type { DefineComponent } from 'vue'

type ViteManifestEntry = {
    file: string
    src?: string
}

let manifestCache: Record<string, ViteManifestEntry> | null = null

function normalizeTemplateName(name: string): string {
    if (! name.startsWith('themes/')) {
        return name
    }

    return name
        .replace(/^themes\//, 'templates/')
        .replace(/^templates\/([^/]+)\/resources\/views\//, 'templates/$1/')
}

function templateNotFoundError(name: string): Error {
    return new Error(
        `Template page not found: ${name}. ` +
        'Run npm run build after installing a theme, then hard-refresh the storefront.',
    )
}

function parseTemplateName(name: string): { themeSlug: string; componentPath: string; suffix: string } {
    const parts = name.split('/')
    const themeSlug = parts[1]
    const componentPath = parts.slice(2).join('/')
    const suffix = `/resources/views/${componentPath}.vue`

    return { themeSlug, componentPath, suffix }
}

function isValidTemplateVuePath(key: string): boolean
{
    return ! key.includes('/__MACOSX/')
        && ! key.split('/').some((part) => part.startsWith('._'))
}

function findGlobEntry(
    themeSlug: string,
    suffix: string,
): [string, () => Promise<DefineComponent>] | null {
    const pages = import.meta.glob<DefineComponent>(
        '../../../templates/storefront/**/resources/views/**/*.vue',
    )

    const entry = Object.entries(pages).find(
        ([key]) => isValidTemplateVuePath(key) && key.includes(`/${themeSlug}/`) && key.endsWith(suffix),
    )

    return entry ?? null
}

async function loadManifest(): Promise<Record<string, ViteManifestEntry>> {
    if (manifestCache) {
        return manifestCache
    }

    const response = await fetch(`/build/manifest.json?v=${Date.now()}`, {
        credentials: 'same-origin',
        cache: 'no-store',
    })

    if (! response.ok) {
        throw templateNotFoundError('manifest')
    }

    manifestCache = await response.json()

    return manifestCache!
}

async function resolveFromManifest(themeSlug: string, suffix: string): Promise<DefineComponent> {
    const manifest = await loadManifest()
    const manifestKey = Object.keys(manifest).find(
        (key) =>
            isValidTemplateVuePath(key)
            && key.startsWith('templates/storefront/')
            && key.includes(`/${themeSlug}/`)
            && key.endsWith(suffix),
    )

    if (! manifestKey) {
        throw templateNotFoundError(`templates/${themeSlug}${suffix.replace('/resources/views', '/pages')}`)
    }

    const module = await import(/* @vite-ignore */ `/build/${manifest[manifestKey].file}`)

    return module.default as DefineComponent
}

export async function resolveTemplatePage(name: string): Promise<DefineComponent> {
    const normalized = normalizeTemplateName(name)
    const { themeSlug, suffix } = parseTemplateName(normalized)

    const globEntry = findGlobEntry(themeSlug, suffix)

    if (globEntry) {
        return globEntry[1]()
    }

    return resolveFromManifest(themeSlug, suffix)
}

export function clearTemplateManifestCache(): void {
    manifestCache = null
}
