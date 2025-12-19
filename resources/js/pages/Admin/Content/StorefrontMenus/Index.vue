<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Plus, GripVertical } from 'lucide-vue-next';
import AdminLayout from '@/layouts/AdminLayout.vue';
import MenuFormModal from '@/components/Admin/Content/StorefrontMenu/MenuFormModal.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';

interface MenuItem {
  id: number;
  title: string;
  key: string | null;
  icon: string | null;
  route: string | null;
  url: string | null;
  parent_id: number | null;
  location: string;
  menu_type: string;
  order: number;
  active: boolean;
  children?: MenuItem[];
}

interface Props {
  headerMenuItems: MenuItem[];
  footerMenuItems: MenuItem[];
  mobileMenuItems: MenuItem[];
}

const props = defineProps<Props>();

const activeTab = ref<'header' | 'footer' | 'mobile'>('header');
const showFormModal = ref(false);
const editingItem = ref<MenuItem | null>(null);
const showDeleteModal = ref(false);
const deletingItem = ref<MenuItem | null>(null);

const currentMenuItems = computed(() => {
  switch (activeTab.value) {
    case 'header':
      return props.headerMenuItems;
    case 'footer':
      return props.footerMenuItems;
    case 'mobile':
      return props.mobileMenuItems;
    default:
      return [];
  }
});

const parentItems = computed(() => currentMenuItems.value.filter(item => !item.parent_id));

const getChildren = (parentId: number): MenuItem[] => {
  return currentMenuItems.value.filter(item => item.parent_id === parentId);
};

const handleCreate = () => {
  editingItem.value = null;
  showFormModal.value = true;
};

const handleEdit = (item: MenuItem) => {
  editingItem.value = item;
  showFormModal.value = true;
};

const handleDelete = (item: MenuItem) => {
  deletingItem.value = item;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (!deletingItem.value) return;
  router.delete(`/admin/content/storefront-menus/${deletingItem.value.id}`, {
    onSuccess: () => {
      showDeleteModal.value = false;
      deletingItem.value = null;
    },
  });
};

const toggleActive = (item: MenuItem) => {
  router.post(`/admin/content/storefront-menus/${item.id}/toggle`, {}, {
    preserveScroll: true,
  });
};

const draggedItem = ref<MenuItem | null>(null);
const dragOverItem = ref<MenuItem | null>(null);

const handleDragStart = (event: DragEvent, item: MenuItem) => {
  draggedItem.value = item;
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move';
  }
};

const handleDragOver = (event: DragEvent, item: MenuItem) => {
  event.preventDefault();
  if (event.dataTransfer) {
    event.dataTransfer.dropEffect = 'move';
  }
  if (draggedItem.value && draggedItem.value.parent_id === item.parent_id) {
    dragOverItem.value = item;
  }
};

const handleDrop = (event: DragEvent, targetItem: MenuItem) => {
  event.preventDefault();
  
  if (!draggedItem.value || draggedItem.value.id === targetItem.id) {
    draggedItem.value = null;
    dragOverItem.value = null;
    return;
  }
  
  if (draggedItem.value.parent_id !== targetItem.parent_id) {
    draggedItem.value = null;
    dragOverItem.value = null;
    return;
  }
  
  const siblings = currentMenuItems.value.filter(item => item.parent_id === draggedItem.value!.parent_id);
  const draggedIndex = siblings.findIndex(item => item.id === draggedItem.value!.id);
  const targetIndex = siblings.findIndex(item => item.id === targetItem.id);
  
  if (draggedIndex === -1 || targetIndex === -1) {
    draggedItem.value = null;
    dragOverItem.value = null;
    return;
  }
  
  const items = siblings.map((item, index) => {
    let newOrder = index;
    
    if (draggedIndex < targetIndex) {
      if (index === draggedIndex) {
        newOrder = targetIndex;
      } else if (index > draggedIndex && index <= targetIndex) {
        newOrder = index - 1;
      }
    } else {
      if (index === draggedIndex) {
        newOrder = targetIndex;
      } else if (index >= targetIndex && index < draggedIndex) {
        newOrder = index + 1;
      }
    }
    
    return {
      id: item.id,
      order: newOrder,
      parent_id: item.parent_id
    };
  });
  
  router.post('/admin/content/storefront-menus/reorder', { items }, {
    preserveScroll: true
  });
  
  draggedItem.value = null;
  dragOverItem.value = null;
};

