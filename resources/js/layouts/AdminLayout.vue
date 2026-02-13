<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import admin from '@/routes/admin'
import axios from '@/lib/axios'
import Toast from '@/components/Toast.vue'
import { useMenuIcons } from '@/composables/useMenuIcons'
import { useAppearance } from '@/composables/useAppearance'
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
  Settings,
  Sun,
  Moon
} from 'lucide-vue-next'

defineProps<{
  title?: string
}>()

const page = usePage()
const { getIcon } = useMenuIcons()
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const userMenuOpen = ref(false)
const notificationsOpen = ref(false)
const notifications = ref<any[]>([])
const notificationsLoading = ref(false)
const notificationsUnreadCount = ref<number>(Number((page.props as any)?.adminNotifications?.unread_count ?? 0))
const hasNotificationPulse = ref(false)
const latestSeenNotificationId = ref<number | null>(null)
const hoveredMenuId = ref<number | null>(null)
const popoverPosition = ref({ top: 0 })
let notificationPollTimer: ReturnType<typeof setInterval> | null = null
let notificationPulseTimer: ReturnType<typeof setTimeout> | null = null

// Admin config for logo
const adminConfig = computed(() => page.props.adminConfig as any)

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

// Search
const searchOpen = ref(false)
const searchQuery = ref('')
const prefersDark = ref(false)
let mediaQueryList: MediaQueryList | null = null
const handleSystemThemeChange = (event: MediaQueryListEvent) => {
  prefersDark.value = event.matches
}
const { appearance, updateAppearance } = useAppearance()
const searchResults = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) return []

  const results: any[] = []
  const walk = (items: any[]) => {
    items.forEach((item) => {
      if (item.full_url && item.title?.toLowerCase().includes(query)) {
        results.push(item)
      }
      if (item.children && item.children.length) {
        walk(item.children)
      }
    })
  }

  walk(menuItems.value || [])
  return results
})

const openSearch = () => {
  searchOpen.value = true
  searchQuery.value = ''
  nextTick(() => {
    const el = document.getElementById('admin-search-input') as HTMLInputElement | null
    el?.focus()
  })
}

const closeSearch = () => {
  searchOpen.value = false
  searchQuery.value = ''
}

const handleSearchKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    closeSearch()
  }
}

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

// Initialize open menus based on active routes only
const initializeOpenMenus = () => {
  // Clear current state
  openMenus.value.clear()
  
  // Only open menus that have an active child (current page)
  menuItems.value.forEach((item: any) => {
    if (hasActiveChild(item)) {
      openMenus.value.add(item.id)
    }
  })
  
  // Save the new state
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

  window.addEventListener('keydown', handleSearchKeydown)

  mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)')
  prefersDark.value = mediaQueryList.matches
  mediaQueryList.addEventListener('change', handleSystemThemeChange)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleSearchKeydown)
  if (mediaQueryList) {
    mediaQueryList.removeEventListener('change', handleSystemThemeChange)
  }
})

const isDarkMode = computed(() => {
  if (appearance.value === 'dark') return true
  if (appearance.value === 'light') return false
  return prefersDark.value
})

const toggleAppearance = () => {
  if (isDarkMode.value) {
    updateAppearance('light')
  } else {
    updateAppearance('dark')
  }
}

const appVersion = computed(() => page.props.appVersion as string | undefined)
const versionLabel = computed(() => (appVersion.value ? `v${appVersion.value}` : 'v--'))

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
    // Accordion: close all other menus before opening this one
    openMenus.value.clear()
    openMenus.value.add(itemId)
    // Keep parent with active child open too
    menuItems.value.forEach((mi: any) => {
      if (mi.id !== itemId && hasActiveChild(mi)) {
        openMenus.value.add(mi.id)
      }
    })
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

