<script setup lang="ts">
import { computed } from 'vue';
import { Folder, FolderOpen, ChevronRight, ChevronDown } from 'lucide-vue-next';
import type { MediaFolder } from '@/types/media';

interface Props {
    folder: MediaFolder;
    currentFolderId: number | null;
    expandedFolders: number[];
    level?: number;
}

const props = withDefaults(defineProps<Props>(), {
    level: 0,
});

const emit = defineEmits<{
    toggle: [folderId: number];
    navigate: [folderId: number];
}>();

const isExpanded = computed(() => props.expandedFolders.includes(props.folder.id));
const isActive = computed(() => props.currentFolderId === props.folder.id);
const hasChildren = computed(() => props.folder.children && props.folder.children.length > 0);

const toggleFolder = (e: Event) => {
    e.stopPropagation();
    emit('toggle', props.folder.id);
};

const navigateToFolder = () => {
    emit('navigate', props.folder.id);
};
</script>

<template>
    <div>
        <button
            @click="navigateToFolder"
            :class="[
                'w-full flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-colors group',
                isActive
                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                    : 'hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-300'
            ]"
            :style="{ paddingLeft: `${level * 16 + 12}px` }"
        >
            <!-- Expand/collapse chevron -->
            <button
                v-if="hasChildren"
                @click="toggleFolder"
                class="p-0.5 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors"
            >
                <ChevronDown v-if="isExpanded" :size="14" />
                <ChevronRight v-else :size="14" />
            </button>
            <div v-else class="w-4" /> <!-- Spacer for alignment -->

            <!-- Folder icon -->
            <component
                :is="isExpanded ? FolderOpen : Folder"
                :size="16"
                :class="[
                    'flex-shrink-0',
                    isActive ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400'
                ]"
            />

            <!-- Folder name -->
            <span class="flex-1 text-left truncate font-medium">
                {{ folder.name }}
            </span>

            <!-- File count badge -->
            <span
                v-if="folder.files_count !== undefined && folder.files_count > 0"
                :class="[
                    'text-xs px-1.5 py-0.5 rounded-full',
                    isActive
                        ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300'
                        : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                ]"
            >
                {{ folder.files_count }}
            </span>
        </button>

        <!-- Child folders (recursive) -->
        <div v-if="hasChildren && isExpanded" class="mt-1">
            <FolderTreeItem
                v-for="child in folder.children"
                :key="child.id"
                :folder="child"
                :current-folder-id="currentFolderId"
                :expanded-folders="expandedFolders"
                :level="level + 1"
                @toggle="(id) => emit('toggle', id)"
                @navigate="(id) => emit('navigate', id)"
            />
        </div>
    </div>
</template>
