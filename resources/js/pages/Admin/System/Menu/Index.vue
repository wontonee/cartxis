<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { Plus, GripVertical, Trash2, Eye, EyeOff, Plug, Edit } from 'lucide-vue-next'
import AdminLayout from '@/layouts/AdminLayout.vue'
import * as menuRoutes from '@/routes/admin/system/menus/index'
import MenuFormModal from '@/components/Admin/System/Menu/MenuFormModal.vue'
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue'
import type { MenuItem } from '@/types/admin-menu'

interface Props {
    menuItems: MenuItem[]
}

const props = defineProps<Props>()

// State
const showFormModal = ref(false)
const editingItem = ref<MenuItem | null>(null)
const showDeleteModal = ref(false)
const deletingItem = ref<MenuItem | null>(null)

// Computed properties for menu organization
const parentItems = computed(() => props.menuItems.filter(item => !item.parent_id))

const getChildren = (parentId: number): MenuItem[] => {
    return props.menuItems.filter(item => item.parent_id === parentId)
}

// Drag and drop state
const draggedItem = ref<MenuItem | null>(null)
const dragOverItem = ref<MenuItem | null>(null)

// Drag handlers
const handleDragStart = (event: DragEvent, item: MenuItem) => {
    draggedItem.value = item
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move'
        event.dataTransfer.setData('text/html', (event.target as HTMLElement).innerHTML)
    }
}

const handleDragOver = (event: DragEvent, item: MenuItem) => {
    event.preventDefault()
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move'
    }
    
    // Only allow reordering within same parent level
    if (draggedItem.value && draggedItem.value.parent_id === item.parent_id) {
        dragOverItem.value = item
    }
}

const handleDragLeave = () => {
    dragOverItem.value = null
}

const handleDrop = (event: DragEvent, targetItem: MenuItem) => {
    event.preventDefault()
    
    if (!draggedItem.value || draggedItem.value.id === targetItem.id) {
        draggedItem.value = null
        dragOverItem.value = null
        return
    }
    
    // Only allow reordering within same parent level
    if (draggedItem.value.parent_id !== targetItem.parent_id) {
        draggedItem.value = null
        dragOverItem.value = null
        return
    }
    
    // Get all items at the same level
    const siblings = props.menuItems.filter(item => item.parent_id === draggedItem.value!.parent_id)
    const draggedIndex = siblings.findIndex(item => item.id === draggedItem.value!.id)
    const targetIndex = siblings.findIndex(item => item.id === targetItem.id)
    
    if (draggedIndex === -1 || targetIndex === -1) {
        draggedItem.value = null
        dragOverItem.value = null
        return
    }
    
    // Reorder the items
    const items = siblings.map((item, index) => {
        let newOrder = index
        
        if (draggedIndex < targetIndex) {
            // Moving down
            if (index === draggedIndex) {
                newOrder = targetIndex
            } else if (index > draggedIndex && index <= targetIndex) {
                newOrder = index - 1
            }
        } else {
            // Moving up
            if (index === draggedIndex) {
                newOrder = targetIndex
            } else if (index >= targetIndex && index < draggedIndex) {
                newOrder = index + 1
            }
        }
        
        return {
            id: item.id,
            order: newOrder,
            parent_id: item.parent_id
        }
    })
    
    // Send to backend
    router.post(menuRoutes.reorder(), { items }, {
        preserveScroll: true
    })
    
    draggedItem.value = null
    dragOverItem.value = null
}

const handleDragEnd = () => {
    draggedItem.value = null
    dragOverItem.value = null
}

// Handlers
const handleCreate = () => {
    editingItem.value = null
    showFormModal.value = true
}

const handleEdit = (item: MenuItem) => {
    editingItem.value = item
    showFormModal.value = true
}

const handleDelete = (item: MenuItem) => {
    deletingItem.value = item
    showDeleteModal.value = true
}

const confirmDelete = () => {
    if (!deletingItem.value) return

    router.delete(menuRoutes.destroy(deletingItem.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            deletingItem.value = null
        },
    })
}

const toggleActive = (item: MenuItem) => {
    router.post(menuRoutes.toggle(item.id))
}
</script>

