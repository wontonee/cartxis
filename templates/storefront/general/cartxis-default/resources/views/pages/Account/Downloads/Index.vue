<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import ThemeLayout from '../../../layouts/ThemeLayout.vue'

interface Download {
  id: number
  title: string
  file_name: string | null
  order_number: string | null
  download_count: number
  max_downloads: number | null
  expires_at: string | null
  can_download: boolean
  download_url: string
}

defineProps<{ downloads: Download[] }>()
</script>

<template>
  <ThemeLayout>
    <Head title="My Downloads" />
    <div class="max-w-4xl mx-auto px-4 py-10">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">My Downloads</h1>
        <p class="mt-1 text-sm text-gray-600">Access digital files from your paid orders.</p>
      </div>

      <div v-if="downloads.length === 0" class="rounded-xl border border-gray-200 bg-white p-10 text-center text-gray-500">
        You do not have any downloads yet.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="item in downloads"
          :key="item.id"
          class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5"
        >
          <div>
            <p class="font-semibold text-gray-900">{{ item.title }}</p>
            <p class="text-sm text-gray-500">
              {{ item.file_name }}
              <span v-if="item.order_number"> · Order {{ item.order_number }}</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">
              Downloads: {{ item.download_count }}{{ item.max_downloads ? ` / ${item.max_downloads}` : '' }}
              <span v-if="item.expires_at"> · Expires {{ new Date(item.expires_at).toLocaleDateString() }}</span>
            </p>
          </div>
          <a
            v-if="item.can_download"
            :href="item.download_url"
            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500"
          >
            Download
          </a>
          <span v-else class="text-sm text-red-600">Unavailable</span>
        </div>
      </div>

      <div class="mt-8">
        <Link href="/account" class="text-sm text-blue-600 hover:underline">← Back to account</Link>
      </div>
    </div>
  </ThemeLayout>
</template>
