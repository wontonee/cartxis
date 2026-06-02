import { readFileSync } from 'node:fs'
import { resolve as pathResolve } from 'node:path'
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

function parseTemplateName(name: string): { themeSlug: string; suffix: string } {
    const parts = name.split('/')
    const themeSlug = parts[1]
    const componentPath = parts.slice(2).join('/')
    const suffix = `/resources/views/${componentPath}.vue`

    return { themeSlug, suffix }
}

function loadManifestSync(): Record<string, ViteManifestEntry> {
    if (manifestCache) {
        return manifestCache
    }

    const manifestPath = pathResolve(process.cwd(), 'public/build/manifest.json')
    manifestCache = JSON.parse(readFileSync(manifestPath, 'utf8'))

    return manifestCache!
}

function isValidTemplateVuePath(key: string): boolean
{
    return ! key.includes('/__MACOSX/')
        && ! key.split('/').some((part) => part.startsWith('._'))
}

export async function resolveTemplatePage(name: string): Promise<DefineComponent> {
    const normalized = normalizeTemplateName(name)
    const { themeSlug, suffix } = parseTemplateName(normalized)

    const pages = import.meta.glob<DefineComponent>(
        '../../../templates/storefront/**/resources/views/**/*.vue',
    )

    const globEntry = Object.entries(pages).find(
        ([key]) => isValidTemplateVuePath(key) && key.includes(`/${themeSlug}/`) && key.endsWith(suffix),
    )

    if (globEntry) {
        return globEntry[1]()
    }

    const manifest = loadManifestSync()
    const manifestKey = Object.keys(manifest).find(
        (key) =>
            isValidTemplateVuePath(key)
            && key.startsWith('templates/storefront/')
            && key.includes(`/${themeSlug}/`)
            && key.endsWith(suffix),
    )

    if (! manifestKey) {
        throw templateNotFoundError(normalized)
    }

    const module = await import(/* @vite-ignore */ pathResolve(process.cwd(), 'public/build', manifest[manifestKey].file))

    return module.default as DefineComponent
}
