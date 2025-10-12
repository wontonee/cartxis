<script setup lang="ts">
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import admin from '@/routes/admin'
import Toast from '@/Components/Toast.vue'
import {
  Menu,
  ChevronDown,
  LayoutDashboard,
  ShoppingBag,
  ShoppingCart,
  Users,
  TrendingUp,
  FileText,
  BarChart3,
  Settings,
  Server,
  Shield,
  Wrench,
  LogOut,
  Search,
  Bell,
  Tag,
  Package,
  FolderTree,
  ListChecks,
  Star,
  Receipt,
  Truck,
  CreditCard,
  Megaphone,
  Ticket,
  Mail,
  Globe,
  BookOpen,
  Image,
  Newspaper,
  Zap,
  ChevronLeft,
  ChevronRight,
  User,
  UserCircle
} from 'lucide-vue-next'

defineProps<{
  title?: string
}>()

const page = usePage()
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const userMenuOpen = ref(false)
const catalogOpen = ref(true) // Open by default if on catalog page
const salesOpen = ref(false)
const customersOpen = ref(false)
const marketingOpen = ref(false)
const contentOpen = ref(false)
const reportsOpen = ref(false)
const settingsOpen = ref(false)
const systemOpen = ref(false)
const usersOpen = ref(false)
const toolsOpen = ref(false)

