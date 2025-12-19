<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import '@admin/css/styles.css'
import admin from '@/routes/admin'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)
const currentYear = computed(() => new Date().getFullYear())

const submit = () => {
  form.post(admin.login.store.url(), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Admin Login" />

  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 p-4">
    <div class="w-full max-w-6xl grid lg:grid-cols-2 gap-8 items-center">
      <!-- Branding Section -->
      <div class="hidden lg:flex flex-col justify-center space-y-8 p-12">
        <div class="space-y-4">
          <div class="flex items-center gap-3 mb-8">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <span class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
              Vortex
            </span>
          </div>
          
          <h1 class="text-4xl font-bold text-gray-900 dark:text-white leading-tight">
            Welcome to<br />Admin Dashboard
          </h1>
          
          <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
            Powerful eCommerce management platform designed for modern businesses.
            Control your entire online store from one unified interface.
          </p>
        </div>
        
        <div class="grid grid-cols-2 gap-4 pt-8">
          <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">24/7</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Support</div>
          </div>
          <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">99.9%</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Uptime</div>
          </div>
        </div>
      </div>

      <!-- Login Form Section -->
      <div class="w-full max-w-md mx-auto lg:mx-0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 lg:p-10">
          <!-- Mobile Logo -->
          <div class="lg:hidden flex items-center gap-3 mb-8 justify-center">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
              Vortex
            </span>
          </div>

          <form @submit.prevent="submit" class="space-y-6">
            <!-- Header -->
            <div class="text-center lg:text-left">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Sign in to Admin
              </h2>
              <p class="text-gray-600 dark:text-gray-400">
                Enter your credentials to access the dashboard
              </p>
            </div>

            <!-- Email Input -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Email Address
              </label>
              <input
                v-model="form.email"
                type="email"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                :class="{ 'border-red-500 focus:ring-red-500': form.errors.email }"
                placeholder="admin@wontonee.com"
                autofocus
                required
              />
              <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                {{ form.errors.email }}
              </p>
            </div>

            <!-- Password Input -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Password
              </label>
              <div class="relative">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  class="w-full px-4 py-3 pr-12 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  :class="{ 'border-red-500 focus:ring-red-500': form.errors.password }"
                  placeholder="Enter your password"
                  required
                />
                <button
                  @click="showPassword = !showPassword"
                  type="button"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                >
                  <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
              <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                {{ form.errors.password }}
              </p>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
              <input
                v-model="form.remember"
                type="checkbox"
                id="remember"
                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2"
              />
              <label for="remember" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                Remember me for 30 days
              </label>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
              :disabled="form.processing"
            >
              <span v-if="!form.processing" class="flex items-center justify-center gap-2">
                <span>Sign In</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </span>
              <span v-else class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Signing in...</span>
              </span>
            </button>
          </form>
          
          <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
            <p>© {{ currentYear }} Vortex Commerce. All rights reserved.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
