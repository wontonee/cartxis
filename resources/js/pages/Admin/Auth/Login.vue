<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import '@admin/css/styles.css'
import '@admin/vendors/keenicons/styles.bundle.css'
import admin from '@/routes/admin'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)

const submit = () => {
  form.post(admin.login.store.url(), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Admin Login" />

  <div class="antialiased flex h-screen text-base text-foreground bg-background">
    <div class="grid lg:grid-cols-2 grow">
      <!-- Login Form Section -->
      <div class="flex justify-center items-center p-8 lg:p-10 order-2 lg:order-1">
        <div class="kt-card max-w-[370px] w-full">
          <form @submit.prevent="submit" class="kt-card-content flex flex-col gap-5 p-10">
            <!-- Header -->
            <div class="text-center mb-2.5">
              <h3 class="text-lg font-medium text-mono leading-none mb-2.5">
                Admin Sign In
              </h3>
              <div class="flex items-center justify-center font-medium">
                <span class="text-sm text-secondary-foreground">
                  Secure access to Vortex Commerce Admin Panel
                </span>
              </div>
            </div>

            <!-- Email Input -->
            <div class="flex flex-col gap-1">
              <label class="kt-form-label font-normal text-mono">
                Email
              </label>
              <input
                v-model="form.email"
                class="kt-input"
                :class="{ 'border-red-500': form.errors.email }"
                placeholder="admin@vortex.com"
                type="email"
                autofocus
                required
              />
              <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">
                {{ form.errors.email }}
              </div>
            </div>

            <!-- Password Input -->
            <div class="flex flex-col gap-1">
              <div class="flex items-center justify-between gap-1">
                <label class="kt-form-label font-normal text-mono">
                  Password
                </label>
              </div>
              <div class="kt-input" data-kt-toggle-password="true">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Enter Password"
                  class="flex-1 border-0 p-0 focus:ring-0"
                  required
                />
                <button
                  @click="showPassword = !showPassword"
                  class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5"
                  type="button"
                >
                  <span v-if="!showPassword">
                    <i class="ki-filled ki-eye text-muted-foreground"></i>
                  </span>
                  <span v-else>
                    <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                  </span>
                </button>
              </div>
              <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">
                {{ form.errors.password }}
              </div>
            </div>

            <!-- Remember Me -->
            <label class="kt-label">
              <input
                v-model="form.remember"
                class="kt-checkbox kt-checkbox-sm"
                type="checkbox"
              />
              <span class="kt-checkbox-label">
                Remember me
              </span>
            </label>

            <!-- Submit Button -->
            <button
              type="submit"
              class="kt-btn kt-btn-primary flex justify-center grow"
              :disabled="form.processing"
            >
              <span v-if="!form.processing">Sign In</span>
              <span v-else>Signing in...</span>
            </button>
          </form>
        </div>
      </div>

      <!-- Branded Section -->
      <div class="lg:rounded-xl lg:border lg:border-border lg:m-5 order-1 lg:order-2 bg-top xxl:bg-center xl:bg-cover bg-no-repeat branded-bg">
        <div class="flex flex-col p-8 lg:p-16 gap-4">
          <div class="flex items-center gap-2">
            <div class="size-10 shrink-0">
              <img src="/resources/admin/media/app/mini-logo.svg" class="h-[28px] max-w-none" alt="Vortex Logo" />
            </div>
          </div>
          <div class="flex flex-col gap-3">
            <h3 class="text-2xl font-semibold text-mono">
              Vortex Commerce Admin
            </h3>
            <div class="text-base font-medium text-secondary-foreground">
              A robust authentication gateway ensuring
              <br />
              secure
              <span class="text-mono font-semibold">
                efficient management access
              </span>
              to your
              <br />
              eCommerce platform.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.branded-bg {
  background-image: url('@admin/media/images/2600x1600/1.png');
}

.dark .branded-bg {
  background-image: url('@admin/media/images/2600x1600/1-dark.png');
}
</style>
