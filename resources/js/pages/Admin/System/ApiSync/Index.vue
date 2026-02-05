<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { RefreshCw, Link2, Wifi, WifiOff, Smartphone } from 'lucide-vue-next';

interface ApiSyncStatus {
  connected: boolean;
  sync_enabled: boolean;
  last_sync_at: string;
  last_status: string;
  last_message: string;
  last_checked_at: string;
}

const props = defineProps<{
  status: ApiSyncStatus;
}>();

const page = usePage();

const liveStatus = ref<ApiSyncStatus>({ ...props.status });
const pollIntervalMs = 10000;
let pollTimer: ReturnType<typeof setInterval> | null = null;

const statusBadge = computed(() => {
  if (liveStatus.value.last_status === 'success') return 'success';
  if (liveStatus.value.last_status === 'failed') return 'destructive';
  return 'secondary';
});

const refreshSync = () => {
  router.post('/admin/system/api-sync/refresh', {}, {
    preserveScroll: true,
    onSuccess: () => refreshStatus(),
  });
};

const refreshStatus = async () => {
  try {
    const response = await fetch('/admin/system/api-sync/status', {
      headers: {
        Accept: 'application/json',
      },
    });

    if (!response.ok) return;

    const data = await response.json();
    liveStatus.value = data as ApiSyncStatus;
  } catch (error) {
    // silent fail for polling
  }
};

onMounted(() => {
  refreshStatus();
  pollTimer = setInterval(refreshStatus, pollIntervalMs);
});

onUnmounted(() => {
  if (pollTimer) {
    clearInterval(pollTimer);
  }
});
</script>

<template>
  <AdminLayout title="API Sync">
    <Head title="API Sync" />

    <div class="flex flex-col h-full">
      <!-- Header -->
      <div class="px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between shadow-sm z-10">
        <div>
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <Smartphone class="w-5 h-5 text-blue-600" />
            API Sync
          </h1>
        </div>
        <Button @click="refreshSync" class="gap-2 bg-blue-600 hover:bg-blue-700 text-white shadow-sm border-0">
          <RefreshCw class="h-4 w-4" />
          Refresh / Sync Now
        </Button>
      </div>

      <!-- Content -->
      <div class="flex-1 p-6 overflow-auto bg-gray-50 dark:bg-gray-900">
        <div class="max-w-5xl space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Card class="bg-white dark:bg-gray-900/70 border-gray-200 dark:border-gray-800 shadow-sm">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Link2 class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                  Connection Status
                </CardTitle>
                <CardDescription>Indicates if the mobile app is connected.</CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <Badge :variant="liveStatus.connected ? 'success' : 'destructive'">
                      {{ liveStatus.connected ? 'Connected' : 'Disconnected' }}
                    </Badge>
                    <span class="text-sm text-gray-500 dark:text-gray-300">
                      Last checked: {{ liveStatus.last_checked_at || 'Never' }}
                    </span>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card class="bg-white dark:bg-gray-900/70 border-gray-200 dark:border-gray-800 shadow-sm">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Wifi class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                  Sync Status
                </CardTitle>
                <CardDescription>Controls whether automatic sync is enabled.</CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <Badge :variant="liveStatus.sync_enabled ? 'success' : 'secondary'">
                      {{ liveStatus.sync_enabled ? 'Enabled' : 'Disabled' }}
                    </Badge>
                    <span class="text-sm text-gray-500 dark:text-gray-300">
                      Last sync: {{ liveStatus.last_sync_at || 'Never' }}
                    </span>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <Card class="bg-white dark:bg-gray-900/70 border-gray-200 dark:border-gray-800 shadow-sm">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <WifiOff class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                Last Sync Result
              </CardTitle>
              <CardDescription>Latest API sync outcome and message.</CardDescription>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-300">Status:</span>
                <Badge :variant="statusBadge">{{ liveStatus.last_status }}</Badge>
              </div>
              <div class="text-sm text-gray-700 dark:text-gray-200">
                {{ liveStatus.last_message || 'No sync message available.' }}
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
