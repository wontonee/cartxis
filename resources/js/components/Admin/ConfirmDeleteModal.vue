<script setup lang="ts">
import { X, AlertTriangle } from 'lucide-vue-next'

interface Props {
    show: boolean
    title: string
    message: string
}

defineProps<Props>()
const emit = defineEmits<{
    'update:show': [value: boolean]
    'confirm': []
}>()

const close = () => {
    emit('update:show', false)
}

const confirm = () => {
    emit('confirm')
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
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
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
                        class="relative w-full max-w-md rounded-lg bg-white shadow-xl dark:bg-gray-800"
                        @click.stop
                    >
                        <!-- Header -->
                        <div class="flex items-center gap-3 border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                                <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="flex-1">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Delete {{ title }}
                                </h2>
                            </div>
                            <button
                                @click="close"
                                class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-4">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                {{ message }}
                            </p>
                        </div>

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
                                type="button"
                                @click="confirm"
                                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
