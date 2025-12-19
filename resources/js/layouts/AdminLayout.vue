<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import admin from '@/routes/admin'
import Toast from '@/components/Toast.vue'
import { useMenuIcons } from '@/composables/useMenuIcons'
import {
  Menu,
  ChevronDown,
  LogOut,
  Search,
  Bell,
  ChevronLeft,
  ChevronRight,
  User,
  UserCircle,
  Settings
} from 'lucide-vue-next'

defineProps<{
  title?: string
}>()

const page = usePage()
const { getIcon } = useMenuIcons()
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const userMenuOpen = ref(false)
const hoveredMenuId = ref<number | null>(null)
const popoverPosition = ref({ top: 0 })

// Track which parent menus are open
const openMenus = ref<Set<number>>(new Set())

// LocalStorage keys for persisting state
const MENU_STATE_KEY = 'admin_menu_state'
const SIDEBAR_COLLAPSED_KEY = 'admin_sidebar_collapsed'

// Load menu state from localStorage
const loadMenuState = (): Set<number> => {
  try {
    const saved = localStorage.getItem(MENU_STATE_KEY)
    if (saved) {
      const parsed = JSON.parse(saved)
      return new Set(parsed)
    }
  } catch (error) {
    console.error('Error loading menu state:', error)
  }
  return new Set()
}

// Load sidebar collapsed state from localStorage
const loadSidebarCollapsed = (): boolean => {
  try {
    const saved = localStorage.getItem(SIDEBAR_COLLAPSED_KEY)
    return saved === 'true'
  } catch (error) {
    console.error('Error loading sidebar state:', error)
  }
  return false
}

// Save menu state to localStorage
const saveMenuState = () => {
  try {
    const state = Array.from(openMenus.value)
    localStorage.setItem(MENU_STATE_KEY, JSON.stringify(state))
  } catch (error) {
    console.error('Error saving menu state:', error)
  }
}

// Get menu items from shared data
const menuItems = computed(() => {
  return page.props.menu?.admin || []
})

// Helper function to check if menu item is active
const isActive = (item: any) => {
  if (!item || !item.full_url) return false
  
  try {
    const currentUrl = page.url
    const menuUrl = item.full_url
    
    // Strip query strings for comparison (to handle pagination, filters, etc.)
    const currentPath = currentUrl.split('?')[0]
    const menuPath = menuUrl.split('?')[0]
    
    // Remove trailing slashes for comparison
    const cleanCurrentPath = currentPath.replace(/\/$/, '')
    const cleanMenuPath = menuPath.replace(/\/$/, '')
    
    // Exact match (most common case)
    if (cleanCurrentPath === cleanMenuPath) return true
    
    // For resource routes, check if current URL starts with menu URL AND
    // has a valid resource pattern (ID or action like /create, /edit)
    // This prevents /admin/customers from matching /admin/customers/groups
    if (cleanCurrentPath.startsWith(cleanMenuPath + '/')) {
      const remainder = cleanCurrentPath.substring(cleanMenuPath.length + 1)
      // Only match if remainder is a number (resource ID) or standard actions
      // Don't match other text segments like "groups", "reports", etc.
      return /^(\d+|create|edit|show)/.test(remainder)
    }
    
    return false
  } catch (error) {
    console.error('Error in isActive:', error)
    return false
  }
}

// Check if any child is active
const hasActiveChild = (item: any): boolean => {
  if (item.children && item.children.length > 0) {
    return item.children.some((child: any) => 
      isActive(child) || hasActiveChild(child)
    )
  }
  return false
}

// Initialize open menus based on active routes and localStorage
const initializeOpenMenus = () => {
  // First, load saved state from localStorage
  const savedState = loadMenuState()
  
  // Clear current state
  openMenus.value.clear()
  
  // Merge saved state with active routes
  menuItems.value.forEach((item: any) => {
    // Always open menus with active children
    if (hasActiveChild(item)) {
      openMenus.value.add(item.id)
    } 
    // Also restore previously opened menus from localStorage
    else if (savedState.has(item.id)) {
      openMenus.value.add(item.id)
    }
  })
  
  // Save the merged state
  saveMenuState()
}

