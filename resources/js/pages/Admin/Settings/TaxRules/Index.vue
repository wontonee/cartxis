<template>
  <Head title="Tax Rules" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300">
            Tax Configuration
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <Receipt class="w-4 h-4" />
            Manage tax rules, classes, rates, and zones
          </p>
        </div>
      </div>

      <!-- Main Content -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="flex overflow-x-auto" aria-label="Tabs">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'flex items-center gap-2 whitespace-nowrap px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/10'
                  : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'
              ]"
            >
              <component :is="tab.icon" class="w-4 h-4" />
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <div class="p-6">
          <!-- Tax Rules Tab -->
          <div v-show="activeTab === 'rules'" class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4 justify-between">
              <div class="relative max-w-md w-full">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search tax rules..."
                  class="w-full pl-9 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
                />
              </div>
              <Link
                href="/admin/settings/tax-rules/create"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm"
              >
                <Plus class="w-4 h-4 mr-2" />
                Create Rule
              </Link>
            </div>

            <div v-if="taxRules.data.length === 0" class="text-center py-12">
              <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                <Gavel class="w-6 h-6 text-gray-400" />
              </div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">No rules found</h3>
              <p class="mt-1 text-gray-500 dark:text-gray-400">Get started by creating your first tax rule.</p>
            </div>

            <div v-else class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
              <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                  <tr>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Name</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Tax Class</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Zone</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Rate</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Priority</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Status</th>
                    <th class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="rule in taxRules.data" :key="rule.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">{{ rule.name }}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                      <div class="flex items-center gap-2">
                        <Tag class="w-3.5 h-3.5 text-gray-400" />
                        {{ rule.tax_class.name }}
                      </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                      <div class="flex items-center gap-2">
                        <Map class="w-3.5 h-3.5 text-gray-400" />
                        {{ rule.tax_zone.name }}
                      </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ rule.tax_rate.percentage }}%</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ rule.priority }}</td>
                    <td class="px-6 py-4">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="rule.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                      >
                        {{ rule.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <div class="flex items-center justify-end gap-2">
                        <Link
                          :href="`/admin/settings/tax-rules/${rule.id}/edit`"
                          class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors dark:text-blue-400 dark:hover:bg-blue-900/30"
                        >
                          <Edit class="w-4 h-4" />
                        </Link>
                        <button
                          @click="confirmDelete(rule.id)"
                          class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:text-red-400 dark:hover:bg-red-900/30"
                        >
                          <Trash2 class="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <Pagination :data="taxRules" resource-name="tax rules" />
          </div>

          <!-- Tax Classes Tab -->
          <div v-show="activeTab === 'classes'" class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-center bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl">
              <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tax Classes</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Categorize products for tax calculation purposes.</p>
              </div>
              <button
                @click="openTaxClassModal()"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm"
              >
                <Plus class="w-4 h-4 mr-2" />
                Add Class
              </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
              <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                  <tr>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Name</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Code</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Description</th>
                    <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-white">Default</th>
                    <th class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="taxClass in taxClasses" :key="taxClass.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">{{ taxClass.name }}</td>
                    <td class="px-6 py-4 font-mono text-gray-600 dark:text-gray-300 text-xs">{{ taxClass.code }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ taxClass.description || '—' }}</td>
                    <td class="px-6 py-4 text-center">
                      <span v-if="taxClass.is_default" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                        Default
                      </span>
                      <span v-else class="text-gray-400 dark:text-gray-600">—</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <div class="flex items-center justify-end gap-2">
                        <button
                          @click="openTaxClassModal(taxClass)"
                          class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors dark:text-blue-400 dark:hover:bg-blue-900/30"
                        >
                          <Edit class="w-4 h-4" />
                        </button>
                        <button
                          @click="confirmDeleteTaxClass(taxClass.id)"
                          class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:text-red-400 dark:hover:bg-red-900/30"
                        >
                          <Trash2 class="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tax Rates Tab -->
          <div v-show="activeTab === 'rates'" class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-center bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl">
              <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tax Rates</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Define percentages for tax calculations.</p>
              </div>
              <button
                @click="openTaxRateModal()"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm"
              >
                <Plus class="w-4 h-4 mr-2" />
                Add Rate
              </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
              <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                  <tr>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Name</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Code</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Rate</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Priority</th>
                    <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-white">Compound</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Status</th>
                    <th class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="rate in taxRates" :key="rate.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">{{ rate.name }}</td>
                    <td class="px-6 py-4 font-mono text-gray-600 dark:text-gray-300 text-xs">{{ rate.code }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">{{ rate.percentage }}%</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ rate.priority }}</td>
                    <td class="px-6 py-4 text-center">
                      <span v-if="rate.is_compound" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                        Compound
                      </span>
                      <span v-else class="text-gray-400 dark:text-gray-600">—</span>
                    </td>
                    <td class="px-6 py-4">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="rate.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                      >
                        {{ rate.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <div class="flex items-center justify-end gap-2">
                        <button
                          @click="openTaxRateModal(rate)"
                          class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors dark:text-blue-400 dark:hover:bg-blue-900/30"
                        >
                          <Edit class="w-4 h-4" />
                        </button>
                        <button
                          @click="confirmDeleteTaxRate(rate.id)"
                          class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:text-red-400 dark:hover:bg-red-900/30"
                        >
                          <Trash2 class="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tax Zones Tab -->
          <div v-show="activeTab === 'zones'" class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-center bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl">
              <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tax Zones</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Define geographic regions for tax application.</p>
              </div>
              <button
                @click="openTaxZoneModal()"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm"
              >
                <Plus class="w-4 h-4 mr-2" />
                Add Zone
              </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
              <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                  <tr>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Name</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Code</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Description</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Locations</th>
                    <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white">Status</th>
                    <th class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="zone in taxZones" :key="zone.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">{{ zone.name }}</td>
                    <td class="px-6 py-4 font-mono text-gray-600 dark:text-gray-300 text-xs">{{ zone.code }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ zone.description || '—' }}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                       <span class="inline-flex items-center px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded text-xs ring-1 ring-inset ring-blue-700/10 dark:ring-blue-400/20">
                          {{ zone.locations.length }} location(s)
                       </span>
                    </td>
                    <td class="px-6 py-4">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="zone.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                      >
                        {{ zone.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <div class="flex items-center justify-end gap-2">
                        <button
                          @click="openTaxZoneModal(zone)"
                          class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors dark:text-blue-400 dark:hover:bg-blue-900/30"
                        >
                          <Edit class="w-4 h-4" />
                        </button>
                        <button
                          @click="confirmDeleteTaxZone(zone.id)"
                          class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:text-red-400 dark:hover:bg-red-900/30"
                        >
                          <Trash2 class="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>

  <!-- Delete Confirmation Modal -->
  <div
    v-if="showDeleteModal"
    class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    @click.self="showDeleteModal = false"
  >
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-sm w-full p-6 space-y-4">
      <div class="flex items-center gap-3 text-red-600">
        <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-xl">
          <AlertTriangle class="w-6 h-6" />
        </div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Confirm Delete</h3>
      </div>
      <p class="text-gray-600 dark:text-gray-400">{{ deleteMessage }}</p>
      <div class="flex justify-end gap-3 pt-2">
        <button
          @click="showDeleteModal = false"
          class="px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl font-medium transition-colors"
        >
          Cancel
        </button>
        <button
          @click="deleteItem"
          class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition-colors shadow-sm"
        >
          Delete
        </button>
      </div>
    </div>
  </div>

  <!-- Tax Class Modal -->
  <div
    v-if="showTaxClassModal"
    class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    @click.self="showTaxClassModal = false"
  >
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ taxClassForm.id ? 'Edit Tax Class' : 'Create Tax Class' }}</h3>
        <button @click="showTaxClassModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
          <X class="w-5 h-5" />
        </button>
      </div>
      <form @submit.prevent="saveTaxClass" class="p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Name <span class="text-red-500">*</span></label>
          <input
            v-model="taxClassForm.name"
            type="text"
            required
            class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Code <span class="text-red-500">*</span></label>
          <input
            v-model="taxClassForm.code"
            type="text"
            required
            class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
          <textarea
            v-model="taxClassForm.description"
            rows="3"
            class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
          />
        </div>
        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
          <input
            v-model="taxClassForm.is_default"
            type="checkbox"
            class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700"
          />
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Set as default tax class</label>
        </div>
        
        <div class="flex justify-end gap-3 pt-4">
          <button
            type="button"
            @click="showTaxClassModal = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 font-medium text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-xl font-medium text-sm hover:bg-blue-700 disabled:opacity-50 transition-colors shadow-sm"
          >
            {{ processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Tax Rate Modal -->
  <div
    v-if="showTaxRateModal"
    class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    @click.self="showTaxRateModal = false"
  >
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ taxRateForm.id ? 'Edit Tax Rate' : 'Create Tax Rate' }}</h3>
        <button @click="showTaxRateModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
          <X class="w-5 h-5" />
        </button>
      </div>
      <form @submit.prevent="saveTaxRate" class="p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
           <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Name <span class="text-red-500">*</span></label>
            <input
              v-model="taxRateForm.name"
              type="text"
              required
              class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Code <span class="text-red-500">*</span></label>
            <input
              v-model="taxRateForm.code"
              type="text"
              required
              class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
            />
          </div>
           <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Priority <span class="text-red-500">*</span></label>
            <input
              v-model="taxRateForm.priority"
              type="number"
              min="1"
              required
              class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
            />
          </div>
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Percentage (%) <span class="text-red-500">*</span></label>
            <input
              v-model="taxRateForm.percentage"
              type="number"
              step="0.01"
              min="0"
              max="100"
              required
              class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
            />
          </div>
        </div>

        <div class="flex flex-col gap-3 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
          <div class="flex items-center gap-3">
            <input
              v-model="taxRateForm.is_compound"
              type="checkbox"
              class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700"
            />
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Compound Tax (calculated on top of other taxes)</label>
          </div>
          <div class="flex items-center gap-3 border-t border-gray-200 dark:border-gray-600 pt-3">
            <input
              v-model="taxRateForm.is_active"
              type="checkbox"
              class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700"
            />
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</label>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <button
            type="button"
            @click="showTaxRateModal = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 font-medium text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-xl font-medium text-sm hover:bg-blue-700 disabled:opacity-50 transition-colors shadow-sm"
          >
            {{ processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Tax Zone Modal -->
  <div
    v-if="showTaxZoneModal"
    class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    @click.self="showTaxZoneModal = false"
  >
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ taxZoneForm.id ? 'Edit Tax Zone' : 'Create Tax Zone' }}</h3>
        <button @click="showTaxZoneModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
          <X class="w-5 h-5" />
        </button>
      </div>
      <form @submit.prevent="saveTaxZone" class="p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Name <span class="text-red-500">*</span></label>
            <input
              v-model="taxZoneForm.name"
              type="text"
              required
              class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Code <span class="text-red-500">*</span></label>
            <input
              v-model="taxZoneForm.code"
              type="text"
              required
              class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
            />
          </div>
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
            <textarea
              v-model="taxZoneForm.description"
              rows="2"
              class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
            />
          </div>
           <div class="col-span-2">
             <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
               <input
                 v-model="taxZoneForm.is_active"
                 type="checkbox"
                 class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700"
               />
               <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</label>
             </div>
           </div>
        </div>

        <!-- Locations -->
        <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
          <div class="flex justify-between items-center mb-3">
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Locations</label>
            <button
              type="button"
              @click="addLocation"
              class="text-sm bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30 px-3 py-1.5 rounded-lg transition-colors font-medium border border-green-200 dark:border-green-800"
            >
              + Add Location
            </button>
          </div>
          
          <div class="space-y-3">
             <div v-if="taxZoneForm.locations.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm italic bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
               No locations added yet.
             </div>
            <div v-for="(location, index) in taxZoneForm.locations" :key="index" class="flex gap-2 items-start">
              <input
                v-model="location.country_code"
                type="text"
                placeholder="Country Code (e.g., US)"
                class="flex-1 px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
              />
              <input
                v-model="location.state_code"
                type="text"
                placeholder="State (optional)"
                class="flex-1 px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
              />
              <button
                type="button"
                @click="removeLocation(index)"
                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:text-red-400 dark:hover:bg-red-900/30"
              >
                <X class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
          <button
            type="button"
            @click="showTaxZoneModal = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 font-medium text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-xl font-medium text-sm hover:bg-blue-700 disabled:opacity-50 transition-colors shadow-sm"
          >
            {{ processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router, Link, Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Pagination from '@/components/Admin/Pagination.vue'
import { 
  Receipt, 
  Search, 
  Plus, 
  Gavel, 
  Tag, 
  Map, 
  Edit, 
  Trash2,
  AlertTriangle,
  X,
  Percent
} from 'lucide-vue-next'

interface TaxClass {
  id: number
  code: string
  name: string
  description: string | null
  is_default: boolean
}

interface TaxRate {
  id: number
  code: string
  name: string
  percentage: string
  priority: number
  is_compound: boolean
  is_active: boolean
}

interface TaxZoneLocation {
  id?: number
  country_code: string
  state_code: string | null
  postal_code_pattern?: string | null
  city?: string | null
}

interface TaxZone {
  id: number
  code: string
  name: string
  description: string | null
  is_active: boolean
  locations: TaxZoneLocation[]
}

interface TaxRule {
  id: number
  name: string
  priority: number
  calculate_shipping: boolean
  is_active: boolean
  tax_class: TaxClass
  tax_zone: TaxZone
  tax_rate: TaxRate
}


interface PaginatedData<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  from: number
  to: number
  total: number
  links: Array<{
    url: string | null
    label: string
    active: boolean
  }>
}

interface Props {
  taxRules: PaginatedData<TaxRule>
  taxClasses: TaxClass[]
  taxRates: TaxRate[]
  taxZones: TaxZone[]
  filters?: {
    search?: string
    tax_class_id?: number
    tax_zone_id?: number
    status?: string
  }
}

const props = defineProps<Props>()

const activeTab = ref('rules')
const searchQuery = ref(props.filters?.search || '')
const showDeleteModal = ref(false)
const deleteId = ref<number | null>(null)
const deleteType = ref<'rule' | 'class' | 'rate' | 'zone'>('rule')
const deleteMessage = ref('')
const processing = ref(false)

// Tax Class Modal State
const showTaxClassModal = ref(false)
const taxClassForm = reactive({
  id: null as number | null,
  name: '',
  code: '',
  description: '',
  is_default: false,
})

// Tax Rate Modal State
const showTaxRateModal = ref(false)
const taxRateForm = reactive({
  id: null as number | null,
  name: '',
  code: '',
  percentage: 0,
  priority: 1,
  is_compound: false,
  is_active: true,
})

// Tax Zone Modal State
const showTaxZoneModal = ref(false)
const taxZoneForm = reactive({
  id: null as number | null,
  name: '',
  code: '',
  description: '',
  is_active: true,
  locations: [] as TaxZoneLocation[],
})

const tabs = [
  { id: 'rules', name: 'Tax Rules', icon: Gavel },
  { id: 'classes', name: 'Tax Classes', icon: Tag },
  { id: 'rates', name: 'Tax Rates', icon: Percent },
  { id: 'zones', name: 'Tax Zones', icon: Map },
]

// Tax Rule Functions
const confirmDelete = (id: number) => {
  deleteId.value = id
  deleteType.value = 'rule'
  deleteMessage.value = 'Are you sure you want to delete this tax rule? This action cannot be undone.'
  showDeleteModal.value = true
}

const deleteTaxRule = () => {
  if (deleteId.value) {
    router.delete(`/admin/settings/tax-rules/${deleteId.value}`, {
      onSuccess: () => {
        showDeleteModal.value = false
        deleteId.value = null
      },
    })
  }
}

// Tax Class Functions
const openTaxClassModal = (taxClass?: TaxClass) => {
  if (taxClass) {
    taxClassForm.id = taxClass.id
    taxClassForm.name = taxClass.name
    taxClassForm.code = taxClass.code
    taxClassForm.description = taxClass.description || ''
    taxClassForm.is_default = taxClass.is_default
  } else {
    taxClassForm.id = null
    taxClassForm.name = ''
    taxClassForm.code = ''
    taxClassForm.description = ''
    taxClassForm.is_default = false
  }
  showTaxClassModal.value = true
}

const saveTaxClass = () => {
  processing.value = true
  const url = taxClassForm.id
    ? `/admin/settings/tax-classes/${taxClassForm.id}`
    : '/admin/settings/tax-classes'

  const method = taxClassForm.id ? 'put' : 'post'

  router[method](url, taxClassForm, {
    preserveScroll: true,
    onSuccess: () => {
      showTaxClassModal.value = false
      processing.value = false
    },
    onError: () => {
      processing.value = false
    },
  })
}

const confirmDeleteTaxClass = (id: number) => {
  deleteId.value = id
  deleteType.value = 'class'
  deleteMessage.value = 'Are you sure you want to delete this tax class? This may affect existing tax rules.'
  showDeleteModal.value = true
}

const deleteTaxClass = () => {
  if (deleteId.value) {
    router.delete(`/admin/settings/tax-classes/${deleteId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        showDeleteModal.value = false
        deleteId.value = null
      },
    })
  }
}

// Tax Rate Functions
const openTaxRateModal = (taxRate?: TaxRate) => {
  if (taxRate) {
    taxRateForm.id = taxRate.id
    taxRateForm.name = taxRate.name
    taxRateForm.code = taxRate.code
    taxRateForm.percentage = parseFloat(taxRate.percentage)
    taxRateForm.priority = taxRate.priority
    taxRateForm.is_compound = taxRate.is_compound
    taxRateForm.is_active = taxRate.is_active
  } else {
    taxRateForm.id = null
    taxRateForm.name = ''
    taxRateForm.code = ''
    taxRateForm.percentage = 0
    taxRateForm.priority = 1
    taxRateForm.is_compound = false
    taxRateForm.is_active = true
  }
  showTaxRateModal.value = true
}

const saveTaxRate = () => {
  processing.value = true
  const url = taxRateForm.id
    ? `/admin/settings/tax-rates/${taxRateForm.id}`
    : '/admin/settings/tax-rates'

  const method = taxRateForm.id ? 'put' : 'post'

  router[method](url, taxRateForm, {
    preserveScroll: true,
    onSuccess: () => {
      showTaxRateModal.value = false
      processing.value = false
    },
    onError: () => {
      processing.value = false
    },
  })
}

const confirmDeleteTaxRate = (id: number) => {
  deleteId.value = id
  deleteType.value = 'rate'
  deleteMessage.value = 'Are you sure you want to delete this tax rate? This may affect existing tax rules.'
  showDeleteModal.value = true
}

const deleteTaxRate = () => {
  if (deleteId.value) {
    router.delete(`/admin/settings/tax-rates/${deleteId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        showDeleteModal.value = false
        deleteId.value = null
      },
    })
  }
}

// Tax Zone Functions
const openTaxZoneModal = (taxZone?: TaxZone) => {
  if (taxZone) {
    taxZoneForm.id = taxZone.id
    taxZoneForm.name = taxZone.name
    taxZoneForm.code = taxZone.code
    taxZoneForm.description = taxZone.description || ''
    taxZoneForm.is_active = taxZone.is_active
    taxZoneForm.locations = taxZone.locations.map(loc => ({
      id: loc.id,
      country_code: loc.country_code,
      state_code: loc.state_code,
    }))
  } else {
    taxZoneForm.id = null
    taxZoneForm.name = ''
    taxZoneForm.code = ''
    taxZoneForm.description = ''
    taxZoneForm.is_active = true
    taxZoneForm.locations = []
  }
  showTaxZoneModal.value = true
}

const addLocation = () => {
  taxZoneForm.locations.push({
    country_code: '',
    state_code: null,
  })
}

const removeLocation = (index: number) => {
  taxZoneForm.locations.splice(index, 1)
}

const saveTaxZone = () => {
  processing.value = true
  const url = taxZoneForm.id
    ? `/admin/settings/tax-zones/${taxZoneForm.id}`
    : '/admin/settings/tax-zones'

  const method = taxZoneForm.id ? 'put' : 'post'

  router[method](url, taxZoneForm, {
    preserveScroll: true,
    onSuccess: () => {
      showTaxZoneModal.value = false
      processing.value = false
    },
    onError: () => {
      processing.value = false
    },
  })
}

const confirmDeleteTaxZone = (id: number) => {
  deleteId.value = id
  deleteType.value = 'zone'
  deleteMessage.value = 'Are you sure you want to delete this tax zone? This may affect existing tax rules.'
  showDeleteModal.value = true
}

const deleteTaxZone = () => {
  if (deleteId.value) {
    router.delete(`/admin/settings/tax-zones/${deleteId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        showDeleteModal.value = false
        deleteId.value = null
      },
    })
  }
}

// Generic Delete Function
const deleteItem = () => {
  switch (deleteType.value) {
    case 'rule':
      deleteTaxRule()
      break
    case 'class':
      deleteTaxClass()
      break
    case 'rate':
      deleteTaxRate()
      break
    case 'zone':
      deleteTaxZone()
      break
  }
}
</script>
