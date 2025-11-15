<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import FolderTreeItem from '@/components/Admin/Media/FolderTreeItem.vue';
import UploadDropzone from '@/components/Admin/Media/UploadDropzone.vue';
import * as mediaRoutes from '@/routes/admin/content/media/index';
import * as folderRoutes from '@/routes/admin/content/folders/index';
import type { MediaFile, MediaFolder, MediaStatistics, MediaFilters, PaginatedMedia, MediaViewMode } from '@/types/media';

// Simple debounce function
const debounce = <T extends (...args: any[]) => any>(
    func: T,
    wait: number
): ((...args: Parameters<T>) => void) => {
    let timeout: ReturnType<typeof setTimeout> | null = null;
    return (...args: Parameters<T>) => {
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
};
import { 
    Image, 
    Grid3x3, 
    List, 
    Search, 
    Upload, 
    FolderPlus,
    Filter,
    FileImage,
    FileVideo,
    File,
    X,
    Folder,
    FolderOpen,
    ChevronRight,
    ChevronDown,
    Home,
    Copy,
    ExternalLink,
    Eye,
    Info,
} from 'lucide-vue-next';

interface Props {
    media: PaginatedMedia;
    folders: MediaFolder[];
    currentFolder: MediaFolder | null;
    statistics: MediaStatistics;
    filters: MediaFilters;
}

const props = defineProps<Props>();

const page = usePage();

// View mode state
const viewMode = ref<MediaViewMode>('grid');

// Form state for filters
const form = ref<MediaFilters>({
    folder_id: props.filters.folder_id || null,
    type: props.filters.type || 'all',
    search: props.filters.search || '',
    sort_by: props.filters.sort_by || 'created_at',
    sort_order: props.filters.sort_order || 'desc',
});

// Selection state
const selectedFiles = ref<number[]>([]);
const selectAll = ref(false);

// Folder tree state
const expandedFolders = ref<number[]>([]);
const showNewFolderModal = ref(false);
const showUploadModal = ref(false);
const showDeleteModal = ref(false);
const showFileDetailsModal = ref(false);
const selectedFileDetails = ref<MediaFile | null>(null);
const deleteTarget = ref<{ type: 'single' | 'bulk'; file?: MediaFile; count?: number } | null>(null);
const newFolderName = ref('');
const newFolderParentId = ref<number | null>(null);
const copyUrlFeedback = ref(false);

// Computed properties for delete modal
const deleteModalTitle = computed(() => {
    if (deleteTarget.value?.type === 'bulk') {
        return 'Delete Multiple Files';
    }
    return deleteTarget.value?.file?.original_filename ?? '';
});

const deleteModalMessage = computed(() => {
    if (deleteTarget.value?.type === 'bulk') {
        return `Are you sure you want to delete ${deleteTarget.value.count} file(s)? This action cannot be undone.`;
    }
    return `Are you sure you want to delete '${deleteTarget.value?.file?.original_filename}'? This action cannot be undone.`;
});

// Toggle folder expansion
const toggleFolder = (folderId: number) => {
    const index = expandedFolders.value.indexOf(folderId);
    if (index > -1) {
        expandedFolders.value.splice(index, 1);
    } else {
        expandedFolders.value.push(folderId);
    }
};

// Navigate to folder
const navigateToFolder = (folderId: number | null) => {
    form.value.folder_id = folderId;
    applyFilters();
};

// Create folder
const createFolder = () => {
    if (!newFolderName.value.trim()) {
        return;
    }

    router.post(
        folderRoutes.store().url,
        {
            name: newFolderName.value,
            parent_id: newFolderParentId.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showNewFolderModal.value = false;
                newFolderName.value = '';
                newFolderParentId.value = null;
            },
        }
    );
};

const cancelCreateFolder = () => {
    showNewFolderModal.value = false;
    newFolderName.value = '';
    newFolderParentId.value = null;
};

// Build folder tree recursively
const buildFolderTree = (folders: MediaFolder[], parentId: number | null = null): MediaFolder[] => {
    return folders
        .filter(folder => folder.parent_id === parentId)
        .map(folder => ({
            ...folder,
            children: buildFolderTree(folders, folder.id)
        }));
};