const handleDragEnd = () => {
  draggedItem.value = null;
  dragOverItem.value = null;
};
</script>

<template>
  <Head title="Storefront Menu" />

  <AdminLayout title="Storefront Menu">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Storefront Menu</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Content → Storefront Menu</p>
        </div>
        <button
          @click="handleCreate"
          class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
        >
          <Plus class="h-4 w-4" />
          Add Menu Item
        </button>
      </div>

      <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
        <p class="text-sm text-blue-800 dark:text-blue-200">
          <strong>Storefront Navigation:</strong> Manage header, footer, and mobile navigation menus for your store. Drag and drop to reorder items.
        </p>
      </div>

      <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'header'"
            :class="[
              activeTab === 'header'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
              'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors'
            ]"
          >
            Header Menu
            <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-700">
              {{ headerMenuItems.length }}
            </span>
          </button>
          <button
            @click="activeTab = 'footer'"
            :class="[
              activeTab === 'footer'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
              'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors'
            ]"
          >
            Footer Menu
            <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-700">
              {{ footerMenuItems.length }}
            </span>
          </button>
          <button
            @click="activeTab = 'mobile'"
            :class="[
              activeTab === 'mobile'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
              'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors'
            ]"
          >
            Mobile Menu
            <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-700">
              {{ mobileMenuItems.length }}
            </span>
          </button>
        </nav>
      </div>

      <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
              <tr>
                <th class="w-10 px-6 py-3"></th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">URL/Route</th>
                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
              <template v-for="item in parentItems" :key="item.id">
                <tr
                  draggable="true"
                  @dragstart="handleDragStart($event, item)"
                  @dragover="handleDragOver($event, item)"
                  @drop="handleDrop($event, item)"
                  @dragend="handleDragEnd"
                  :class="{ 'bg-blue-50 dark:bg-blue-900/20': dragOverItem?.id === item.id }"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
                >
                  <td class="px-6 py-4">
                    <button class="cursor-pointer text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                      <GripVertical class="h-5 w-5" />
                    </button>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
                    <div class="flex items-center">
                      <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.title }}</span>
                      <span v-if="item.children && item.children.length > 0" class="ml-2 text-xs text-gray-500">
                        ({{ item.children.length }} sub-items)
                      </span>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                    {{ item.url || item.route || '-' }}
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-center">
                    <span
                      :class="[
                        item.active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold'
                      ]"
                    >
                      {{ item.active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <button @click="handleEdit(item)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Edit">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button @click="handleDelete(item)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Delete">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>

                <tr
                  v-for="child in getChildren(item.id)"
                  :key="child.id"
                  draggable="true"
                  @dragstart="handleDragStart($event, child)"
                  @dragover="handleDragOver($event, child)"
                  @drop="handleDrop($event, child)"
                  @dragend="handleDragEnd"
                  :class="{ 'bg-blue-50 dark:bg-blue-900/20': dragOverItem?.id === child.id }"
                  class="bg-gray-50 hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-800"
                >
                  <td class="px-6 py-3">
                    <button class="cursor-pointer pl-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                      <GripVertical class="h-4 w-4" />
                    </button>
                  </td>
                  <td class="whitespace-nowrap px-6 py-3">
                    <div class="flex items-center pl-8">
                      <span class="text-sm text-gray-900 dark:text-white">{{ child.title }}</span>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ child.url || child.route || '-' }}
                  </td>
                  <td class="whitespace-nowrap px-6 py-3 text-center">
                    <span
                      :class="[
                        child.active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold'
                      ]"
                    >
                      {{ child.active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-6 py-3 text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <button @click="handleEdit(child)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Edit">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button @click="handleDelete(child)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Delete">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>

              <tr v-if="currentMenuItems.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <p class="text-sm text-gray-500 dark:text-gray-400">No menu items found</p>
                  <button @click="handleCreate" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <Plus class="h-4 w-4" />
                    Add Menu Item
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <MenuFormModal
      :show="showFormModal"
      :menu-item="editingItem"
      :menu-type="activeTab"
      :parent-options="parentItems"
      @update:show="showFormModal = $event"
    />

    <ConfirmDeleteModal
      v-model:show="showDeleteModal"
      title="Delete Menu Item"
      :message="`Are you sure you want to delete '${deletingItem?.title}'? This action cannot be undone.`"
      @confirm="confirmDelete"
    />
  </AdminLayout>
</template>
