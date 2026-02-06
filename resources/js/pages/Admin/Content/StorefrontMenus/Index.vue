<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { 
    Plus, 
    GripVertical,
    Edit, 
    Trash2, 
    Menu, 
    Smartphone, 
    LayoutTemplate,
    Info,
    CheckCircle,
    XCircle
} from 'lucide-vue-next';
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
    <div class="p-6 space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Storefront Menu</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage header, footer, and mobile navigation menus.</p>
        </div>
        <button
          @click="handleCreate"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <Plus class="w-4 h-4 mr-2" />
          Add Menu Item
        </button>
      </div>

      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 flex items-start gap-3">
        <Info class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" />
        <p class="text-sm text-blue-800 dark:text-blue-200">
          <strong>Storefront Navigation:</strong> Manage header, footer, and mobile navigation menus. Drag and drop to reorder items.
        </p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-100 dark:border-gray-700 px-6">
            <nav class="-mb-px flex space-x-8">
            <button
                @click="activeTab = 'header'"
                :class="[
                activeTab === 'header'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors flex items-center gap-2'
                ]"
            >
                <LayoutTemplate class="w-4 h-4" />
                Header Menu
                <span :class="[
                    activeTab === 'header' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                    'ml-2 rounded-full px-2 py-0.5 text-xs transition-colors'
                ]">
                {{ headerMenuItems.length }}
                </span>
            </button>
            <button
                @click="activeTab = 'footer'"
                :class="[
                activeTab === 'footer'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors flex items-center gap-2'
                ]"
            >
                <Menu class="w-4 h-4" />
                Footer Menu
                <span :class="[
                     activeTab === 'footer' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                    'ml-2 rounded-full px-2 py-0.5 text-xs transition-colors'
                ]">
                {{ footerMenuItems.length }}
                </span>
            </button>
            <button
                @click="activeTab = 'mobile'"
                :class="[
                activeTab === 'mobile'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors flex items-center gap-2'
                ]"
            >
                <Smartphone class="w-4 h-4" />
                Mobile Menu
                <span :class="[
                     activeTab === 'mobile' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                    'ml-2 rounded-full px-2 py-0.5 text-xs transition-colors'
                ]">
                {{ mobileMenuItems.length }}
                </span>
            </button>
            </nav>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th class="w-12 px-6 py-3"></th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">URL/Route</th>
                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <template v-for="item in parentItems" :key="item.id">
                <tr
                  draggable="true"
                  @dragstart="handleDragStart($event, item)"
                  @dragover="handleDragOver($event, item)"
                  @drop="handleDrop($event, item)"
                  @dragend="handleDragEnd"
                  :class="{ 'bg-blue-50 dark:bg-blue-900/20': dragOverItem?.id === item.id }"
                  class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group"
                >
                  <td class="px-6 py-4">
                    <button class="cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                      <GripVertical class="h-5 w-5" />
                    </button>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
                    <div class="flex items-center">
                      <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.title }}</span>
                      <span v-if="item.children && item.children.length > 0" class="ml-2 text-xs text-gray-500 font-mono">
                         ({{ item.children.length }} sub-items)
                      </span>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 font-mono">
                    {{ item.url || item.route || '-' }}
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-center">
                    <span
                      :class="[
                        item.active ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800' : 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-900/20 dark:text-gray-300 dark:border-gray-800',
                        'inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm'
                      ]"
                    >
                      {{ item.active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                      <button @click="handleEdit(item)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors" title="Edit">
                        <Edit class="w-4 h-4" />
                      </button>
                      <button @click="handleDelete(item)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete">
                        <Trash2 class="w-4 h-4" />
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
                  class="bg-gray-50/50 dark:bg-gray-900/20 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group"
                >
                  <td class="px-6 py-3">
                    <button class="cursor-move pl-6 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                      <GripVertical class="h-4 w-4" />
                    </button>
                  </td>
                  <td class="whitespace-nowrap px-6 py-3">
                    <div class="flex items-center pl-8 text-sm text-gray-600 dark:text-gray-300">
                        <div class="w-4 border-l border-b border-gray-300 dark:border-gray-600 h-4 absolute -ml-6 -mt-4 rounded-bl"></div>
                         {{ child.title }}
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-500 dark:text-gray-400 font-mono">
                    {{ child.url || child.route || '-' }}
                  </td>
                  <td class="whitespace-nowrap px-6 py-3 text-center">
                    <span
                      :class="[
                        child.active ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800' : 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-900/20 dark:text-gray-300 dark:border-gray-800',
                        'inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm'
                      ]"
                    >
                      {{ child.active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-6 py-3 text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                      <button @click="handleEdit(child)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors" title="Edit">
                        <Edit class="w-4 h-4" />
                      </button>
                      <button @click="handleDelete(child)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </template>

              <tr v-if="currentMenuItems.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                            <Menu class="w-8 h-8" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No menu items found</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Starts adding items to your {{ activeTab }} menu.</p>
                        <button
                            @click="handleCreate"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <Plus class="w-4 h-4 mr-2" />
                            Add Menu Item
                        </button>
                  </div>
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
