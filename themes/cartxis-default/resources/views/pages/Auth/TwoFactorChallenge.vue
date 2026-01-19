<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle, Smartphone, Key } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';

const page = usePage();
const theme = computed(() => page.props.theme as any);
const siteConfig = computed(() => page.props.siteConfig as any);
const primaryColor = computed(() => theme.value?.settings?.primary_color || '#3b82f6');

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggleRecovery = () => {
    recovery.value = !recovery.value;
    form.clearErrors();
    form.reset();
};

const submit = () => {
    form.post('/two-factor-challenge');
};
</script>

<template>
    <ThemeLayout>
        <Head :title="`Two-Factor Authentication - ${siteConfig?.name || 'Shop'}`" />

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
                            <component 
                                :is="recovery ? Key : Smartphone"
                                class="w-8 h-8"
                                :style="{ color: primaryColor }"
                            />
                        </div>
                    </div>

                    <!-- Header -->
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">
                            Two-Factor Authentication
                        </h1>
                        <p class="text-gray-600">
                            <template v-if="!recovery">
                                Please confirm access to your account by entering the authentication code provided by your authenticator application.
                            </template>
                            <template v-else>
                                Please confirm access to your account by entering one of your emergency recovery codes.
                            </template>
                        </p>
                    </div>

                    <!-- Two-Factor Form -->
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Code Input -->
                        <div v-if="!recovery" class="space-y-2">
                            <Label for="code" class="text-sm font-medium text-gray-700">
                                Authentication Code
                            </Label>
                            <Input
                                id="code"
                                type="text"
                                inputmode="numeric"
                                v-model="form.code"
                                required
                                autofocus
                                autocomplete="one-time-code"
                                placeholder="000000"
                                class="w-full text-center text-2xl tracking-widest"
                                maxlength="6"
                            />
                            <InputError :message="form.errors.code" />
                        </div>

                        <!-- Recovery Code Input -->
                        <div v-else class="space-y-2">
                            <Label for="recovery_code" class="text-sm font-medium text-gray-700">
                                Recovery Code
                            </Label>
                                          <Input
                id="recovery_code"
                type="text"
                name="recovery_code"
                v-model="form.recovery_code"
                autocomplete="one-time-code"
                class="w-full"
              />
                            <InputError :message="form.errors.recovery_code" />
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            class="w-full text-white font-medium py-2.5 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: primaryColor }"
                            :disabled="form.processing"
                        >
                            <LoaderCircle
                                v-if="form.processing"
                                class="h-5 w-5 animate-spin mr-2"
                            />
                            {{ form.processing ? 'Verifying...' : 'Verify' }}
                        </Button>
                    </form>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">or</span>
                        </div>
                    </div>

                    <!-- Toggle Recovery -->
                    <div class="text-center">
                        <button
                            type="button"
                            @click="toggleRecovery"
                            class="text-sm hover:underline"
                            :style="{ color: primaryColor }"
                        >
                            <template v-if="!recovery">
                                Use a recovery code
                            </template>
                            <template v-else>
                                Use an authentication code
                            </template>
                        </button>
                    </div>
                </div>

                <!-- Help Text -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-blue-900 mb-2">
                        Need help?
                    </h3>
                    <p class="text-xs text-blue-800">
                        If you've lost access to your authenticator device, use one of your recovery codes to log in. Recovery codes can be found in your account security settings.
                    </p>
                </div>
            </div>
        </div>
    </ThemeLayout>
</template>