<template>
    <Head title="Menu Management" />

    <AdminLayout title="Menu Management">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                   <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        Menu Management
                   </h1>
                   <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage and customize your admin sidebar navigation hierarchy.
                   </p>
                </div>
                <button
                    @click="handleCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200"
                >
                    <Plus class="h-4 w-4" />
                    Add Menu Item
                </button>
            </div>

            <!-- Content -->
            <div class="overflow-auto rounded-xl">

            <!-- Info Card -->
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20 mb-6">
                <p class="text-sm text-blue-800 dark:text-blue-200 flex items-start gap-2">
                    <span class="mt-0.5">•</span>
                    <span><strong>Admin Menu Structure:</strong> Drag and drop items to reorder the sidebar navigation. Parent items act as collapsible sections when they contain child menu links.</span>
                </p>
            </div>

            <!-- Menu Tree -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50/50 dark:bg-gray-900/50">
                            <tr>
                                <th class="w-12 px-6 py-4"></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Title
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Icon
                                </th>
                                <th class="hidden sm:table-cell px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Route / URL
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Status
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <template v-for="item in parentItems" :key="item.id">
                                <tr 
                                    draggable="true"
                                    @dragstart="handleDragStart($event, item)"
                                    @dragover="handleDragOver($event, item)"
                                    @dragleave="handleDragLeave"
                                    @drop="handleDrop($event, item)"
                                    @dragend="handleDragEnd"
                                    :class="[
                                        'group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors',
                                        draggedItem?.id === item.id ? 'opacity-50 bg-gray-50 dark:bg-gray-700' : '',
                                        dragOverItem?.id === item.id ? 'border-t-2 border-blue-500' : ''
                                    ]"
                                >
                                    <!-- Drag Handle -->
                                    <td class="px-6 py-4 pl-4 sm:pl-6 text-center">
                                        <div class="cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 inline-block transition-colors">
                                            <GripVertical class="h-4 w-4" />
                                        </div>
                                    </td>

                                    <!-- Title -->
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ item.title }}</span>
                                            <span v-if="getChildren(item.id).length > 0" class="inline-flex w-fit rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ getChildren(item.id).length }} children
                                            </span>
                                            <!-- Mobile-only URL view -->
                                            <span v-if="item.route || item.url" class="sm:hidden text-xs text-gray-500 font-mono truncate max-w-[150px]">
                                                {{ item.route ? `route: ${item.route}` : item.url }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Icon -->
                                    <td class="px-6 py-4">
                                        <div v-if="item.icon" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 py-1 px-2 rounded w-fit font-mono text-xs">
                                            <Plug class="w-3 h-3" />
                                            {{ item.icon }}
                                        </div>
                                        <span v-else class="text-sm text-gray-400">—</span>
                                    </td>

                                    <!-- Route / URL -->
                                    <td class="hidden sm:table-cell px-6 py-4">
                                        <span v-if="item.route" class="font-mono text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-0.5 rounded">{{ item.route }}</span>
                                        <span v-else-if="item.url" class="font-mono text-xs text-gray-600 dark:text-gray-400">{{ item.url }}</span>
                                        <span v-else class="text-sm text-gray-400">#</span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        <button
                                            @click="toggleActive(item)"
                                            :class="[
                                                'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                item.active
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            ]"
                                        >
                                            <Eye v-if="item.active" class="h-3 w-3" />
                                            <EyeOff v-else class="h-3 w-3" />
                                            {{ item.active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <button
                                                @click="handleEdit(item)"
                                                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                title="Edit"
                                            >
                                                <Edit class="w-4 h-4" />
                                            </button>
                                            <button
                                                @click="handleDelete(item)"
                                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                title="Delete"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Children -->
                                <tr 
                                    v-for="child in getChildren(item.id)" 
                                    :key="child.id" 
                                    draggable="true"
                                    @dragstart="handleDragStart($event, child)"
                                    @dragover="handleDragOver($event, child)"
                                    @dragleave="handleDragLeave"
                                    @drop="handleDrop($event, child)"
                                    @dragend="handleDragEnd"
                                    :class="[
                                        'group bg-gray-50/50 hover:bg-gray-100 dark:bg-gray-900/30 dark:hover:bg-gray-800 transition-colors',
                                        draggedItem?.id === child.id ? 'opacity-50' : '',
                                        dragOverItem?.id === child.id ? 'border-t-2 border-blue-500' : ''
                                    ]"
                                >
                                    <td class="px-6 py-4 pl-8 sm:pl-12 text-center border-l-2 border-transparent">
                                        <div class="cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 inline-block p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                            <GripVertical class="h-4 w-4" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-gray-300 dark:text-gray-600 sm:inline hidden">SUB</span>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ child.title }}</span>
                                            </div>
                                            <!-- Mobile-only URL view -->
                                            <span v-if="child.route || child.url" class="sm:hidden text-xs text-gray-500 font-mono truncate max-w-[150px]">
                                                {{ child.route ? `route: ${child.route}` : child.url }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="child.icon" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600 py-0.5 px-2 rounded w-fit font-mono text-xs scale-90 origin-left">
                                            <Plug class="w-3 h-3" />
                                            {{ child.icon }}
                                        </div>
                                        <span v-else class="text-sm text-gray-400">—</span>
                                    </td>
                                    <td class="hidden sm:table-cell px-6 py-4">
                                        <span v-if="child.route" class="font-mono text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-0.5 rounded">{{ child.route }}</span>
                                        <span v-else-if="child.url" class="font-mono text-xs text-gray-600 dark:text-gray-400">{{ child.url }}</span>
                                        <span v-else class="text-sm text-gray-400">#</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button
                                            @click="toggleActive(child)"
                                            :class="[
                                                'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                child.active
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            ]"
                                        >
                                            <Eye v-if="child.active" class="h-3 w-3" />
                                            <EyeOff v-else class="h-3 w-3" />
                                            {{ child.active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <button
                                                @click="handleEdit(child)"
                                                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                title="Edit"
                                            >
                                                <Edit class="w-4 h-4" />
                                            </button>
                                            <button
                                                @click="handleDelete(child)"
                                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                title="Delete"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="parentItems.length === 0" class="p-12 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No menu items found. Click "Add Menu Item" to create your first item.</p>
                </div>
            </div>
        </div>
    </div>

        <!-- Form Modal -->
        <MenuFormModal
            v-model:show="showFormModal"
            :menu-item="editingItem"
            :parent-options="menuItems"
        />

        <!-- Delete Confirmation -->
        <ConfirmDeleteModal
            v-model:show="showDeleteModal"
            :title="deletingItem?.title ?? ''"
            :message="`Are you sure you want to delete this menu item? ${deletingItem && getChildren(deletingItem.id).length > 0 ? 'All child items will also be deleted.' : ''}`"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
