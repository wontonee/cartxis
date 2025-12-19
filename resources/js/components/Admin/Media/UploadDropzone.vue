<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Upload, X, FileIcon, AlertCircle } from 'lucide-vue-next';
import * as mediaRoutes from '@/routes/admin/content/media/index';

interface Props {
    folderId?: number | null;
    maxFileSize?: number; // in MB
    acceptedTypes?: string[];
}

const props = withDefaults(defineProps<Props>(), {
    folderId: null,
    maxFileSize: 10,
    acceptedTypes: () => ['image/*', 'video/*', 'application/pdf'],
});

const emit = defineEmits<{
    success: [files: File[]];
    error: [message: string];
}>();

const isDragging = ref(false);
const isUploading = ref(false);
const uploadProgress = ref(0);
const selectedFiles = ref<File[]>([]);
const errors = ref<string[]>([]);

const fileInput = ref<HTMLInputElement | null>(null);

// Computed
const acceptString = computed(() => props.acceptedTypes.join(','));

// Methods
const openFileDialog = () => {
    console.log('openFileDialog triggered, fileInput:', fileInput.value);
    fileInput.value?.click();
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    console.log('handleFileSelect triggered, files:', target.files);
    if (target.files) {
        addFiles(Array.from(target.files));
    }
    // Reset input so same file can be selected again
    target.value = '';
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    console.log('handleDrop triggered');
    
    if (event.dataTransfer?.files) {
        addFiles(Array.from(event.dataTransfer.files));
    }
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = () => {
    isDragging.value = false;
};

const addFiles = (files: File[]) => {
    console.log('addFiles called with:', files.length, 'files');
    errors.value = [];
    const validFiles: File[] = [];
    
    files.forEach(file => {
        // Check file size
        const maxSize = props.maxFileSize * 1024 * 1024; // Convert to bytes
        if (file.size > maxSize) {
            errors.value.push(`${file.name} exceeds maximum size of ${props.maxFileSize}MB`);
            return;
        }
        
        // Check file type
        const isTypeAccepted = props.acceptedTypes.some(type => {
            if (type.endsWith('/*')) {
                const category = type.split('/')[0];
                return file.type.startsWith(category + '/');
            }
            return file.type === type;
        });
        
        if (!isTypeAccepted) {
            errors.value.push(`${file.name} is not an accepted file type`);
            return;
        }
        
        validFiles.push(file);
    });
    
    console.log('Valid files:', validFiles.length);
    selectedFiles.value = [...selectedFiles.value, ...validFiles];
};

const removeFile = (index: number) => {
    selectedFiles.value.splice(index, 1);
};

const clearFiles = () => {
    selectedFiles.value = [];
    errors.value = [];
    uploadProgress.value = 0;
};

const uploadFiles = async () => {
    if (selectedFiles.value.length === 0) return;
    
    isUploading.value = true;
    errors.value = [];
    
    const formData = new FormData();
    
    selectedFiles.value.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
    });
    
    if (props.folderId) {
        formData.append('folder_id', props.folderId.toString());
    }
    
    try {
        router.post(mediaRoutes.upload().url, formData, {
            forceFormData: true,
            preserveScroll: true,
            onProgress: (progress) => {
                if (progress) {
                    uploadProgress.value = progress.percentage || 0;
                }
            },
            onSuccess: () => {
                emit('success', selectedFiles.value);
                clearFiles();
                isUploading.value = false;
            },
            onError: (errors) => {
                const errorMessages = Object.values(errors).flat() as string[];
                emit('error', errorMessages.join(', '));
                isUploading.value = false;
            },
        });
    } catch {
        errors.value.push('Upload failed. Please try again.');
        emit('error', 'Upload failed');
        isUploading.value = false;
    }
};

const formatFileSize = (bytes: number): string => {
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unitIndex = 0;
    
    while (size > 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    
    return `${size.toFixed(2)} ${units[unitIndex]}`;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Dropzone -->
        <div
            @drop.prevent="handleDrop"
            @dragover.prevent="handleDragOver"
            @dragleave="handleDragLeave"
            @click="openFileDialog"
            :class="[
                'relative border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-all',
                isDragging
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                    : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 bg-gray-50 dark:bg-gray-800/50'
            ]"
        >
            <input
                ref="fileInput"
                type="file"
                multiple
                :accept="acceptString"
                @change="handleFileSelect"
                class="hidden"
            />
            
            <Upload :size="48" class="mx-auto text-gray-400 mb-4" />
            
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                Drop files here or click to browse
            </h3>
            
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Maximum file size: {{ maxFileSize }}MB
            </p>
        </div>
        
        <!-- Errors -->
        <div v-if="errors.length > 0" class="space-y-2">
            <div
                v-for="(error, index) in errors"
                :key="index"
                class="flex items-start gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
            >
                <AlertCircle :size="18" class="text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
            </div>
        </div>
        
        <!-- Selected Files List -->
        <div v-if="selectedFiles.length > 0" class="space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ selectedFiles.length }} file(s) selected
                </h4>
                <button
                    @click="clearFiles"
                    type="button"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                >
                    Clear all
                </button>
            </div>
            
            <div class="space-y-2">
                <div
                    v-for="(file, index) in selectedFiles"
                    :key="index"
                    class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
                >
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <FileIcon :size="18" class="text-gray-400 flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ file.name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ formatFileSize(file.size) }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="removeFile(index)"
                        type="button"
                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                    >
                        <X :size="16" class="text-gray-400" />
                    </button>
                </div>
            </div>
            
            <!-- Upload Progress -->
            <div v-if="isUploading" class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Uploading...</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ Math.round(uploadProgress) }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div
                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        :style="{ width: `${uploadProgress}%` }"
                    />
                </div>
            </div>
            
            <!-- Upload Button -->
            <button
                @click="uploadFiles"
                :disabled="isUploading"
                type="button"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <Upload :size="16" class="mr-2" />
                {{ isUploading ? 'Uploading...' : 'Upload Files' }}
            </button>
        </div>
    </div>
</template>
