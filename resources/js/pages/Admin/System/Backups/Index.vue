<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Download, Trash2, Plus, Database, HardDrive, FileArchive } from 'lucide-vue-next';
import AdminLayout from '@/layouts/AdminLayout.vue';
import * as backupRoutes from '@/routes/admin/system/backups/index';

interface Backup {
    path: string;
    date: string;
    size: string;
    disk: string;
}

interface Props {
    backups: Backup[];
}

const props = defineProps<Props>();

const isLoading = ref(false);
const showCreateModal = ref(false);
const backupOption = ref('only-db'); // Default to DB only as it's faster and common

const createBackup = () => {
    isLoading.value = true;
    showCreateModal.value = false;

    router.post(backupRoutes.create().url, {
        option: backupOption.value
    }, {
        onFinish: () => {
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        }
    });
};

const deleteBackup = (backup: Backup) => {
    if (!confirm('Are you sure you want to delete this backup?')) return;

    router.delete(backupRoutes.destroy().url, {
        data: {
            disk: backup.disk,
            path: backup.path
        }
    });
};

const downloadBackup = (backup: Backup) => {
    // We use window.location for download to avoid Inertia handling the file stream as a page visit
    const url = backupRoutes.download({
        query: {
            disk: backup.disk,
            path: backup.path
        }
    }).url;
    window.location.href = url;
};
</script>

<template>
    <Head title="System Backups" />

    <AdminLayout title="System Backups">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between shadow-sm z-10">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <Database class="w-5 h-5 text-blue-600" />
                        System Backups
                    </h1>
                </div>
                <button
                    @click="showCreateModal = true"
                    :disabled="isLoading"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium shadow-sm"
                >
                    <Plus v-if="!isLoading" class="w-4 h-4" />
                    <svg v-else class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ isLoading ? 'Creating...' : 'Create Backup' }}</span>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 p-6 overflow-auto bg-gray-50 dark:bg-gray-900">
                
                <!-- Progress Banner -->
                <div v-if="isLoading" class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                Creating backup...
                            </p>
                            <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                Please wait while your backup is being created. This may take a few moments depending on the size of your data.
                            </p>
                        </div>
                    </div>
                    <!-- Progress bar -->
                    <div class="mt-3 bg-blue-200 dark:bg-blue-800 rounded-full h-2 overflow-hidden">
                        <div class="bg-blue-600 dark:bg-blue-400 h-full rounded-full animate-pulse" style="width: 100%"></div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">File Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Size</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="(backup, index) in backups" :key="index" class="group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                            <FileArchive class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ backup.path.split('/').pop() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ backup.size }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ backup.date }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            @click="downloadBackup(backup)"
                                            class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                            title="Download"
                                        >
                                            <Download class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click="deleteBackup(backup)"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                            title="Delete"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="backups.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                        <HardDrive class="w-12 h-12 mb-4 opacity-50" />
                                        <p class="text-lg font-medium">No backups found</p>
                                        <p class="text-sm mt-1">Create your first backup to keep your data safe.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Backup Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showCreateModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500/75 dark:bg-gray-900/75 p-4"
                    @click="showCreateModal = false"
                >
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="scale-95 opacity-0"
                        enter-to-class="scale-100 opacity-100"
                        leave-active-class="transition duration-200 ease-in"
                        leave-from-class="scale-100 opacity-100"
                        leave-to-class="scale-95 opacity-0"
                    >
                        <div
                            v-if="showCreateModal"
                            class="relative w-full max-w-lg rounded-lg bg-white shadow-xl dark:bg-gray-800"
                            @click.stop
                        >
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                    Create New Backup
                                </h3>
                            </div>

                            <!-- Content -->
                            <div class="px-6 py-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Choose what you want to backup. Creating a full backup may take several minutes.
                                </p>
                                
                                <div class="space-y-3">
                                    <div class="flex items-center">
                                        <input id="only-db" name="backup-option" type="radio" value="only-db" v-model="backupOption" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                                        <label for="only-db" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Database Only (Fastest)
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="only-files" name="backup-option" type="radio" value="only-files" v-model="backupOption" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                                        <label for="only-files" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Files Only (Media & Uploads)
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="full" name="backup-option" type="radio" value="" v-model="backupOption" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                                        <label for="full" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Full Backup (Database + Files)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                                <button 
                                    type="button" 
                                    @click="showCreateModal = false" 
                                    class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="button" 
                                    @click="createBackup" 
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                >
                                    Create
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>
