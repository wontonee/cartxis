<script setup lang="ts">
const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

interface SocialLink { network: string; url: string }

const socialLinks = (props.settings.social_links as SocialLink[]) ?? []

const socialIcon = (network: string): string => {
    const icons: Record<string, string> = {
        twitter: 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z',
        linkedin: 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z',
        github: 'M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12',
        website: 'M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm7.931 9h-2.764a14.67 14.67 0 00-1.792-6.243A8.013 8.013 0 0119.931 11zM12.53 4.027c1.035 1.364 2.427 3.78 2.627 6.973H9.03c.139-2.596.994-5.028 2.451-6.974.172-.01.347-.026.519-.026.179 0 .354.016.53.027zm-3.842.7C7.704 6.618 7.136 8.762 7.030 11H4.069a8.013 8.013 0 015.619-6.273zM4.069 13h2.974c.136 2.379.665 4.478 1.556 6.27A8.01 8.01 0 014.069 13zm7.381 6.973C10.049 18.275 9.222 15.896 9.041 13h6.113c-.208 2.773-1.117 5.196-2.603 6.972-.182.012-.364.028-.551.028-.186 0-.367-.016-.55-.027zm4.011-.772c.955-1.794 1.538-3.901 1.691-6.201h2.957a8.005 8.005 0 01-4.648 6.201z',
    }
    return icons[network] ?? ''
}
</script>

<template>
    <div class="w-full">
        <div class="flex items-start gap-6">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                <img
                    v-if="settings.avatar_url"
                    :src="(settings.avatar_url as string)"
                    :alt="(settings.name as string)"
                    class="w-20 h-20 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700"
                />
                <div
                    v-else
                    class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold"
                >
                    {{ (settings.name as string)?.charAt(0)?.toUpperCase() ?? 'A' }}
                </div>
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ settings.name }}</h3>
                <p v-if="settings.role" class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-2">{{ settings.role }}</p>
                <p v-if="settings.bio" class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ settings.bio }}</p>

                <!-- Social Links -->
                <div v-if="socialLinks.length" class="mt-3 flex items-center gap-2">
                    <a
                        v-for="link in socialLinks"
                        :key="link.network"
                        :href="editorMode ? '#' : link.url"
                        :target="editorMode ? '_self' : '_blank'"
                        rel="noopener noreferrer"
                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded"
                        :title="link.network"
                    >
                        <svg
                            v-if="socialIcon(link.network)"
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                        >
                            <path :d="socialIcon(link.network)" />
                        </svg>
                        <span v-else class="text-xs font-medium">{{ link.network }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
