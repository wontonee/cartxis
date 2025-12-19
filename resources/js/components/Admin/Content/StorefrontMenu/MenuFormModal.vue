<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';

interface MenuItem {
  id: number;
  title: string;
  icon: string | null;
  route: string | null;
  url: string | null;
  parent_id: number | null;
  menu_type: string;
  order: number;
  active: boolean;
}

interface Props {
  show: boolean;
  menuItem?: MenuItem | null;
  menuType: 'header' | 'footer' | 'mobile';
  parentOptions: MenuItem[];
}

interface Emits {
  (e: 'update:show', value: boolean): void;
}

const props = withDefaults(defineProps<Props>(), {
  menuItem: null,
});

const emit = defineEmits<Emits>();

interface MenuFormData {
  title: string;
  icon: string;
  route: string;
  url: string;
  parent_id: number | null;
  menu_type: string;
  order: number;
  active: boolean;
}

const form = ref<MenuFormData>({
  title: '',
  icon: '',
  route: '',
  url: '',
  parent_id: null,
  menu_type: props.menuType,
  order: 0,
  active: true,
});

const isSubmitting = ref(false);

const isEditMode = computed(() => props.menuItem !== null);

// Watch show prop to reset form
watch(() => props.show, (newValue) => {
  if (newValue) {
    if (props.menuItem) {
      form.value = {
        title: props.menuItem.title,
        icon: props.menuItem.icon || '',
        route: props.menuItem.route || '',
        url: props.menuItem.url || '',
        parent_id: props.menuItem.parent_id,
        menu_type: props.menuItem.menu_type,
        order: props.menuItem.order,
        active: props.menuItem.active,
      };
    } else {
      form.value = {
        title: '',
        icon: '',
        route: '',
        url: '',
        parent_id: null,
        menu_type: props.menuType,
        order: 0,
        active: true,
      };
    }
  }
});

const close = () => {
  emit('update:show', false);
};

const submit = () => {
  if (isSubmitting.value) return;
  
  isSubmitting.value = true;

  const url = isEditMode.value
    ? `/admin/content/storefront-menus/${props.menuItem!.id}`
    : '/admin/content/storefront-menus';

  const method = isEditMode.value ? 'put' : 'post';

  router[method](url, form.value, {
    onSuccess: () => {
      close();
      isSubmitting.value = false;
    },
    onError: () => {
      isSubmitting.value = false;
    },
  });
};

// Basic icon options
const iconOptions = [
  { value: '', label: 'No Icon' },
  { value: 'home', label: 'Home' },
  { value: 'shopping-bag', label: 'Shopping Bag' },
  { value: 'tag', label: 'Tag' },
  { value: 'percent', label: 'Percent' },
  { value: 'info', label: 'Info' },
  { value: 'user', label: 'User' },
  { value: 'phone', label: 'Phone' },
  { value: 'mail', label: 'Mail' },
  { value: 'help-circle', label: 'Help' },
  { value: 'file-text', label: 'Document' },
];
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500/75 dark:bg-gray-900/75 p-4"
        @click.self="close"
      >
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="show"
            class="relative w-full max-w-2xl rounded-lg bg-white shadow-xl dark:bg-gray-800"
            @click.stop
          >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ isEditMode ? 'Edit Menu Item' : 'Add Menu Item' }}
              </h2>
              <button
                @click="close"
                class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
              >
                <X class="h-5 w-5" />
              </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4 px-6 py-4">
              <!-- Title -->
              <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Title <span class="text-red-500">*</span>
                </label>
                <input
                  id="title"
                  v-model="form.title"
                  type="text"
                  required
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
                  placeholder="e.g., Shop All, About Us"
                />
              </div>

              <!-- URL and Route -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    URL
                  </label>
                  <input
                    id="url"
                    v-model="form.url"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
                    placeholder="/products"
                  />
                </div>

                <div>
                  <label for="route" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Route
                  </label>
                  <input
                    id="route"
                    v-model="form.route"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
                    placeholder="shop.products"
                  />
                </div>
              </div>

              <!-- Icon and Parent -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Icon
                  </label>
                  <select
                    id="icon"
                    v-model="form.icon"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  >
                    <option v-for="option in iconOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </option>
                  </select>
                </div>

                <div>
                  <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Parent Menu
                  </label>
                  <select
                    id="parent_id"
                    v-model="form.parent_id"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  >
                    <option :value="null">None (Top Level)</option>
                    <option v-for="option in parentOptions" :key="option.id" :value="option.id">
                      {{ option.title }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Order -->
              <div>
                <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Order
                </label>
                <input
                  id="order"
                  v-model.number="form.order"
                  type="number"
                  min="0"
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
                  placeholder="0"
                />
              </div>

              <!-- Active -->
              <div class="flex items-center">
                <input
                  id="active"
                  v-model="form.active"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                />
                <label for="active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                  Active
                </label>
              </div>
            </form>

            <!-- Footer -->
            <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
              <button
                type="button"
                @click="close"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
              >
                Cancel
              </button>
              <button
                type="button"
                @click="submit"
                :disabled="isSubmitting"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:focus:ring-offset-gray-800"
              >
                {{ isEditMode ? 'Update' : 'Create' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
