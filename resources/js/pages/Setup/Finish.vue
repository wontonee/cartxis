<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

// ─── Admin redirect ────────────────────────────────────────────────────────

const completing = ref(false);

const completeSetup = async () => {
    completing.value = true;
    try {
        const response = await axios.post('/setup/complete');
        if (response.data.success) {
            window.location.href = response.data.redirect;
        }
    } catch (error) {
        completing.value = false;
        alert('Failed to complete setup. Please try again.');
    }
};

// ─── Developer newsletter (optional, never blocks setup) ───────────────────

type NLState = 'idle' | 'loading' | 'success' | 'skipped';
const nlState   = ref<NLState>('idle');
const nlEmail   = ref('');
const nlName    = ref('');
const nlConsent = ref(false); // GDPR Art. 7 / Recital 32 — consent must not be pre-ticked
const nlErrors  = ref<Record<string, string[]>>({});

const subscribeNewsletter = async () => {
    nlErrors.value = {};
    nlState.value  = 'loading';
    try {
        await axios.post('/setup/newsletter-subscribe', {
            email:   nlEmail.value,
            name:    nlName.value,
            consent: nlConsent.value,
        });
        nlState.value = 'success';
    } catch (err: any) {
        if (err.response?.status === 422) {
            nlErrors.value = err.response.data.errors ?? {};
            nlState.value  = 'idle';
        } else {
            // Network / API error — show success silently (non-blocking spec)
            nlState.value = 'success';
        }
    }
};
</script>

<template>
    <Head title="Setup Complete" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6 text-white">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-3xl font-bold text-center">Setup Complete!</h1>
                <p class="text-center text-green-100 mt-2">Your store is ready to go</p>
            </div>

            <!-- Progress Bar -->
            <div class="px-8 pt-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Step 4 of 4</span>
                    <span class="text-sm font-medium text-gray-700">100%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 100%"></div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">
                        🎉 Congratulations!
                    </h2>
                    <p class="text-lg text-gray-600">
                        Your eCommerce store has been successfully set up and is ready for business.
                    </p>
                </div>

                <!-- Next Steps -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 mb-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        Next Steps
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Configure your store settings and payment methods</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Add or customize your products and categories</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Customize your storefront theme and content</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Set up shipping methods and tax rates</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Start accepting orders from customers!</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Button -->
                <div class="text-center">
                    <button
                        @click="completeSetup"
                        :disabled="completing"
                        :class="[
                            'inline-flex items-center px-8 py-4 font-semibold rounded-lg shadow-lg transform transition-all duration-200',
                            completing
                                ? 'bg-gray-400 text-gray-200 cursor-not-allowed'
                                : 'bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-700 hover:to-emerald-700 hover:scale-105'
                        ]"
                    >
                        <svg v-if="completing" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-if="completing">Finalizing Setup...</span>
                        <span v-else>Go to Admin Dashboard</span>
                        <svg v-if="!completing" class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </div>

                <!-- Developer Newsletter — optional, non-blocking -->
                <div v-if="nlState !== 'skipped'" class="mt-6 border border-gray-200 rounded-xl p-5 bg-gray-50">

                    <!-- Success state -->
                    <div v-if="nlState === 'success'" class="text-center py-2">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="font-semibold text-gray-800 text-sm">You're subscribed!</p>
                        <p class="text-xs text-gray-500 mt-1">We'll keep you updated with release notes and security advisories.</p>
                        <button @click="nlState = 'skipped'" class="mt-2 text-xs text-gray-400 hover:text-gray-600 underline">Dismiss</button>
                    </div>

                    <!-- Form (idle / loading) -->
                    <template v-else>
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">Stay in the loop</p>
                                <p class="text-xs text-gray-500 mt-0.5">Get release notes, security advisories, and developer tips — no spam. Unsubscribe any time.</p>
                            </div>
                        </div>

                        <form @submit.prevent="subscribeNewsletter" class="space-y-3">
                            <input
                                v-model="nlName"
                                type="text"
                                placeholder="Your name (optional)"
                                :disabled="nlState === 'loading'"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 bg-white"
                            />
                            <div>
                                <input
                                    v-model="nlEmail"
                                    type="email"
                                    placeholder="Your email address"
                                    required
                                    :disabled="nlState === 'loading'"
                                    class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 bg-white"
                                    :class="nlErrors.email ? 'border-red-400' : 'border-gray-300'"
                                />
                                <p v-if="nlErrors.email" class="text-xs text-red-500 mt-1">{{ nlErrors.email[0] }}</p>
                            </div>
                            <div>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input
                                        v-model="nlConsent"
                                        type="checkbox"
                                        :disabled="nlState === 'loading'"
                                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="text-xs text-gray-600 leading-snug">
                                        I agree to receive developer updates from the Cartxis Commerce team.
                                        You can unsubscribe at any time.
                                        <a href="https://cartxiscommerce.com/privacy" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">Privacy Policy</a>
                                    </span>
                                </label>
                                <p v-if="nlErrors.consent" class="text-xs text-red-500 mt-1">{{ nlErrors.consent[0] }}</p>
                            </div>
                            <div class="flex items-center justify-between pt-1">
                                <button
                                    type="submit"
                                    :disabled="nlState === 'loading'"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <svg v-if="nlState === 'loading'" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ nlState === 'loading' ? 'Subscribing...' : 'Subscribe' }}
                                </button>
                                <button
                                    type="button"
                                    @click="nlState = 'skipped'"
                                    class="text-xs text-gray-400 hover:text-gray-600 underline"
                                >
                                    No thanks, skip
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 text-center text-sm text-gray-600">
                <p>Thank you for choosing Cartxis eCommerce Platform! 🚀</p>
            </div>
        </div>
    </div>
</template>