const fetchNotifications = async (silent = false) => {
  if (!silent) {
    notificationsLoading.value = true
  }

  try {
    const response = await axios.get('/admin/notifications', {
      params: {
        limit: 12,
      },
      headers: {
        Accept: 'application/json',
      },
    })

    const payload = response.data || {}
    notifications.value = payload.notifications || []
    const latestIncomingId = Number(notifications.value[0]?.id || 0)
    notificationsUnreadCount.value = Number(payload.unread_count || 0)

    if (latestSeenNotificationId.value === null) {
      latestSeenNotificationId.value = latestIncomingId || null
    } else if (latestIncomingId && latestIncomingId !== latestSeenNotificationId.value) {
      latestSeenNotificationId.value = latestIncomingId

      if (notificationsUnreadCount.value > 0) {
        hasNotificationPulse.value = true

        if (notificationPulseTimer) {
          clearTimeout(notificationPulseTimer)
        }

        notificationPulseTimer = setTimeout(() => {
          hasNotificationPulse.value = false
        }, 5000)
      }
    }
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  } finally {
    if (!silent) {
      notificationsLoading.value = false
    }
  }
}

const toggleNotifications = async () => {
  notificationsOpen.value = !notificationsOpen.value

  if (notificationsOpen.value) {
    userMenuOpen.value = false
    await fetchNotifications()
  }
}

const markNotificationAsRead = async (notificationId: number) => {
  try {
    await axios.post(`/admin/notifications/${notificationId}/read`, {}, {
      headers: {
        Accept: 'application/json',
      },
    })

    notifications.value = notifications.value.map((item) => {
      if (item.id === notificationId) {
        return {
          ...item,
          read_at: new Date().toISOString(),
        }
      }

      return item
    })

    notificationsUnreadCount.value = Math.max(0, notificationsUnreadCount.value - 1)
  } catch (error) {
    console.error('Failed to mark notification as read:', error)
  }
}

const markAllNotificationsAsRead = async () => {
  try {
    await axios.post('/admin/notifications/mark-all-read', {}, {
      headers: {
        Accept: 'application/json',
      },
    })

    notifications.value = notifications.value.map((item) => ({
      ...item,
      read_at: item.read_at || new Date().toISOString(),
    }))
    notificationsUnreadCount.value = 0
  } catch (error) {
    console.error('Failed to mark all notifications as read:', error)
  }
}

watch(
  () => (page.props as any)?.adminNotifications?.unread_count,
  (count) => {
    if (typeof count === 'number') {
      notificationsUnreadCount.value = count
    }
  }
)

watch(userMenuOpen, (open) => {
  if (open) {
    notificationsOpen.value = false
  }
})

watch(notificationsOpen, (open) => {
  if (open) {
    userMenuOpen.value = false
  }
})

onMounted(() => {
  fetchNotifications(true)

  notificationPollTimer = setInterval(() => {
    fetchNotifications(true)
  }, 30000)
})

