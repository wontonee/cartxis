<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { X } from 'lucide-vue-next'
import * as menuRoutes from '@/routes/admin/system/menus/index'
import IconPicker from '@/components/Admin/System/Menu/IconPicker.vue'
import type { MenuItem, MenuFormData } from '@/types/admin-menu'

interface Props {
    show: boolean
    menuItem?: MenuItem | null
    parentOptions: MenuItem[]
}

const props = defineProps<Props>()
const emit = defineEmits<{
    'update:show': [value: boolean]
}>()

const form = ref<MenuFormData>({
    key: '',
    title: '',
    icon: null,
    route: null,
    url: null,
    parent_id: null,
    order: 0,
    permission: null,
    location: 'admin',
    active: true,
})

const errors = ref<Record<string, string>>({})
const isSubmitting = ref(false)

// Reset form when modal opens/closes
watch(() => props.show, (show) => {
    if (show && props.menuItem) {
        // Edit mode
        form.value = {
            key: props.menuItem.key || '',
            title: props.menuItem.title,
            icon: props.menuItem.icon,
            route: props.menuItem.route,
            url: props.menuItem.url,
            parent_id: props.menuItem.parent_id,
            order: props.menuItem.order,
            permission: props.menuItem.permission,
            location: props.menuItem.location,
            active: props.menuItem.active,
        }
    } else if (show) {
        // Create mode
        form.value = {
            key: '',
            title: '',
            icon: null,
            route: null,
            url: null,
            parent_id: null,
            order: 0,
            permission: null,
            location: 'admin',
            active: true,
        }
    }
    errors.value = {}
})

const isEditMode = computed(() => !!props.menuItem)

const close = () => {
    emit('update:show', false)
}

const submit = () => {
    errors.value = {}
    isSubmitting.value = true

    const url = isEditMode.value
        ? menuRoutes.update(props.menuItem!.id)
        : menuRoutes.store()

    const method = isEditMode.value ? 'put' : 'post'

    router[method](url, form.value, {
        onSuccess: () => {
            close()
        },
        onError: (errs) => {
            errors.value = errs
        },
        onFinish: () => {
            isSubmitting.value = false
        },
    })
}
</script>

<template>
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
                v-if="show"
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-500/75 dark:bg-gray-900/75 p-4 sm:items-center"
                @click="close"
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
                        v-if="show"
                        class="relative flex max-h-[calc(100vh-2rem)] w-full max-w-2xl flex-col rounded-lg bg-white shadow-xl dark:bg-gray-800"
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
                        <form @submit.prevent="submit" class="flex-1 space-y-4 overflow-y-auto px-6 py-4">
                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Title <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.title }"
                                    required
                                />
                                <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
                            </div>

                            <!-- Key -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Key (Optional)
                                </label>
                                <input
                                    v-model="form.key"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="e.g., dashboard, catalog, settings"
                                />
                                <p class="mt-1 text-xs text-gray-500">Unique identifier for programmatic access</p>
                            </div>

                            <!-- Icon -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Icon
                                </label>
                                <IconPicker v-model="form.icon" />
                            </div>

                            <!-- Route / URL -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Route
                                    </label>
                                    <input
                                        v-model="form.route"
                                        type="text"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': errors.route }"
                                        placeholder="admin.dashboard"
                                    />
                                    <p v-if="errors.route" class="mt-1 text-sm text-red-600">{{ errors.route }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        URL
                                    </label>
                                    <input
                                        v-model="form.url"
                                        type="text"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="/admin/dashboard"
                                    />
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">Provide either Route or URL</p>

                            <!-- Parent -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Parent Menu
                                </label>
                                <select
                                    v-model="form.parent_id"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option :value="null">None (Top Level)</option>
                                    <option
                                        v-for="option in parentOptions.filter(p => !menuItem || p.id !== menuItem.id)"
                                        :key="option.id"
                                        :value="option.id"
                                    >
                                        {{ option.title }}
                                    </option>
                                </select>
                            </div>

                            <!-- Order & Permission -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Order
                                    </label>
                                    <input
                                        v-model.number="form.order"
                                        type="number"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Permission (Optional)
                                    </label>
                                    <input
                                        v-model="form.permission"
                                        type="text"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="view.dashboard"
                                    />
                                </div>
                            </div>

                            <!-- Active -->
                            <div class="flex items-center">
                                <input
                                    v-model="form.active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Active
                                </label>
                            </div>
                        </form>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                            <button
                                type="button"
                                @click="close"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                @click="submit"
                                :disabled="isSubmitting"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ isSubmitting ? 'Saving...' : (isEditMode ? 'Update' : 'Create') }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