// Call on mount
onMounted(() => {
  // Load sidebar collapsed state from localStorage
  sidebarCollapsed.value = loadSidebarCollapsed()
  
  // Load menu state from localStorage
  openMenus.value = loadMenuState()
  
  initializeOpenMenus()
  
  // Listen for Inertia navigation events - use 'before' event
  router.on('before', () => {
    // Will re-initialize after nextTick when page data updates
    nextTick(() => {
      initializeOpenMenus()
    })
  })
  
  // Also listen for 'finish' as backup
  router.on('finish', () => {
    nextTick(() => {
      initializeOpenMenus()
    })
  })
})

// Also watch URL changes as backup
watch(() => page.url, () => {
  nextTick(() => {
    initializeOpenMenus()
  })
}, { immediate: false })

const toggleMenu = (itemId: number) => {
  if (openMenus.value.has(itemId)) {
    openMenus.value.delete(itemId)
  } else {
    openMenus.value.add(itemId)
  }
  // Persist menu state to localStorage after toggle
  saveMenuState()
}

const isMenuOpen = (itemId: number) => {
  return openMenus.value.has(itemId)
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  // Persist sidebar collapsed state
  try {
    localStorage.setItem(SIDEBAR_COLLAPSED_KEY, String(sidebarCollapsed.value))
  } catch (error) {
    console.error('Error saving sidebar state:', error)
  }
  // Close all submenus when sidebar is collapsed
  if (sidebarCollapsed.value) {
    openMenus.value.clear()
    saveMenuState()
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 bg-gray-900 transform transition-all duration-200 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'w-20' : 'w-64'
      ]"
    >
      <!-- Logo -->
      <div class="flex items-center justify-between h-16 px-6 border-b border-gray-800">
        <Link :href="admin.dashboard.url()" class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="text-white font-bold text-xl">V</span>
          </div>
          <span v-if="!sidebarCollapsed" class="text-white font-semibold text-lg transition-opacity duration-200">Vortex</span>
        </Link>
        
        <!-- Desktop Toggle Button -->
        <button
          @click="toggleSidebar"
          class="hidden lg:flex items-center justify-center w-8 h-8 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
        >
          <ChevronLeft v-if="!sidebarCollapsed" class="w-5 h-5" />
          <ChevronRight v-if="sidebarCollapsed" class="w-5 h-5" />
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 h-[calc(100vh-4rem)] overflow-visible">
        <div class="px-4 py-6 space-y-1 overflow-y-auto h-full">
        <!-- Dynamic Menu Items -->
        <template v-for="item in menuItems" :key="item.id">
          <!-- Parent Menu without children (Direct Link) -->
          <Link
            v-if="!item.children || item.children.length === 0"
            :href="item.full_url || '#'"
            :class="[
              'flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
              { 'bg-blue-600 text-white': isActive(item) },
              sidebarCollapsed && 'justify-center'
            ]"
            :title="sidebarCollapsed ? item.title : ''"
          >
            <component 
              :is="getIcon(item.icon)" 
              v-if="item.icon"
              class="w-5 h-5 flex-shrink-0" 
              :class="{ 'mr-3': !sidebarCollapsed }" 
            />
            <span v-if="!sidebarCollapsed">{{ item.title }}</span>
          </Link>

          <!-- Parent Menu with children (Expandable) -->
          <div 
            v-else 
            class="relative menu-item-wrapper"
            @mouseenter="(e) => { if (sidebarCollapsed) { hoveredMenuId = item.id; popoverPosition.top = (e.currentTarget as HTMLElement).getBoundingClientRect().top } }"
            @mouseleave="sidebarCollapsed && (hoveredMenuId = null)"
            :data-menu-id="item.id"
          >
            <button
              @click="!sidebarCollapsed && toggleMenu(item.id)"
              :class="[
                'w-full flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
                sidebarCollapsed && 'justify-center'
              ]"
              :title="sidebarCollapsed ? item.title : ''"
            >
              <component 
                :is="getIcon(item.icon)" 
                v-if="item.icon"
                class="w-5 h-5 flex-shrink-0" 
                :class="{ 'mr-3': !sidebarCollapsed }" 
              />
              <span v-if="!sidebarCollapsed" class="flex-1 text-left">{{ item.title }}</span>
              <ChevronDown 
                v-if="!sidebarCollapsed" 
                :class="['w-4 h-4 transition-transform', isMenuOpen(item.id) && 'rotate-180']" 
              />
            </button>
            
            <!-- Child Menu Items (expanded when sidebar is open) -->
            <div v-if="!sidebarCollapsed" v-show="isMenuOpen(item.id)" class="ml-4 mt-1 space-y-1">
              <Link 
                v-for="child in item.children" 
                :key="child.id"
                :href="child.full_url || '#'" 
                :class="[
                  'flex items-center px-4 py-2 rounded-lg transition-colors cursor-pointer',
                  isActive(child)
                    ? 'bg-blue-600 text-white' 
                    : 'text-gray-400 hover:text-white hover:bg-gray-800'
                ]"
              >
                <component 
                  :is="getIcon(child.icon)" 
                  v-if="child.icon"
                  class="w-4 h-4 mr-3" 
                />
                <span class="text-sm">{{ child.title }}</span>
              </Link>
            </div>

            <!-- Hover Popover for collapsed sidebar -->
            <Teleport to="body">
              <Transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <div 
                  v-if="sidebarCollapsed && hoveredMenuId === item.id"
                  class="fixed left-20 w-56 bg-gray-800 rounded-lg shadow-xl border border-gray-700 py-2 z-[60]"
                  :style="{ top: `${popoverPosition.top}px` }"
                  @mouseenter="hoveredMenuId = item.id"
                  @mouseleave="hoveredMenuId = null"
                >
                  <div class="px-3 py-2 border-b border-gray-700">
                    <p class="text-sm font-medium text-white">{{ item.title }}</p>
                  </div>
                  <Link 
                    v-for="child in item.children" 
                    :key="child.id"
                    :href="child.full_url || '#'" 
                    :class="[
                      'flex items-center px-4 py-2 text-sm transition-colors cursor-pointer',
                      isActive(child)
                        ? 'bg-blue-600 text-white' 
                        : 'text-gray-300 hover:text-white hover:bg-gray-700'
                    ]"
                  >
                    <component 
                      :is="getIcon(child.icon)" 
                      v-if="child.icon"
                      class="w-4 h-4 mr-3" 
                    />
                    <span>{{ child.title }}</span>
                  </Link>
                </div>
              </Transition>
            </Teleport>
          </div>
        </template>
        </div>
      </nav>
    </aside>

    <!-- Main Content -->
    <div :class="['transition-all duration-200', sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64']">
      <!-- Header -->
      <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between h-16">
            <!-- Mobile menu button -->
            <button
              @click="sidebarOpen = !sidebarOpen"
              class="lg:hidden text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300"
            >
              <Menu class="w-6 h-6" />
            </button>

            <!-- Page title -->
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
              {{ title || 'Dashboard' }}
            </h1>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
              <!-- Search -->
              <button class="text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                <Search class="w-5 h-5" />
              </button>

              <!-- Notifications -->
              <button class="relative text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                <Bell class="w-5 h-5" />
                <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-gray-800"></span>
              </button>

              <!-- User Dropdown Menu -->
              <div class="relative">
                <button
                  @click="userMenuOpen = !userMenuOpen"
                  class="flex items-center space-x-3 focus:outline-none"
                >
                  <div class="text-right hidden sm:block">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ page.props.auth?.user?.name || 'Admin' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      {{ page.props.auth?.user?.email }}
                    </div>
                  </div>
                  <div v-if="page.props.auth?.user?.profile_photo_path" class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-200 dark:border-gray-600">
                    <img :src="`/storage/${page.props.auth.user.profile_photo_path}`" :alt="page.props.auth.user.name" class="w-full h-full object-cover" />
                  </div>
                  <div v-else class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    <UserCircle class="w-6 h-6" />
                  </div>
                </button>

                <!-- Dropdown Menu -->
                <Transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <div
                    v-show="userMenuOpen"
                    class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1"
                  >
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ page.props.auth?.user?.name || 'Admin' }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ page.props.auth?.user?.email }}
                      </p>
                    </div>
                    
                    <Link href="/admin/profile" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                      <User class="w-4 h-4 mr-3" />
                      Update Profile Photo
                    </Link>
                    
                    <Link href="/admin/password" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                      <Settings class="w-4 h-4 mr-3" />
                      Change Password
                    </Link>
                    
                    <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                    
                    <Link
                      :href="admin.logout.url()"
                      method="post"
                      as="button"
                      class="w-full flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                      <LogOut class="w-4 h-4 mr-3" />
                      Logout
                    </Link>
                  </div>
                </Transition>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>

    <!-- Mobile sidebar overlay -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="fixed inset-0 z-40 bg-gray-600/75 lg:hidden"
    ></div>

    <!-- Toast Notifications -->
    <Toast />
  </div>
</template>
