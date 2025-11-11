<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import ThemeLayout from '@/../../themes/vortex-default/resources/views/layouts/ThemeLayout.vue';
import { ref } from 'vue';

interface Address {
  id: number;
  label?: string;
  first_name: string;
  last_name: string;
  company?: string;
  address_line1: string;
  address_line2?: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
  phone: string;
  is_default: boolean;
  type: 'shipping' | 'billing';
}

interface Props {
  addresses: Address[];
}

const props = defineProps<Props>();

const showAddModal = ref(false);
const showEditModal = ref(false);
const editingAddress = ref<Address | null>(null);

// Add Address Form
const addForm = useForm({
  label: '',
  first_name: '',
  last_name: '',
  company: '',
  address_line1: '',
  address_line2: '',
  city: '',
  state: '',
  postal_code: '',
  country: 'India',
  phone: '',
  is_default: false,
  address_type: 'shipping' as 'shipping' | 'billing',
});

const openAddModal = () => {
  addForm.reset();
  showAddModal.value = true;
};

const submitAddForm = () => {
  addForm.post('/account/addresses', {
    preserveScroll: true,
    onSuccess: () => {
      showAddModal.value = false;
      addForm.reset();
    },
  });
};

// Edit Address Form
const editForm = useForm({
  label: '',
  first_name: '',
  last_name: '',
  company: '',
  address_line1: '',
  address_line2: '',
  city: '',
  state: '',
  postal_code: '',
  country: '',
  phone: '',
  is_default: false,
  address_type: 'shipping' as 'shipping' | 'billing',
});

const openEditModal = (address: Address) => {
  editingAddress.value = address;
  editForm.label = address.label || '';
  editForm.first_name = address.first_name;
  editForm.last_name = address.last_name;
  editForm.company = address.company || '';
  editForm.address_line1 = address.address_line1;
  editForm.address_line2 = address.address_line2 || '';
  editForm.city = address.city;
  editForm.state = address.state;
  editForm.postal_code = address.postal_code;
  editForm.country = address.country;
  editForm.phone = address.phone;
  editForm.is_default = address.is_default;
  editForm.type = address.type;
  showEditModal.value = true;
};