onUnmounted(() => {
  if (notificationPulseTimer) {
    clearTimeout(notificationPulseTimer)
  }

  if (notificationPollTimer) {
    clearInterval(notificationPollTimer)
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 transform transition-all duration-200 ease-in-out flex flex-col',
        'bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 dark:from-slate-950 dark:via-slate-950 dark:to-black',
        'border-r border-slate-800/50',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'w-[72px]' : 'w-[260px]'
      ]"
    >
      <!-- Logo -->
      <div class="flex items-center justify-between h-16 border-b border-white/[0.06]" :class="sidebarCollapsed ? 'px-3' : 'px-5'">
        <Link :href="admin.dashboard.url()" class="flex items-center gap-3 min-w-0">
          <template v-if="adminConfig?.logo && !sidebarCollapsed">
            <img :src="`/storage/${adminConfig.logo}`" :alt="adminConfig?.site_name || 'Admin'" class="h-8 object-contain" />
          </template>
          <template v-else>
            <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/20">
              <span class="text-white font-bold text-lg">{{ adminConfig?.site_name?.charAt(0) || 'C' }}</span>
            </div>
            <span v-if="!sidebarCollapsed" class="text-white font-semibold text-lg truncate transition-opacity duration-200">{{ adminConfig?.site_name || 'Cartxis' }}</span>
          </template>
        </Link>
        
        <!-- Desktop Toggle Button -->
        <button
          @click="toggleSidebar"
          class="hidden lg:flex items-center justify-center w-7 h-7 rounded-lg text-slate-500 hover:text-white hover:bg-white/[0.06] transition-all"
          :class="sidebarCollapsed && 'mx-auto'"
        >
          <ChevronLeft v-if="!sidebarCollapsed" class="w-4 h-4" />
          <ChevronRight v-if="sidebarCollapsed" class="w-4 h-4" />
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-hidden">
        <div class="py-4 overflow-y-auto h-full custom-scrollbar" :class="sidebarCollapsed ? 'px-2' : 'px-3'">

        <!-- Section label -->
        <div v-if="!sidebarCollapsed" class="px-3 mb-3">
          <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">Navigation</span>
        </div>

        <!-- Dynamic Menu Items -->
        <div class="space-y-0.5">
        <template v-for="item in menuItems" :key="item.id">
          <!-- Parent Menu without children (Direct Link) -->
          <Link
            v-if="!item.children || item.children.length === 0"
            :href="item.full_url || '#'"
            :class="[
              'group relative flex items-center gap-3 rounded-lg transition-all duration-150',
              sidebarCollapsed ? 'justify-center p-2.5 mx-auto' : 'px-3 py-2.5',
              isActive(item)
                ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/20'
                : 'text-slate-400 hover:text-white hover:bg-white/[0.06]'
            ]"
            :title="sidebarCollapsed ? item.title : ''"
          >
            <component 
              :is="getIcon(item.icon)" 
              v-if="item.icon"
              class="flex-shrink-0 transition-colors"
              :class="[
                sidebarCollapsed ? 'w-5 h-5' : 'w-[18px] h-[18px]',
                isActive(item) ? 'text-white' : 'text-slate-500 group-hover:text-slate-300'
              ]" 
            />
            <span v-if="!sidebarCollapsed" class="text-[15px] font-medium">{{ item.title }}</span>
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
                'group w-full flex items-center gap-3 rounded-lg transition-all duration-150',
                sidebarCollapsed ? 'justify-center p-2.5' : 'px-3 py-2.5',
                (isMenuOpen(item.id) || hasActiveChild(item))
                  ? 'text-white bg-white/[0.06]'
                  : 'text-slate-400 hover:text-white hover:bg-white/[0.06]'
              ]"
              :title="sidebarCollapsed ? item.title : ''"
            >
              <component 
                :is="getIcon(item.icon)" 
                v-if="item.icon"
                class="flex-shrink-0 transition-colors"
                :class="[
                  sidebarCollapsed ? 'w-5 h-5' : 'w-[18px] h-[18px]',
                  (isMenuOpen(item.id) || hasActiveChild(item)) ? 'text-blue-400' : 'text-slate-500 group-hover:text-slate-300'
                ]" 
              />
              <span v-if="!sidebarCollapsed" class="flex-1 text-left text-[15px] font-medium">{{ item.title }}</span>
              <ChevronDown 
                v-if="!sidebarCollapsed" 
                :class="[
                  'w-3.5 h-3.5 text-slate-500 transition-transform duration-200',
                  isMenuOpen(item.id) && 'rotate-180 text-slate-400'
                ]" 
              />
            </button>
            
            <!-- Child Menu Items (expanded when sidebar is open) -->
            <Transition
              enter-active-class="transition-all duration-200 ease-out"
              enter-from-class="max-h-0 opacity-0"
              enter-to-class="max-h-[500px] opacity-100"
              leave-active-class="transition-all duration-150 ease-in"
              leave-from-class="max-h-[500px] opacity-100"
              leave-to-class="max-h-0 opacity-0"
            >
              <div v-if="!sidebarCollapsed" v-show="isMenuOpen(item.id)" class="overflow-hidden">
                <div class="ml-[15px] mt-1 mb-1 pl-3 border-l border-slate-800 space-y-0.5">
                  <Link 
                    v-for="child in item.children" 
                    :key="child.id"
                    :href="child.full_url || '#'" 
                    :class="[
                      'group/child flex items-center gap-2.5 px-3 py-2 rounded-lg transition-all duration-150 cursor-pointer',
                      isActive(child)
                        ? 'bg-blue-500/10 text-blue-400' 
                        : 'text-slate-500 hover:text-slate-200 hover:bg-white/[0.04]'
                    ]"
                  >
                    <component 
                      :is="getIcon(child.icon)" 
                      v-if="child.icon"
                      :class="[
                        'w-3.5 h-3.5 transition-colors',
                        isActive(child) ? 'text-blue-400' : 'text-slate-600 group-hover/child:text-slate-400'
                      ]" 
                    />
                    <span class="text-[13px]">{{ child.title }}</span>
                  </Link>
                </div>
              </div>
            </Transition>

            <!-- Hover Popover for collapsed sidebar -->
            <Teleport to="body">
              <Transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 translate-x-1"
                enter-to-class="transform opacity-100 translate-x-0"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 translate-x-0"
                leave-to-class="transform opacity-0 translate-x-1"
              >
                <div 
                  v-if="sidebarCollapsed && hoveredMenuId === item.id"
                  class="fixed left-[76px] w-52 bg-slate-900 rounded-xl shadow-xl shadow-black/30 border border-white/[0.08] py-1.5 z-[60]"
                  :style="{ top: `${popoverPosition.top}px` }"
                  @mouseenter="hoveredMenuId = item.id"
                  @mouseleave="hoveredMenuId = null"
                >
                  <div class="px-3.5 py-2 border-b border-white/[0.06]">
                    <p class="text-xs font-semibold text-white">{{ item.title }}</p>
                  </div>
                  <div class="py-1">
                    <Link 
                      v-for="child in item.children" 
                      :key="child.id"
                      :href="child.full_url || '#'" 
                      :class="[
                        'flex items-center gap-2.5 px-3.5 py-2 text-[13px] transition-colors cursor-pointer mx-1.5 rounded-lg',
                        isActive(child)
                          ? 'bg-blue-500/10 text-blue-400' 
                          : 'text-slate-400 hover:text-white hover:bg-white/[0.06]'
                      ]"
                    >
                      <component 
                        :is="getIcon(child.icon)" 
                        v-if="child.icon"
                        class="w-3.5 h-3.5" 
                      />
                      <span>{{ child.title }}</span>
                    </Link>
                  </div>
                </div>
              </Transition>
            </Teleport>
          </div>
        </template>
        </div>
        </div>
      </nav>

      <div class="border-t border-white/[0.06] text-slate-400" :class="sidebarCollapsed ? 'px-2 py-3' : 'px-4 py-3'">
        <span v-if="!sidebarCollapsed" class="text-xs">Version {{ appVersion || '--' }}</span>
        <span v-else class="text-[10px] font-semibold uppercase block text-center">{{ versionLabel }}</span>
      </div>
    </aside>

    <!-- Main Content -->
    <div :class="['transition-all duration-200', sidebarCollapsed ? 'lg:pl-[72px]' : 'lg:pl-[260px]']">  
      <!-- Header -->
      <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-40">
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
              <!-- Theme Toggle -->
              <button
                @click="toggleAppearance"
                class="text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300"
                :aria-label="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
                :title="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
              >
                <Sun v-if="isDarkMode" class="w-5 h-5" />
                <Moon v-else class="w-5 h-5" />
              </button>

              <!-- Search -->
              <button
                @click="openSearch"
                class="text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300"
              >
                <Search class="w-5 h-5" />
              </button>

              <!-- Notifications -->
              <div class="relative">
                <button
                  @click="toggleNotifications"
                  class="relative text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300 transition-transform duration-300"
                  :class="hasNotificationPulse ? 'animate-bounce text-blue-600 dark:text-blue-400' : ''"
                  aria-label="Notifications"
                  title="Notifications"
                >
                  <Bell class="w-5 h-5" />
                  <span
                    v-if="hasNotificationPulse"
                    class="absolute -top-1 -right-1 inline-flex h-4 w-4 rounded-full bg-blue-400 opacity-75 animate-ping"
                  ></span>
                  <span
                    v-if="notificationsUnreadCount > 0"
                    class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-[10px] leading-4 text-white text-center"
                  >
                    {{ notificationsUnreadCount > 99 ? '99+' : notificationsUnreadCount }}
                  </span>
                </button>

                <Transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <div
                    v-show="notificationsOpen"
                    class="absolute right-0 mt-2 w-96 max-w-[92vw] bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
                  >
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                      <p class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</p>
                      <div class="flex items-center gap-3">
                        <a
                          href="/admin/activity-logs"
                          class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:underline"
                        >
                          View logs
                        </a>
                        <button
                          v-if="notificationsUnreadCount > 0"
                          @click="markAllNotificationsAsRead"
                          class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline"
                        >
                          Mark all as read
                        </button>
                      </div>
                    </div>

                    <div v-if="notificationsLoading" class="px-4 py-8 text-sm text-gray-500 dark:text-gray-400 text-center">
                      Loading notifications...
                    </div>

                    <div v-else-if="notifications.length === 0" class="px-4 py-8 text-sm text-gray-500 dark:text-gray-400 text-center">
                      No notifications yet.
                    </div>

                    <div v-else class="max-h-[360px] overflow-y-auto">
                      <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        class="px-4 py-3 border-b border-gray-100 dark:border-gray-700/60"
                        :class="notification.read_at ? 'bg-white dark:bg-gray-800' : 'bg-blue-50/60 dark:bg-blue-900/10'"
                      >
                        <div class="flex items-start gap-3">
                          <div class="mt-1 w-2 h-2 rounded-full" :class="notification.read_at ? 'bg-gray-300 dark:bg-gray-600' : 'bg-blue-500'"></div>
                          <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ notification.title }}</p>
                            <p v-if="notification.message" class="text-xs mt-0.5 text-gray-600 dark:text-gray-300 line-clamp-2">{{ notification.message }}</p>
                            <p class="text-[11px] mt-1 text-gray-500 dark:text-gray-400">{{ notification.created_at_human }}</p>
                            <div class="mt-2 flex items-center gap-3">
                              <a
                                v-if="notification.action_url"
                                :href="notification.action_url"
                                class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline"
                              >
                                View
                              </a>
                              <button
                                v-if="!notification.read_at"
                                @click="markNotificationAsRead(notification.id)"
                                class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:underline"
                              >
                                Mark read
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </Transition>
              </div>

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

    <!-- Search Modal -->
    <div
      v-if="searchOpen"
      @click.self="closeSearch"
      class="fixed inset-0 z-50 flex items-start justify-center bg-black/40 px-4"
    >
      <div class="mt-20 w-full max-w-xl overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-xl ring-1 ring-black/10 dark:ring-white/10">
        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 px-4 py-3">
          <Search class="h-4 w-4 text-gray-400 dark:text-gray-500" />
          <input
            id="admin-search-input"
            v-model="searchQuery"
            type="text"
            placeholder="Search modules, pages, settings..."
            class="w-full bg-transparent text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none"
          />
          <button
            @click="closeSearch"
            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
          >
            Esc
          </button>
        </div>

        <div class="max-h-80 overflow-y-auto">
          <div v-if="!searchQuery" class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">
            Start typing to search the admin menu.
          </div>
          <div v-else-if="searchResults.length === 0" class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">
            No results found.
          </div>
          <div v-else class="py-2">
            <Link
              v-for="item in searchResults"
              :key="item.id"
              :href="item.full_url"
              @click="closeSearch"
              class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              <div class="font-medium text-gray-900 dark:text-white">{{ item.title }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">{{ item.full_url }}</div>
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notifications -->
    <Toast />
  </div>
</template>
