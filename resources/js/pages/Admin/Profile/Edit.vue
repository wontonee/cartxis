<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { User } from 'lucide-vue-next';

interface Props {
    user: {
        id: number;
        name: string;
        email: string;
        profile_photo_path?: string | null;
    };
}

const props = defineProps<Props>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    profile_photo: null as File | null,
});

const photoPreview = ref<string | null>(null);

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
        form.profile_photo = file;
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post('/admin/profile', {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
        },
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Profile Settings" />

        <div class="max-w-4xl">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profile Settings</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Update your profile information and photo
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Profile Photo -->
                    <div class="space-y-2">
                        <Label for="profile_photo">Profile Photo</Label>
                        <div class="flex items-center gap-6">
                            <!-- Current/Preview Photo -->
                            <div class="flex-shrink-0">
                                <div v-if="photoPreview" class="w-24 h-24 rounded-full overflow-hidden border-2 border-gray-200 dark:border-gray-600">
                                    <img :src="photoPreview" alt="Preview" class="w-full h-full object-cover" />
                                </div>
                                <div v-else-if="user.profile_photo_path" class="w-24 h-24 rounded-full overflow-hidden border-2 border-gray-200 dark:border-gray-600">
                                    <img :src="`/storage/${user.profile_photo_path}`" alt="Current photo" class="w-full h-full object-cover" />
                                </div>
                                <div v-else class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center border-2 border-gray-200 dark:border-gray-600">
                                    <User class="w-12 h-12 text-gray-400" />
                                </div>
                            </div>
                            
                            <!-- File Input -->
                            <div class="flex-1">
                                <Input
                                    id="profile_photo"
                                    type="file"
                                    accept="image/*"
                                    @change="handleFileChange"
                                    class="cursor-pointer"
                                />
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    JPG, PNG or GIF (max. 2MB)
                                </p>
                            </div>
                        </div>
                        <p v-if="form.errors.profile_photo" class="text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.profile_photo }}
                        </p>
                    </div>

                    <!-- Name -->
                    <div class="space-y-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full"
                            placeholder="Your name"
                        />
                        <p v-if="form.errors.name" class="text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full"
                            placeholder="your.email@example.com"
                        />
                        <p v-if="form.errors.email" class="text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex justify-center items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="form.recentlySuccessful"
                                class="text-sm text-green-600 dark:text-green-400"
                            >
                                Saved successfully!
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
