<template>
  <AdminLayout title="Tax Rules">
    <template #default>
      <Head title="Tax Rules" />
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Tax Configuration</h1>
        <p class="mt-2 text-sm text-gray-600">Manage tax rules, classes, rates, and zones for your store</p>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Tax Rules Tab -->
          <div v-show="activeTab === 'rules'">
            <div class="mb-4 flex justify-between items-center">
              <div class="flex-1 max-w-md">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search tax rules..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <Link
                href="/admin/settings/tax-rules/create"
                class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                Create Tax Rule
              </Link>
            </div>

            <!-- Tax Rules Table -->
            <div v-if="taxRules.data.length === 0" class="text-center py-8 text-gray-500">
              <p>No tax rules found. Create your first tax rule to get started.</p>
            </div>

            <table v-else class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tax Class</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Zone</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="rule in taxRules.data" :key="rule.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ rule.name }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ rule.tax_class.name }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ rule.tax_zone.name }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ rule.tax_rate.percentage }}%</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ rule.priority }}</td>
                  <td class="px-4 py-3">
                    <span
                      :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        rule.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-gray-100 text-gray-800'
                      ]"
                    >
                      {{ rule.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right space-x-2">
                    <Link
                      :href="`/admin/settings/tax-rules/${rule.id}/edit`"
                      class="inline-flex items-center text-blue-600 hover:text-blue-800"
                      title="Edit"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </Link>
                    <button
                      @click="confirmDelete(rule.id)"
                      class="inline-flex items-center text-red-600 hover:text-red-800"
                      title="Delete"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="taxRules.data.length > 0" class="mt-4 flex justify-between items-center">
              <div class="text-sm text-gray-600">
                Showing {{ taxRules.from }} to {{ taxRules.to }} of {{ taxRules.total }} results
              </div>
              <div class="flex space-x-2">
                <a
                  v-for="link in taxRules.links"
                  :key="link.label"
                  :href="link.url || '#'"
                  :class="[
                    'px-3 py-1 border rounded',
                    link.active
                      ? 'bg-blue-600 text-white border-blue-600'
                      : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50',
                    !link.url && 'opacity-50 cursor-not-allowed'
                  ]"
                  v-html="link.label"
                />
              </div>
            </div>
          </div>

          <!-- Tax Classes Tab -->
          <div v-show="activeTab === 'classes'">
            <div class="mb-4 flex justify-between items-center">
              <div>
                <h3 class="text-lg font-semibold mb-2">Tax Classes</h3>
                <p class="text-sm text-gray-600">Tax classes are used to categorize products for tax calculation purposes.</p>
              </div>
              <button
                @click="openTaxClassModal()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                Create Tax Class
              </button>
            </div>

            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Default</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="taxClass in taxClasses" :key="taxClass.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ taxClass.name }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ taxClass.code }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ taxClass.description || '—' }}</td>
                  <td class="px-4 py-3 text-center">
                    <span
                      v-if="taxClass.is_default"
                      class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800"
                    >
                      Default
                    </span>
                    <span v-else class="text-gray-400">—</span>
                  </td>
                  <td class="px-4 py-3 text-right space-x-2">
                    <button
                      @click="openTaxClassModal(taxClass)"
                      class="inline-flex items-center text-blue-600 hover:text-blue-800"
                      title="Edit"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="confirmDeleteTaxClass(taxClass.id)"
                      class="inline-flex items-center text-red-600 hover:text-red-800"
                      title="Delete"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Tax Rates Tab -->
          <div v-show="activeTab === 'rates'">
            <div class="mb-4 flex justify-between items-center">
              <div>
                <h3 class="text-lg font-semibold mb-2">Tax Rates</h3>
                <p class="text-sm text-gray-600">Tax rates define the percentage to be applied in tax calculations.</p>
              </div>
              <button
                @click="openTaxRateModal()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                Create Tax Rate
              </button>
            </div>

            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Compound</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="rate in taxRates" :key="rate.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ rate.name }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ rate.code }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ rate.percentage }}%</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ rate.priority }}</td>
                  <td class="px-4 py-3 text-center">
                    <span
                      v-if="rate.is_compound"
                      class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800"
                    >
                      Yes
                    </span>
                    <span v-else class="text-gray-400">No</span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span
                      :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        rate.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-gray-100 text-gray-800'
                      ]"
                    >
                      {{ rate.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right space-x-2">
                    <button
                      @click="openTaxRateModal(rate)"
                      class="inline-flex items-center text-blue-600 hover:text-blue-800"
                      title="Edit"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="confirmDeleteTaxRate(rate.id)"
                      class="inline-flex items-center text-red-600 hover:text-red-800"
                      title="Delete"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Tax Zones Tab -->
          <div v-show="activeTab === 'zones'">
            <div class="mb-4 flex justify-between items-center">
              <div>
                <h3 class="text-lg font-semibold mb-2">Tax Zones</h3>
                <p class="text-sm text-gray-600">Tax zones define geographic regions where specific tax rules apply.</p>
              </div>
              <button
                @click="openTaxZoneModal()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                Create Tax Zone
              </button>
            </div>

            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Locations</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="zone in taxZones" :key="zone.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ zone.name }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ zone.code }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ zone.description || '—' }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">
                    <span class="px-2 py-1 text-xs bg-gray-100 rounded">
                      {{ zone.locations.length }} location(s)
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span
                      :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        zone.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-gray-100 text-gray-800'
                      ]"
                    >
                      {{ zone.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right space-x-2">
                    <button
                      @click="openTaxZoneModal(zone)"
                      class="inline-flex items-center text-blue-600 hover:text-blue-800"
                      title="Edit"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="confirmDeleteTaxZone(zone.id)"
                      class="inline-flex items-center text-red-600 hover:text-red-800"
                      title="Delete"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="showDeleteModal = false"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-semibold mb-4">Confirm Delete</h3>
          <p class="text-gray-600 mb-6">{{ deleteMessage }}</p>
          <div class="flex justify-end space-x-3">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="deleteItem"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Tax Class Modal -->
      <div
        v-if="showTaxClassModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="showTaxClassModal = false"
      >
        <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
          <h3 class="text-lg font-semibold mb-4">{{ taxClassForm.id ? 'Edit Tax Class' : 'Create Tax Class' }}</h3>
          <form @submit.prevent="saveTaxClass">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input
                  v-model="taxClassForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
                <input
                  v-model="taxClassForm.code"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                  v-model="taxClassForm.description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div class="flex items-center">
                <input
                  v-model="taxClassForm.is_default"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <label class="ml-2 text-sm font-medium text-gray-700">Set as default</label>
              </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="showTaxClassModal = false"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="processing"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                {{ processing ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tax Rate Modal -->
      <div
        v-if="showTaxRateModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="showTaxRateModal = false"
      >
        <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
          <h3 class="text-lg font-semibold mb-4">{{ taxRateForm.id ? 'Edit Tax Rate' : 'Create Tax Rate' }}</h3>
          <form @submit.prevent="saveTaxRate">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input
                  v-model="taxRateForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
                <input
                  v-model="taxRateForm.code"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Percentage (%) *</label>
                <input
                  v-model="taxRateForm.percentage"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
                <input
                  v-model="taxRateForm.priority"
                  type="number"
                  min="1"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div class="flex items-center">
                <input
                  v-model="taxRateForm.is_compound"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <label class="ml-2 text-sm font-medium text-gray-700">Is compound tax</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="taxRateForm.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <label class="ml-2 text-sm font-medium text-gray-700">Active</label>
              </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="showTaxRateModal = false"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="processing"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                {{ processing ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tax Zone Modal -->
      <div
        v-if="showTaxZoneModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="showTaxZoneModal = false"
      >
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
          <h3 class="text-lg font-semibold mb-4">{{ taxZoneForm.id ? 'Edit Tax Zone' : 'Create Tax Zone' }}</h3>
          <form @submit.prevent="saveTaxZone">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input
                  v-model="taxZoneForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
                <input
                  v-model="taxZoneForm.code"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                  v-model="taxZoneForm.description"
                  rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div class="flex items-center">
                <input
                  v-model="taxZoneForm.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <label class="ml-2 text-sm font-medium text-gray-700">Active</label>
              </div>

              <!-- Locations -->
              <div class="border-t pt-4">
                <div class="flex justify-between items-center mb-3">
                  <label class="block text-sm font-medium text-gray-700">Locations</label>
                  <button
                    type="button"
                    @click="addLocation"
                    class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                  >
                    Add Location
                  </button>
                </div>
                <div v-for="(location, index) in taxZoneForm.locations" :key="index" class="flex gap-2 mb-2">
                  <input
                    v-model="location.country_code"
                    type="text"
                    placeholder="Country Code (e.g., US, IN)"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <input
                    v-model="location.state_code"
                    type="text"
                    placeholder="State (optional)"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <button
                    type="button"
                    @click="removeLocation(index)"
                    class="px-3 py-2 text-red-600 hover:text-red-800"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="showTaxZoneModal = false"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="processing"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                {{ processing ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router, Link, Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

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
  { id: 'rules', name: 'Tax Rules' },
  { id: 'classes', name: 'Tax Classes' },
  { id: 'rates', name: 'Tax Rates' },
  { id: 'zones', name: 'Tax Zones' },
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