const submitEditForm = () => {
  if (!editingAddress.value) return;
  
  editForm.put(`/account/addresses/${editingAddress.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false;
      editingAddress.value = null;
    },
  });
};

// Set Default Address
const setDefault = (address: Address) => {
  useForm({}).put(`/account/addresses/${address.id}/default`, {
    preserveScroll: true,
  });
};

// Delete Address
const deleteAddress = (address: Address) => {
  if (confirm('Are you sure you want to delete this address?')) {
    useForm({}).delete(`/account/addresses/${address.id}`, {
      preserveScroll: true,
    });
  }
};

const formatAddress = (address: Address) => {
  const parts = [
    address.company,
    address.address_line1,
    address.address_line2,
    `${address.city}, ${address.state} ${address.postal_code}`,
    address.country,
  ].filter(Boolean);
  return parts.join(', ');
};
</script>

<template>
  <ThemeLayout>
    <Head title="My Addresses" />

    <div class="container mx-auto px-4 py-8">
      <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold mb-2">My Addresses</h1>
            <p class="text-gray-600">Manage your shipping and billing addresses</p>
          </div>
          <button
            @click="openAddModal"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            + Add New Address
          </button>
        </div>

        <!-- Addresses Grid -->
        <div v-if="addresses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="address in addresses"
            :key="address.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 relative"
          >
            <!-- Default Badge -->
            <div v-if="address.is_default" class="absolute top-4 right-4">
              <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                Default {{ address.type }}
              </span>
            </div>

            <!-- Address Label -->
            <h3 v-if="address.label" class="text-lg font-semibold mb-3">{{ address.label }}</h3>
            <h3 v-else class="text-lg font-semibold mb-3 text-gray-500">{{ address.type }} Address</h3>

            <!-- Address Details -->
            <div class="space-y-1 text-sm text-gray-700 mb-4">
              <p class="font-medium">{{ address.first_name }} {{ address.last_name }}</p>
              <p v-if="address.company">{{ address.company }}</p>
              <p>{{ address.address_line1 }}</p>
              <p v-if="address.address_line2">{{ address.address_line2 }}</p>
              <p>{{ address.city }}, {{ address.state }} {{ address.postal_code }}</p>
              <p>{{ address.country }}</p>
              <p class="pt-1">{{ address.phone }}</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
              <button
                v-if="!address.is_default"
                @click="setDefault(address)"
                class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50 transition-colors"
              >
                Set Default
              </button>
              <button
                @click="openEditModal(address)"
                class="flex-1 px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
              >
                Edit
              </button>
              <button
                @click="deleteAddress(address)"
                class="px-3 py-1.5 text-sm border border-red-300 text-red-600 rounded hover:bg-red-50 transition-colors"
              >
                Delete
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <h3 class="text-lg font-semibold mb-2">No addresses yet</h3>
          <p class="text-gray-600 mb-4">Add your first address to make checkout faster</p>
          <button
            @click="openAddModal"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Add Address
          </button>
        </div>
      </div>
    </div>

    <!-- Add Address Modal -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showAddModal = false"
    >
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
          <h2 class="text-xl font-semibold">Add New Address</h2>
          <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitAddForm" class="p-6 space-y-4">
          <!-- Address Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Address Type <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-4">
              <label class="flex items-center cursor-pointer">
                <input
                  v-model="addForm.address_type"
                  type="radio"
                  value="shipping"
                  class="mr-2"
                />
                <span>Shipping Address</span>
              </label>
              <label class="flex items-center cursor-pointer">
                <input
                  v-model="addForm.address_type"
                  type="radio"
                  value="billing"
                  class="mr-2"
                />
                <span>Billing Address</span>
              </label>
            </div>
          </div>

          <!-- Label -->
          <div>
            <label for="add-label" class="block text-sm font-medium text-gray-700 mb-1">
              Address Label (Optional)
            </label>
            <input
              id="add-label"
              v-model="addForm.label"
              type="text"
              placeholder="Home, Office, etc."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Name Fields -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="add-first-name" class="block text-sm font-medium text-gray-700 mb-1">
                First Name <span class="text-red-500">*</span>
              </label>
              <input
                id="add-first-name"
                v-model="addForm.first_name"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label for="add-last-name" class="block text-sm font-medium text-gray-700 mb-1">
                Last Name <span class="text-red-500">*</span>
              </label>
              <input
                id="add-last-name"
                v-model="addForm.last_name"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Company -->
          <div>
            <label for="add-company" class="block text-sm font-medium text-gray-700 mb-1">
              Company (Optional)
            </label>
            <input
              id="add-company"
              v-model="addForm.company"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Address Lines -->
          <div>
            <label for="add-address1" class="block text-sm font-medium text-gray-700 mb-1">
              Address Line 1 <span class="text-red-500">*</span>
            </label>
            <input
              id="add-address1"
              v-model="addForm.address_line1"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <div>
            <label for="add-address2" class="block text-sm font-medium text-gray-700 mb-1">
              Address Line 2 (Optional)
            </label>
            <input
              id="add-address2"
              v-model="addForm.address_line2"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- City, State, Postal -->
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label for="add-city" class="block text-sm font-medium text-gray-700 mb-1">
                City <span class="text-red-500">*</span>
              </label>
              <input
                id="add-city"
                v-model="addForm.city"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label for="add-state" class="block text-sm font-medium text-gray-700 mb-1">
                State <span class="text-red-500">*</span>
              </label>
              <input
                id="add-state"
                v-model="addForm.state"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label for="add-postal" class="block text-sm font-medium text-gray-700 mb-1">
                Postal Code <span class="text-red-500">*</span>
              </label>
              <input
                id="add-postal"
                v-model="addForm.postal_code"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Country & Phone -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="add-country" class="block text-sm font-medium text-gray-700 mb-1">
                Country <span class="text-red-500">*</span>
              </label>
              <input
                id="add-country"
                v-model="addForm.country"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label for="add-phone" class="block text-sm font-medium text-gray-700 mb-1">
                Phone <span class="text-red-500">*</span>
              </label>
              <input
                id="add-phone"
                v-model="addForm.phone"
                type="tel"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Set as Default -->
          <div>
            <label class="flex items-center cursor-pointer">
              <input
                v-model="addForm.is_default"
                type="checkbox"
                class="mr-2 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700">Set as default {{ addForm.address_type }} address</span>
            </label>
          </div>

          <!-- Submit Buttons -->
          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="addForm.processing"
              class="flex-1 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ addForm.processing ? 'Saving...' : 'Save Address' }}
            </button>
            <button
              type="button"
              @click="showAddModal = false"
              class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Address Modal (similar structure to Add modal) -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showEditModal = false"
    >
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
          <h2 class="text-xl font-semibold">Edit Address</h2>
          <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitEditForm" class="p-6 space-y-4">
          <!-- Same form fields as Add modal, but with editForm -->
          <!-- (Abbreviated for brevity - same structure) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Address Type <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-4">
              <label class="flex items-center cursor-pointer">
                <input v-model="editForm.address_type" type="radio" value="shipping" class="mr-2" />
                <span>Shipping Address</span>
              </label>
              <label class="flex items-center cursor-pointer">
                <input v-model="editForm.address_type" type="radio" value="billing" class="mr-2" />
                <span>Billing Address</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address Label (Optional)</label>
            <input v-model="editForm.label" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
              <input v-model="editForm.first_name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
              <input v-model="editForm.last_name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Company (Optional)</label>
            <input v-model="editForm.company" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1 <span class="text-red-500">*</span></label>
            <input v-model="editForm.address_line1" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2 (Optional)</label>
            <input v-model="editForm.address_line2" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
              <input v-model="editForm.city" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">State <span class="text-red-500">*</span></label>
              <input v-model="editForm.state" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code <span class="text-red-500">*</span></label>
              <input v-model="editForm.postal_code" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-red-500">*</span></label>
              <input v-model="editForm.country" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
              <input v-model="editForm.phone" type="tel" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
            </div>
          </div>

          <div>
            <label class="flex items-center cursor-pointer">
              <input v-model="editForm.is_default" type="checkbox" class="mr-2 w-4 h-4 text-blue-600 border-gray-300 rounded" />
              <span class="text-sm text-gray-700">Set as default {{ editForm.address_type }} address</span>
            </label>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="editForm.processing"
              class="flex-1 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ editForm.processing ? 'Updating...' : 'Update Address' }}
            </button>
            <button
              type="button"
              @click="showEditModal = false"
              class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </ThemeLayout>
</template>
