<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle, Shield } from 'lucide-vue-next';
import { computed } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';

const page = usePage();
const theme = computed(() => page.props.theme as any);
const siteConfig = computed(() => page.props.siteConfig as any);
const primaryColor = computed(() => theme.value?.settings?.['colors.primary'] || theme.value?.settings?.['colors.primary_color'] || theme.value?.settings?.primary_color || '#3b82f6');

const form = useForm({
    password: '',
});

const submit = () => {
    form.post('/user/confirm-password', {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <ThemeLayout>
        <Head :title="`Confirm Password - ${siteConfig?.name || 'Shop'}`" />

        <div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gray-50">
            <div class="w-full max-w-md">
                <!-- Card -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <!-- Icon -->
                    <div class="flex justify-center mb-6">
                        <div 
                            class="w-16 h-16 rounded-full flex items-center justify-center"
                            :style="{ backgroundColor: primaryColor + '20' }"
                        >
                            <Shield 
                                class="w-8 h-8"
                                :style="{ color: primaryColor }"
                            />
                        </div>
                    </div>

                    <!-- Header -->
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">
                            Confirm Password
                        </h1>
                        <p class="text-gray-600">
                            This is a secure area. Please confirm your password before continuing.
                        </p>
                    </div>

                    <!-- Confirm Password Form -->
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Password Field -->
                        <div class="space-y-2">
                            <Label for="password" class="text-sm font-medium text-gray-700">
                                Password
                            </Label>
                            <Input
                                id="password"
                                type="password"
                                v-model="form.password"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="w-full"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            class="w-full text-white font-medium py-2.5 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: primaryColor }"
                            :tabindex="2"
                            :disabled="form.processing"
                        >
                            <LoaderCircle
                                v-if="form.processing"
                                class="h-5 w-5 animate-spin mr-2"
                            />
                            {{ form.processing ? 'Confirming...' : 'Confirm' }}
                        </Button>
                    </form>
                </div>

                <!-- Security Notice -->
                <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-1">
                        Why do we need this?
                    </h3>
                    <p class="text-xs text-gray-600">
                        For your security, we require password confirmation before accessing sensitive information or performing critical actions.
                    </p>
                </div>
            </div>
        </div>
    </ThemeLayout>
</template>
