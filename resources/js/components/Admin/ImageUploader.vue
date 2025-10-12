<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';

interface Props {
  modelValue: File[];
  maxFiles?: number;
  maxSize?: number; // in MB
  accept?: string;
  multiple?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  maxFiles: 10,
  maxSize: 5,
  accept: 'image/*',
  multiple: true,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: File[]): void;
}>();

const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const previewUrls = ref<{ file: File; url: string; id: string }[]>([]);

// Generate preview URLs for existing files
const generatePreviews = (files: File[]) => {
  // Only clean up previews that are no longer in the file list
  const currentUrls = previewUrls.value;
  const newPreviews: { file: File; url: string; id: string }[] = [];
  
  files.forEach((file) => {
    // Check if this file already has a preview
    const existing = currentUrls.find(p => p.file === file);
    if (existing) {
      newPreviews.push(existing);
    } else {
      // Create new preview for new file
      const previewUrl = URL.createObjectURL(file);
      newPreviews.push({
        file,
        url: previewUrl,
        id: Math.random().toString(36).substring(7),
      });
    }
  });
  
  // Clean up old preview URLs that are no longer needed
  currentUrls.forEach(preview => {
    if (!newPreviews.find(p => p.file === preview.file)) {
      URL.revokeObjectURL(preview.url);
    }
  });
  
  previewUrls.value = newPreviews;
};

// Watch for changes in modelValue and update previews
watch(() => props.modelValue, (newFiles) => {
  if (newFiles.length > 0) {
    generatePreviews(newFiles);
  } else {
    // Clean up all previews
    previewUrls.value.forEach(preview => {
      URL.revokeObjectURL(preview.url);
    });
    previewUrls.value = [];
  }
}, { immediate: true, deep: true });

// Generate previews on mount if files exist
onMounted(() => {
  if (props.modelValue.length > 0) {
    generatePreviews(props.modelValue);
  }
});

// Handle file selection
const handleFiles = (files: FileList | null) => {
  if (!files) return;

  const fileArray = Array.from(files);
  const validFiles: File[] = [];

  for (const file of fileArray) {
    // Check file type
    if (!file.type.startsWith('image/')) {
      alert(`${file.name} is not an image file`);
      continue;
    }

    // Check file size
    const sizeMB = file.size / 1024 / 1024;
    if (sizeMB > props.maxSize) {
      alert(`${file.name} exceeds ${props.maxSize}MB size limit`);
      continue;
    }

    // Check max files
    if (props.modelValue.length + validFiles.length >= props.maxFiles) {
      alert(`Maximum ${props.maxFiles} files allowed`);
      break;
    }

    validFiles.push(file);
  }

  const newFiles = [...props.modelValue, ...validFiles];
  emit('update:modelValue', newFiles);
  // generatePreviews will be called automatically by the watcher
};

// Handle drag and drop
const handleDrop = (e: DragEvent) => {
  isDragging.value = false;
  handleFiles(e.dataTransfer?.files || null);
};

const handleDragOver = (e: DragEvent) => {
  e.preventDefault();
  isDragging.value = true;
};

const handleDragLeave = () => {
  isDragging.value = false;
};

// Handle click to upload
const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  handleFiles(target.files);
  // Reset input so same file can be selected again
  target.value = '';
};

// Remove file
const removeFile = (index: number) => {
  const newFiles = props.modelValue.filter((_, i) => i !== index);
  emit('update:modelValue', newFiles);
  // Preview URLs will be regenerated automatically by the watcher
};

// Computed
const hasFiles = computed(() => previewUrls.value.length > 0);
const remainingSlots = computed(() => props.maxFiles - props.modelValue.length);
</script>

<template>
  <div class="space-y-4">
    <!-- Dropzone -->
    <div
      @drop.prevent="handleDrop"
      @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave"
      @click="triggerFileInput"
      :class="[
        'border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors',
        isDragging
          ? 'border-blue-500 bg-blue-50'
          : 'border-gray-300 hover:border-gray-400 bg-white'
      ]"
    >
      <input
        ref="fileInput"
        type="file"
        :accept="accept"
        :multiple="multiple && remainingSlots > 1"
        @change="handleFileInput"
        class="hidden"
      />

      <!-- Upload Icon -->
      <div class="mx-auto w-16 h-16 mb-4">
        <svg
          class="w-full h-full text-gray-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
          />
        </svg>
      </div>

      <!-- Instructions -->
      <div class="space-y-2">
        <p class="text-lg font-medium text-gray-700">
          <span class="text-blue-600">Click to upload</span> or drag and drop
        </p>
        <p class="text-sm text-gray-500">
          PNG, JPG, GIF up to {{ maxSize }}MB
        </p>
        <p v-if="remainingSlots > 0" class="text-xs text-gray-400">
          {{ remainingSlots }} of {{ maxFiles }} slots available
        </p>
        <p v-else class="text-xs text-red-500">
          Maximum files reached
        </p>
      </div>
    </div>

    <!-- Preview Grid -->
    <div v-if="hasFiles" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-6">
      <div
        v-for="(preview, index) in previewUrls"
        :key="preview.id"
        class="space-y-2"
      >
        <!-- Image Preview -->
        <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
          <div class="w-full h-full group relative">
            <img
              :src="preview.url"
              :alt="preview.file.name"
              class="w-full h-full object-cover block"
            />
            
            <!-- Hover Overlay with Delete Button -->
            <div class="absolute inset-0 bg-opacity-0 group-hover:bg-black group-hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center">
              <button
                type="button"
                @click.stop="removeFile(index)"
                class="opacity-0 group-hover:opacity-100 transform scale-90 group-hover:scale-100 transition-all duration-200 bg-red-600 text-white rounded-full p-3 hover:bg-red-700 shadow-lg"
                title="Remove image"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- File Name -->
        <p class="text-xs text-gray-600 truncate" :title="preview.file.name">
          {{ preview.file.name }}
        </p>

        <!-- File Size -->
        <p class="text-xs text-gray-400">
          {{ (preview.file.size / 1024).toFixed(1) }} KB
        </p>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-8">
      <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <p class="text-sm text-gray-500">No images uploaded yet</p>
    </div>
  </div>
</template>