const folderTree = computed(() => buildFolderTree(props.folders));

// Watch for select all toggle
watch(selectAll, (value) => {
    if (value) {
        selectedFiles.value = props.media.data.map(file => file.id);
    } else {
        selectedFiles.value = [];
    }
});

// Debounced filter application
const applyFilters = debounce(() => {
    router.get(
        mediaRoutes.index().url,
        {
            folder_id: form.value.folder_id || undefined,
            type: form.value.type !== 'all' ? form.value.type : undefined,
            search: form.value.search || undefined,
            sort_by: form.value.sort_by,
            sort_order: form.value.sort_order,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}, 500);

// Watch form changes
watch(() => form.value.search, applyFilters);
watch(() => form.value.type, applyFilters);
watch(() => form.value.folder_id, applyFilters);

// Format file size
const formatSize = (bytes: number | string | null | undefined): string => {
    // If already a string (formatted), return it
    if (typeof bytes === 'string') return bytes;
    
    // Convert to number if needed
    const numBytes = Number(bytes);
    
    if (!numBytes || isNaN(numBytes) || numBytes === 0) return '0.00 B';
    
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = numBytes;
    let unitIndex = 0;
    
    while (size > 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    
    return `${size.toFixed(2)} ${units[unitIndex]}`;
};

// Format date
const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

// Get file icon based on type
const getFileIcon = (file: MediaFile) => {
    if (file.is_image) return FileImage;
    if (file.is_video) return FileVideo;
    return File;
};

// Toggle file selection
const toggleSelection = (fileId: number) => {
    const index = selectedFiles.value.indexOf(fileId);
    if (index > -1) {
        selectedFiles.value.splice(index, 1);
    } else {
        selectedFiles.value.push(fileId);
    }
};

// Clear selection
const clearSelection = () => {
    selectedFiles.value = [];
    selectAll.value = false;
};

// Bulk delete
const bulkDelete = () => {
    deleteTarget.value = {
        type: 'bulk',
        count: selectedFiles.value.length,
    };
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    
    if (deleteTarget.value.type === 'bulk') {
        router.post(
            mediaRoutes.bulkAction().url,
            {
                action: 'delete',
                ids: selectedFiles.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    clearSelection();
                    showDeleteModal.value = false;
                    deleteTarget.value = null;
                },
            }
        );
    } else if (deleteTarget.value.type === 'single' && deleteTarget.value.file) {
        router.delete(mediaRoutes.destroy(deleteTarget.value.file.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                deleteTarget.value = null;
            },
        });
    }
};

// Watch for modal close to reset deleteTarget
watch(showDeleteModal, (newValue) => {
    if (!newValue) {
        deleteTarget.value = null;
    }
});

// Delete single file
const deleteFile = (file: MediaFile) => {
    deleteTarget.value = {
        type: 'single',
        file: file,
    };
    showDeleteModal.value = true;
};

// Upload handlers
const handleUploadSuccess = (files: File[]) => {
    showUploadModal.value = false;
    // Refresh the page to show uploaded files
    router.reload({ only: ['media', 'statistics'] });
};

const handleUploadError = (message: string) => {
    console.error('Upload error:', message);
    // You could also show a toast notification here
};

// View file details
const viewFileDetails = (file: MediaFile) => {
    selectedFileDetails.value = file;
    showFileDetailsModal.value = true;
};

// Copy URL to clipboard
const copyUrlToClipboard = async (url: string) => {
    try {
        await navigator.clipboard.writeText(url);
        copyUrlFeedback.value = true;
        setTimeout(() => {
            copyUrlFeedback.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy URL:', err);
        alert('Failed to copy URL to clipboard');
    }
};
</script>

<template>
    <Head title="Media Library" />

    <AdminLayout title="Media Library">
        <div class="flex gap-6">
            <!-- Sidebar - Folder Tree -->
            <div class="w-64 flex-shrink-0 space-y-4">
                <!-- All Files -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button
                        @click="navigateToFolder(null)"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 text-left transition-colors',
                            form.folder_id === null
                                ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                                : 'hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-300'
                        ]"
                    >
                        <Home :size="18" />
                        <span class="font-medium">All Files</span>
                    </button>
                </div>

                <!-- Folders -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-medium text-gray-900 dark:text-white">Folders</h3>
                        <button
                            @click="showNewFolderModal = true"
                            class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                            title="Create folder"
                        >
                            <FolderPlus :size="18" class="text-gray-600 dark:text-gray-400" />
                        </button>
                    </div>

                    <div class="p-2">
                        <!-- Folder tree component -->
                        <div v-for="folder in folderTree" :key="folder.id">
                            <FolderTreeItem
                                :folder="folder"
                                :current-folder-id="form.folder_id ?? null"
                                :expanded-folders="expandedFolders"
                                @toggle="toggleFolder"
                                @navigate="navigateToFolder"
                            />
                        </div>

                        <!-- Empty state -->
                        <div v-if="folderTree.length === 0" class="py-8 text-center">
                            <Folder :size="32" class="mx-auto text-gray-300 dark:text-gray-600 mb-2" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">No folders yet</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Media Library
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage your images, videos, and documents
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- View mode toggle -->
                    <div class="flex items-center gap-1 rounded-lg border border-gray-200 dark:border-gray-700 p-1">
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'p-2 rounded transition-colors',
                                viewMode === 'grid'
                                    ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
                                    : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'
                            ]"
                            title="Grid view"
                        >
                            <Grid3x3 :size="18" />
                        </button>
                        <button
                            @click="viewMode = 'list'"
                            :class="[
                                'p-2 rounded transition-colors',
                                viewMode === 'list'
                                    ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
                                    : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'
                            ]"
                            title="List view"
                        >
                            <List :size="18" />
                        </button>
                    </div>

                    <!-- Upload button -->
                    <button
                        @click="showUploadModal = true"
                        type="button"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        <Upload :size="16" class="mr-2" />
                        Upload Files
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Files</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ statistics.total_files }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <File :size="24" class="text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Images</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ statistics.total_images }}
                            </p>
                        </div>
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <FileImage :size="24" class="text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Videos</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ statistics.total_videos }}
                            </p>
                        </div>
                        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <FileVideo :size="24" class="text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Storage Used</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ formatSize(statistics.total_size) }}
                            </p>
                        </div>
                        <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                            <Image :size="24" class="text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <Search :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="form.search"
                                type="text"
                                placeholder="Search files..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Type filter -->
                    <div>
                        <select
                            v-model="form.type"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="all">All Files</option>
                            <option value="images">Images</option>
                            <option value="videos">Videos</option>
                            <option value="documents">Documents</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div>
                        <select
                            v-model="form.sort_by"
                            @change="applyFilters"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="created_at">Date Added</option>
                            <option value="original_filename">Name</option>
                            <option value="size">Size</option>
                        </select>
                    </div>
                </div>

                <!-- Selection toolbar -->
                <div v-if="selectedFiles.length > 0" class="mt-4 flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                        {{ selectedFiles.length }} file(s) selected
                    </span>
                    <div class="flex items-center gap-2">
                        <button
                            @click="bulkDelete"
                            class="px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                        >
                            Delete Selected
                        </button>
                        <button
                            @click="clearSelection"
                            class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            <X :size="16" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Media Grid -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <!-- Empty state -->
                <div v-if="media.data.length === 0" class="text-center py-12">
                    <Image :size="48" class="mx-auto text-gray-300 dark:text-gray-600 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No files found</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Upload files to get started
                    </p>
                </div>

                <!-- Grid view -->
                <div v-else-if="viewMode === 'grid'" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div
                        v-for="file in media.data"
                        :key="file.id"
                        :class="[
                            'group relative aspect-square rounded-lg border-2 transition-all overflow-hidden',
                            selectedFiles.includes(file.id)
                                ? 'border-blue-500 ring-2 ring-blue-500 ring-offset-2'
                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                        ]"
                    >
                        <!-- Checkbox -->
                        <div class="absolute top-2 left-2 z-10">
                            <input
                                type="checkbox"
                                :checked="selectedFiles.includes(file.id)"
                                class="w-5 h-5 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500"
                                @click.stop
                                @change="toggleSelection(file.id)"
                            />
                        </div>

                        <!-- View Details Button -->
                        <button
                            @click="viewFileDetails(file)"
                            class="absolute top-2 right-2 z-10 p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-md opacity-0 group-hover:opacity-100 transition-opacity hover:bg-gray-100 dark:hover:bg-gray-700"
                            title="View details"
                        >
                            <Info :size="16" class="text-gray-600 dark:text-gray-400" />
                        </button>

                        <!-- Image preview -->
                        <div 
                            v-if="file.is_image" 
                            class="w-full h-full cursor-pointer"
                            @click="viewFileDetails(file)"
                        >
                            <img
                                :src="file.thumbnail_url || file.url"
                                :alt="file.alt_text || file.original_filename"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <!-- Non-image file icon -->
                        <div 
                            v-else 
                            class="w-full h-full flex items-center justify-center bg-gray-50 dark:bg-gray-700 cursor-pointer"
                            @click="viewFileDetails(file)"
                        >
                            <component :is="getFileIcon(file)" :size="48" class="text-gray-400" />
                        </div>

                        <!-- Overlay on hover -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100 pointer-events-none">
                            <p class="text-white text-xs font-medium px-2 text-center truncate w-full">
                                {{ file.original_filename }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- List view -->
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        v-model="selectAll"
                                        type="checkbox"
                                        class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Preview
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Size
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Uploaded
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="file in media.data"
                                :key="file.id"
                                :class="[
                                    'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors',
                                    selectedFiles.includes(file.id) ? 'bg-blue-50 dark:bg-blue-900/20' : ''
                                ]"
                            >
                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :checked="selectedFiles.includes(file.id)"
                                        @change="toggleSelection(file.id)"
                                        class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="w-12 h-12 rounded overflow-hidden bg-gray-100 dark:bg-gray-700">
                                        <img
                                            v-if="file.is_image"
                                            :src="file.thumbnail_url || file.url"
                                            :alt="file.alt_text || file.original_filename"
                                            class="w-full h-full object-cover"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <component :is="getFileIcon(file)" :size="24" class="text-gray-400" />
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ file.original_filename }}
                                    </div>
                                    <div v-if="file.title" class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ file.title }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ file.formatted_size }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ file.extension?.toUpperCase() || 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(file.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="viewFileDetails(file)"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                                        >
                                            View
                                        </button>
                                        <button
                                            @click="deleteFile(file)"
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="media.last_page > 1" class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ media.from }} to {{ media.to }} of {{ media.total }} files
                    </div>
                    <div class="flex gap-2">
                        <button
                            v-for="page in media.last_page"
                            :key="page"
                            @click="router.get(mediaRoutes.index().url + `?page=${page}`)"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                                page === media.current_page
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600'
                            ]"
                        >
                            {{ page }}
                        </button>
                    </div>
                </div>
            </div>
            </div>
            <!-- End of Main Content -->
        </div>
        <!-- End of flex container -->
        
        <!-- Upload Modal -->
        <div
            v-if="showUploadModal"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                    @click="showUploadModal = false"
                ></div>

                <!-- Modal panel -->
                <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-10">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Upload Files
                        </h3>
                        <button
                            @click="showUploadModal = false"
                            type="button"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                        >
                            <X :size="20" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4">
                        <UploadDropzone
                            :folder-id="form.folder_id"
                            @success="handleUploadSuccess"
                            @error="handleUploadError"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmDeleteModal
            v-model:show="showDeleteModal"
            :title="deleteModalTitle"
            :message="deleteModalMessage"
            @confirm="confirmDelete"
        />

        <!-- File Details Modal -->
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
                    v-if="showFileDetailsModal && selectedFileDetails"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    @click="showFileDetailsModal = false"
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
                            v-if="showFileDetailsModal && selectedFileDetails"
                            class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
                            @click.stop
                        >
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            File Details
                        </h3>
                        <button
                            @click="showFileDetailsModal = false"
                            type="button"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                        >
                            <X :size="20" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Preview -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Preview
                                </label>
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-700">
                                    <img
                                        v-if="selectedFileDetails.is_image"
                                        :src="selectedFileDetails.url"
                                        :alt="selectedFileDetails.alt_text || selectedFileDetails.original_filename"
                                        class="w-full h-auto"
                                    />
                                    <div v-else class="flex items-center justify-center h-64">
                                        <component :is="getFileIcon(selectedFileDetails)" :size="64" class="text-gray-400" />
                                    </div>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="space-y-4">
                                <!-- Filename -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Filename
                                    </label>
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        {{ selectedFileDetails.original_filename }}
                                    </p>
                                </div>

                                <!-- URL with Copy Button -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        File URL
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="text"
                                            :value="selectedFileDetails.url"
                                            readonly
                                            class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white"
                                        />
                                        <button
                                            @click="copyUrlToClipboard(selectedFileDetails.url)"
                                            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                                            :class="{ 'bg-green-600 hover:bg-green-700': copyUrlFeedback }"
                                        >
                                            <Copy :size="16" />
                                            {{ copyUrlFeedback ? 'Copied!' : 'Copy' }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Thumbnail URL (if image) -->
                                <div v-if="selectedFileDetails.is_image && selectedFileDetails.thumbnail_url">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Thumbnail URL
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="text"
                                            :value="selectedFileDetails.thumbnail_url"
                                            readonly
                                            class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white"
                                        />
                                        <button
                                            @click="copyUrlToClipboard(selectedFileDetails.thumbnail_url!)"
                                            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                                        >
                                            <Copy :size="16" />
                                            Copy
                                        </button>
                                    </div>
                                </div>

                                <!-- File Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        File Type
                                    </label>
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        {{ selectedFileDetails.mime_type }} ({{ selectedFileDetails.extension?.toUpperCase() }})
                                    </p>
                                </div>

                                <!-- File Size -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        File Size
                                    </label>
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        {{ selectedFileDetails.formatted_size }}
                                    </p>
                                </div>

                                <!-- Dimensions (if image) -->
                                <div v-if="selectedFileDetails.width && selectedFileDetails.height">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Dimensions
                                    </label>
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        {{ selectedFileDetails.width }} × {{ selectedFileDetails.height }} px
                                    </p>
                                </div>

                                <!-- Alt Text -->
                                <div v-if="selectedFileDetails.alt_text">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Alt Text
                                    </label>
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        {{ selectedFileDetails.alt_text }}
                                    </p>
                                </div>

                                <!-- Title -->
                                <div v-if="selectedFileDetails.title">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Title
                                    </label>
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        {{ selectedFileDetails.title }}
                                    </p>
                                </div>

                                <!-- Description -->
                                <div v-if="selectedFileDetails.description">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Description
                                    </label>
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        {{ selectedFileDetails.description }}
                                    </p>
                                </div>

                                <!-- Uploaded Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Uploaded
                                    </label>
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        {{ formatDate(selectedFileDetails.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between">
                        <a
                            :href="selectedFileDetails.url"
                            target="_blank"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors"
                        >
                            <ExternalLink :size="16" />
                            Open in New Tab
                        </a>
                        <button
                            @click="showFileDetailsModal = false"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                        >
                            Close
                        </button>
                    </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- New Folder Modal -->
        <div
            v-if="showNewFolderModal"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                    @click="cancelCreateFolder"
                ></div>

                <!-- Modal panel -->
                <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full z-10">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Create New Folder
                            </h3>
                            <button
                                @click="cancelCreateFolder"
                                type="button"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4">
                        <div>
                            <label for="folder-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Folder Name
                            </label>
                            <input
                                id="folder-name"
                                v-model="newFolderName"
                                type="text"
                                placeholder="Enter folder name"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                @keyup.enter="createFolder"
                            />
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-end gap-3">
                        <button
                            @click="cancelCreateFolder"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="createFolder"
                            type="button"
                            :disabled="!newFolderName.trim()"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
                        >
                            Create Folder
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