// Helper function to check if route is active
const isActive = (path: string) => {
  return page.url.startsWith(path)
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
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
      <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
        <!-- Dashboard -->
        <Link
          :href="admin.dashboard.url()"
          :class="[
            'flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
            { 'bg-blue-600 text-white': $page.url === '/admin/dashboard' },
            sidebarCollapsed && 'justify-center'
          ]"
          :title="sidebarCollapsed ? 'Dashboard' : ''"
        >
          <LayoutDashboard class="w-5 h-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }" />
          <span v-if="!sidebarCollapsed">Dashboard</span>
        </Link>

        <!-- Catalog -->
        <div>
          <button
            @click="catalogOpen = !catalogOpen"
            :class="[
              'w-full flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
              sidebarCollapsed && 'justify-center'
            ]"
            :title="sidebarCollapsed ? 'Catalog' : ''"
          >
            <ShoppingBag class="w-5 h-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }" />
            <span v-if="!sidebarCollapsed" class="flex-1 text-left">Catalog</span>
            <ChevronDown v-if="!sidebarCollapsed" :class="['w-4 h-4 transition-transform', catalogOpen && 'rotate-180']" />
          </button>
          <div v-if="!sidebarCollapsed" v-show="catalogOpen" class="ml-4 mt-1 space-y-1">
            <Link 
              :href="admin.catalog.products.index.url()" 
              :class="[
                'flex items-center px-4 py-2 rounded-lg transition-colors cursor-pointer',
                isActive('/admin/catalog/products') 
                  ? 'bg-blue-600 text-white' 
                  : 'text-gray-400 hover:text-white hover:bg-gray-800'
              ]"
            >
              <Package class="w-4 h-4 mr-3" />
              <span class="text-sm">Products</span>
            </Link>
            <Link 
              :href="admin.catalog.categories.index.url()" 
              :class="[
                'flex items-center px-4 py-2 rounded-lg transition-colors cursor-pointer',
                isActive('/admin/catalog/categories') 
                  ? 'bg-blue-600 text-white' 
                  : 'text-gray-400 hover:text-white hover:bg-gray-800'
              ]"
            >
              <FolderTree class="w-4 h-4 mr-3" />
              <span class="text-sm">Categories</span>
            </Link>
            <Link 
              :href="admin.catalog.attributes.index.url()" 
              :class="[
                'flex items-center px-4 py-2 rounded-lg transition-colors cursor-pointer',
                isActive('/admin/catalog/attributes') 
                  ? 'bg-blue-600 text-white' 
                  : 'text-gray-400 hover:text-white hover:bg-gray-800'
              ]"
            >
              <ListChecks class="w-4 h-4 mr-3" />
              <span class="text-sm">Attributes</span>
            </Link>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors cursor-pointer">
              <Tag class="w-4 h-4 mr-3" />
              <span class="text-sm">Brands</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors cursor-pointer">
              <Star class="w-4 h-4 mr-3" />
              <span class="text-sm">Reviews</span>
            </a>
          </div>
        </div>

        <!-- Sales -->
        <div>
          <button
            @click="salesOpen = !salesOpen"
            :class="[
              'w-full flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
              sidebarCollapsed && 'justify-center'
            ]"
            :title="sidebarCollapsed ? 'Sales' : ''"
          >
            <ShoppingCart class="w-5 h-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }" />
            <span v-if="!sidebarCollapsed" class="flex-1 text-left">Sales</span>
            <ChevronDown v-if="!sidebarCollapsed" :class="['w-4 h-4 transition-transform', salesOpen && 'rotate-180']" />
          </button>
          <div v-if="!sidebarCollapsed" v-show="salesOpen" class="ml-4 mt-1 space-y-1">
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <ShoppingCart class="w-4 h-4 mr-3" />
              <span class="text-sm">Orders</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Receipt class="w-4 h-4 mr-3" />
              <span class="text-sm">Invoices</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Truck class="w-4 h-4 mr-3" />
              <span class="text-sm">Shipments</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <CreditCard class="w-4 h-4 mr-3" />
              <span class="text-sm">Transactions</span>
            </a>
          </div>
        </div>

        <!-- Customers -->
        <div>
          <button
            @click="customersOpen = !customersOpen"
            :class="[
              'w-full flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
              sidebarCollapsed && 'justify-center'
            ]"
            :title="sidebarCollapsed ? 'Customers' : ''"
          >
            <Users class="w-5 h-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }" />
            <span v-if="!sidebarCollapsed" class="flex-1 text-left">Customers</span>
            <ChevronDown v-if="!sidebarCollapsed" :class="['w-4 h-4 transition-transform', customersOpen && 'rotate-180']" />
          </button>
          <div v-if="!sidebarCollapsed" v-show="customersOpen" class="ml-4 mt-1 space-y-1">
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Users class="w-4 h-4 mr-3" />
              <span class="text-sm">All Customers</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Tag class="w-4 h-4 mr-3" />
              <span class="text-sm">Customer Groups</span>
            </a>
          </div>
        </div>

        <!-- Marketing -->
        <div>
          <button
            @click="marketingOpen = !marketingOpen"
            :class="[
              'w-full flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
              sidebarCollapsed && 'justify-center'
            ]"
            :title="sidebarCollapsed ? 'Marketing' : ''"
          >
            <TrendingUp class="w-5 h-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }" />
            <span v-if="!sidebarCollapsed" class="flex-1 text-left">Marketing</span>
            <ChevronDown v-if="!sidebarCollapsed" :class="['w-4 h-4 transition-transform', marketingOpen && 'rotate-180']" />
          </button>
          <div v-if="!sidebarCollapsed" v-show="marketingOpen" class="ml-4 mt-1 space-y-1">
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Megaphone class="w-4 h-4 mr-3" />
              <span class="text-sm">Promotions</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Ticket class="w-4 h-4 mr-3" />
              <span class="text-sm">Coupons</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Mail class="w-4 h-4 mr-3" />
              <span class="text-sm">Email Campaigns</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Globe class="w-4 h-4 mr-3" />
              <span class="text-sm">SEO</span>
            </a>
          </div>
        </div>

        <!-- Content -->
        <div>
          <button
            @click="contentOpen = !contentOpen"
            :class="[
              'w-full flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
              sidebarCollapsed && 'justify-center'
            ]"
            :title="sidebarCollapsed ? 'Content' : ''"
          >
            <FileText class="w-5 h-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }" />
            <span v-if="!sidebarCollapsed" class="flex-1 text-left">Content</span>
            <ChevronDown v-if="!sidebarCollapsed" :class="['w-4 h-4 transition-transform', contentOpen && 'rotate-180']" />
          </button>
          <div v-if="!sidebarCollapsed" v-show="contentOpen" class="ml-4 mt-1 space-y-1">
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <BookOpen class="w-4 h-4 mr-3" />
              <span class="text-sm">Pages</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Newspaper class="w-4 h-4 mr-3" />
              <span class="text-sm">Blog Posts</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Image class="w-4 h-4 mr-3" />
              <span class="text-sm">Media Gallery</span>
            </a>
          </div>
        </div>

        <!-- Reports -->
        <div>
          <button
            @click="reportsOpen = !reportsOpen"
            :class="[
              'w-full flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
              sidebarCollapsed && 'justify-center'
            ]"
            :title="sidebarCollapsed ? 'Reports' : ''"
          >
            <BarChart3 class="w-5 h-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }" />
            <span v-if="!sidebarCollapsed" class="flex-1 text-left">Reports</span>
            <ChevronDown v-if="!sidebarCollapsed" :class="['w-4 h-4 transition-transform', reportsOpen && 'rotate-180']" />
          </button>
          <div v-if="!sidebarCollapsed" v-show="reportsOpen" class="ml-4 mt-1 space-y-1">
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <TrendingUp class="w-4 h-4 mr-3" />
              <span class="text-sm">Sales Reports</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Package class="w-4 h-4 mr-3" />
              <span class="text-sm">Product Reports</span>
            </a>
          </div>
        </div>

        <!-- Settings -->
        <div>
          <button
            @click="settingsOpen = !settingsOpen"
            :class="[
              'w-full flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
              sidebarCollapsed && 'justify-center'
            ]"
            :title="sidebarCollapsed ? 'Settings' : ''"
          >
            <Settings class="w-5 h-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }" />
            <span v-if="!sidebarCollapsed" class="flex-1 text-left">Settings</span>
            <ChevronDown v-if="!sidebarCollapsed" :class="['w-4 h-4 transition-transform', settingsOpen && 'rotate-180']" />
          </button>
          <div v-if="!sidebarCollapsed" v-show="settingsOpen" class="ml-4 mt-1 space-y-1">
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Settings class="w-4 h-4 mr-3" />
              <span class="text-sm">General</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <CreditCard class="w-4 h-4 mr-3" />
              <span class="text-sm">Payment Methods</span>
            </a>
          </div>
        </div>

        <!-- System -->
        <div>
          <button
            @click="systemOpen = !systemOpen"
            :class="[
              'w-full flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
              sidebarCollapsed && 'justify-center'
            ]"
            :title="sidebarCollapsed ? 'System' : ''"
          >
            <Server class="w-5 h-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }" />
            <span v-if="!sidebarCollapsed" class="flex-1 text-left">System</span>
            <ChevronDown v-if="!sidebarCollapsed" :class="['w-4 h-4 transition-transform', systemOpen && 'rotate-180']" />
          </button>
          <div v-if="!sidebarCollapsed" v-show="systemOpen" class="ml-4 mt-1 space-y-1">
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Zap class="w-4 h-4 mr-3" />
              <span class="text-sm">Cache Management</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <FileText class="w-4 h-4 mr-3" />
              <span class="text-sm">Logs</span>
            </a>
          </div>
        </div>

        <!-- Users & Roles -->
        <div>
          <button
            @click="usersOpen = !usersOpen"
            :class="[
              'w-full flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
              sidebarCollapsed && 'justify-center'
            ]"
            :title="sidebarCollapsed ? 'Users & Roles' : ''"
          >
            <Shield class="w-5 h-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }" />
            <span v-if="!sidebarCollapsed" class="flex-1 text-left">Users & Roles</span>
            <ChevronDown v-if="!sidebarCollapsed" :class="['w-4 h-4 transition-transform', usersOpen && 'rotate-180']" />
          </button>
          <div v-if="!sidebarCollapsed" v-show="usersOpen" class="ml-4 mt-1 space-y-1">
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Users class="w-4 h-4 mr-3" />
              <span class="text-sm">Admin Users</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Shield class="w-4 h-4 mr-3" />
              <span class="text-sm">Roles & Permissions</span>
            </a>
          </div>
        </div>

        <!-- Tools -->
        <div>
          <button
            @click="toolsOpen = !toolsOpen"
            :class="[
              'w-full flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors',
              sidebarCollapsed && 'justify-center'
            ]"
            :title="sidebarCollapsed ? 'Tools' : ''"
          >
            <Wrench class="w-5 h-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }" />
            <span v-if="!sidebarCollapsed" class="flex-1 text-left">Tools</span>
            <ChevronDown v-if="!sidebarCollapsed" :class="['w-4 h-4 transition-transform', toolsOpen && 'rotate-180']" />
          </button>
          <div v-if="!sidebarCollapsed" v-show="toolsOpen" class="ml-4 mt-1 space-y-1">
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <FileText class="w-4 h-4 mr-3" />
              <span class="text-sm">Import/Export</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-400 hover:text-white transition-colors">
              <Zap class="w-4 h-4 mr-3" />
              <span class="text-sm">Bulk Actions</span>
            </a>
          </div>
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
                  <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors">
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
                    
                    <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                      <User class="w-4 h-4 mr-3" />
                      Profile
                    </a>
                    
                    <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                      <Settings class="w-4 h-4 mr-3" />
                      Account Settings
                    </a>
                    
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
      class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
    ></div>

    <!-- Toast Notifications -->
    <Toast />
  </div>
</template>
