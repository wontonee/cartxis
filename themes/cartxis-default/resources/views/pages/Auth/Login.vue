<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Eye, EyeOff, LoaderCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';

const page = usePage();
const theme = computed(() => page.props.theme as any);
const siteConfig = computed(() => page.props.siteConfig as any);
const primaryColor = computed(() => theme.value?.settings?.['colors.primary'] || theme.value?.settings?.['colors.primary_color'] || theme.value?.settings?.primary_color || '#3b82f6');

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const showPassword = ref(false);
</script>

<template>
    <ThemeLayout>
        <Head title="Login" />

        <div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gray-50">
            <div class="w-full max-w-md">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        Welcome Back
                    </h1>
                    <p class="text-gray-600">
                        Log in to your account to continue shopping
                    </p>
                </div>

                <!-- Card -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <!-- Status Message -->
                    <div
                        v-if="status"
                        class="mb-6 p-3 text-center text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-md"
                    >
                        {{ status }}
                    </div>

                    <!-- Login Form -->
                    <Form
                        v-bind="AuthenticatedSessionController.store.form()"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="space-y-6"
                    >
                        <!-- Email Field -->
                        <div class="space-y-2">
                            <Label for="email" class="text-sm font-medium text-gray-700">
                                Email Address
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-sm font-medium text-gray-700">
                                    Password
                                </Label>
                                <TextLink
                                    v-if="canResetPassword"
                                    href="/forgot-password"
                                    class="text-sm hover:underline"
                                    :style="{ color: primaryColor }"
                                    :tabindex="5"
                                >
                                    Forgot password?
                                </TextLink>
                            </div>
                            <div class="relative">
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    class="w-full pr-10"
                                />
                                <button
                                    type="button"
                                    tabindex="-1"
                                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                    @click="showPassword = !showPassword"
                                >
                                    <EyeOff v-if="showPassword" class="h-4 w-4" />
                                    <Eye v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <InputError :message="errors.password" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center">
                            <Label for="remember" class="flex items-center space-x-2 cursor-pointer">
                                <Checkbox id="remember" name="remember" :tabindex="3" />
                                <span class="text-sm text-gray-700">Remember me for 30 days</span>
                            </Label>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            class="w-full text-white font-medium py-2.5 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: primaryColor }"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                        >
                            <LoaderCircle
                                v-if="processing"
                                class="h-5 w-5 animate-spin mr-2"
                            />
                            {{ processing ? 'Logging in...' : 'Log In' }}
                        </Button>
                    </Form>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">or</span>
                        </div>
                    </div>

                    <!-- Register Link -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            Don't have an account?
                            <TextLink
                                href="/register"
                                class="font-medium hover:underline"
                                :style="{ color: primaryColor }"
                                :tabindex="6"
                            >
                                Create one now
                            </TextLink>
                        </p>
                    </div>
                </div>

                <!-- Back to Shop -->
                <div class="text-center mt-6">
                    <TextLink
                        href="/"
                        class="text-sm text-gray-600 hover:underline inline-flex items-center"
                        :tabindex="7"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to shop
                    </TextLink>
                </div>
            </div>
        </div>
    </ThemeLayout>
</template>
