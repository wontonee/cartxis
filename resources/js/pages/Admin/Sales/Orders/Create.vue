<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed, watch } from 'vue';
import { useCurrency } from '@/composables/useCurrency';

interface Product {
  id: number;
  name: string;
  price: string;
  quantity: number;
}

interface Props {
  customers: Array<{ id: number; name: string; email: string }>;
  products: Product[];
}

const props = defineProps<Props>();

const { formatPrice } = useCurrency();

const form = useForm({
  customer_email: '',
  customer_phone: '',
  payment_method: '',
  shipping_method: '',
  notes: '',
  items: [] as Array<{
    product_id: number;
    product_name: string;
    quantity: number;
    price: number;
    available_quantity: number;
  }>,
  shipping_address: {
    first_name: '',
    last_name: '',
    phone: '',
    address_line1: '',
    address_line2: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'IN',
  },
  billing_address: {
    first_name: '',
    last_name: '',
    phone: '',
    address_line1: '',
    address_line2: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'IN',
  },
  subtotal: 0,
  tax: 0,
  shipping_cost: 0,
  discount: 0,
  total: 0,
});

// Product selection
const selectedProductId = ref<number | null>(null);
const selectedQuantity = ref(1);
const searchQuery = ref('');

const filteredProducts = computed(() => {
  if (!searchQuery.value) return props.products;
  const query = searchQuery.value.toLowerCase();
  return props.products.filter(p => 
    p.name.toLowerCase().includes(query)
  );
});

const selectedProduct = computed(() => {
  if (!selectedProductId.value) return null;
  return props.products.find(p => p.id === selectedProductId.value);
});

function addProduct() {
  if (!selectedProduct.value) return;
  
  const existingItem = form.items.find(item => item.product_id === selectedProduct.value!.id);
  
  if (existingItem) {
    // Update quantity if product already in cart
    existingItem.quantity += selectedQuantity.value;
  } else {
    // Add new item
    form.items.push({
      product_id: selectedProduct.value.id,
      product_name: selectedProduct.value.name,
      quantity: selectedQuantity.value,
      price: parseFloat(selectedProduct.value.price),
      available_quantity: selectedProduct.value.quantity,
    });
  }
  
  // Reset selection
  selectedProductId.value = null;
  selectedQuantity.value = 1;
  searchQuery.value = '';
  
  calculateTotals();
}

function removeItem(index: number) {
  form.items.splice(index, 1);
  calculateTotals();
}

function updateItemQuantity(index: number, quantity: number) {
  if (quantity > 0) {
    form.items[index].quantity = quantity;
    calculateTotals();
  }
}

function calculateTotals() {
  // Calculate subtotal
  form.subtotal = form.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  
  // Calculate tax (10% for demo)
  form.tax = form.subtotal * 0.1;
  
  // Set shipping cost based on method
  form.shipping_cost = form.shipping_method === 'express' ? 15 : form.shipping_method === 'standard' ? 5 : 0;
  
  // Calculate total
  form.total = form.subtotal + form.tax + form.shipping_cost - form.discount;
}

// Watch shipping method to recalculate totals
watch(() => form.shipping_method, calculateTotals);
watch(() => form.discount, calculateTotals);

function submit() {
  if (form.items.length === 0) {
    alert('Please add at least one product to the order');
    return;
  }
  
  // Explicitly create complete billing address object
  const billingAddress = {
    first_name: form.shipping_address.first_name,
    last_name: form.shipping_address.last_name,
    phone: form.shipping_address.phone,
    address_line1: form.shipping_address.address_line1,
    address_line2: form.shipping_address.address_line2,
    city: form.shipping_address.city,
    state: form.shipping_address.state,
    postal_code: form.shipping_address.postal_code,
    country: form.shipping_address.country,
  };
  
  // Post with explicit billing_address
  form.transform((data) => ({
    ...data,
    billing_address: billingAddress,
  })).post('/admin/sales/orders', {
    preserveScroll: true,
  });
}
</script>


<template>
  <Head title="Create Order" />

  <AdminLayout title="Create Order">
    <div class="p-6">
      <!-- Back Button -->
      <div class="mb-4">
        <button
          type="button"
          @click="router.visit('/admin/sales/orders')"
          class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Orders
        </button>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Customer Information -->
          <div>
            <h2 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h2>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer Email *</label>
                <input
                  v-model="form.customer_email"
                  type="email"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
                <div v-if="form.errors.customer_email" class="text-red-600 text-sm mt-1">{{ form.errors.customer_email }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input
                  v-model="form.customer_phone"
                  type="tel"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Products Selection -->
          <div>
            <h2 class="text-lg font-medium text-gray-900 mb-4">Products</h2>
            
            <!-- Add Product Form -->
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
              <div class="grid grid-cols-12 gap-4 items-end">
                <div class="col-span-6">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Search Product</label>
                  <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by name..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                  />
                  <select
                    v-model="selectedProductId"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mt-2"
                  >
                    <option :value="null">Select a product...</option>
                    <option 
                      v-for="product in filteredProducts" 
                      :key="product.id" 
                      :value="product.id"
                    >
                      {{ product.name }} - {{ formatPrice(parseFloat(product.price)) }} (Stock: {{ product.quantity }})
                    </option>
                  </select>
                </div>
                <div class="col-span-3">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                  <input
                    v-model.number="selectedQuantity"
                    type="number"
                    min="1"
                    :max="selectedProduct?.quantity || 999"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div class="col-span-3">
                  <button
                    type="button"
                    @click="addProduct"
                    :disabled="!selectedProductId"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Add to Order
                  </button>
                </div>
              </div>
            </div>

            <!-- Order Items Table -->
            <div v-if="form.items.length > 0" class="border border-gray-300 rounded-lg overflow-hidden">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(item, index) in form.items" :key="index">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ item.product_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatPrice(item.price) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <input
                        :value="item.quantity"
                        @input="updateItemQuantity(index, parseInt(($event.target as HTMLInputElement).value))"
                        type="number"
                        min="1"
                        :max="item.available_quantity"
                        class="w-20 px-2 py-1 border border-gray-300 rounded"
                      />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatPrice(item.price * item.quantity) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <button
                        type="button"
                        @click="removeItem(index)"
                        class="text-red-600 hover:text-red-900"
                      >
                        Remove
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-center py-8 text-gray-500 border border-gray-300 rounded-lg border-dashed">
              No products added yet. Use the form above to add products to this order.
            </div>
          </div>

          <!-- Order Details -->
          <div>
            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Details</h2>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                <select
                  v-model="form.payment_method"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Select payment method...</option>
                  <option value="stripe">Stripe</option>
                  <option value="cod">Cash on Delivery</option>
                  <option value="bank_transfer">Bank Transfer</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Method *</label>
                <select
                  v-model="form.shipping_method"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Select shipping method...</option>
                  <option value="standard">Standard Shipping ({{ formatPrice(5.00) }})</option>
                  <option value="express">Express Shipping ({{ formatPrice(15.00) }})</option>
                  <option value="pickup">Local Pickup (Free)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Shipping Address -->
          <div>
            <h2 class="text-lg font-medium text-gray-900 mb-4">Shipping Address</h2>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                <input
                  v-model="form.shipping_address.first_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                <input
                  v-model="form.shipping_address.last_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                <input
                  v-model="form.shipping_address.phone"
                  type="tel"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1 *</label>
                <input
                  v-model="form.shipping_address.address_line1"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                <input
                  v-model="form.shipping_address.city"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">State *</label>
                <input
                  v-model="form.shipping_address.state"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code *</label>
                <input
                  v-model="form.shipping_address.postal_code"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-medium">{{ formatPrice(form.subtotal) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Tax (10%):</span>
                <span class="font-medium">{{ formatPrice(form.tax) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Shipping:</span>
                <span class="font-medium">{{ formatPrice(form.shipping_cost) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <label class="text-gray-600">Discount:</label>
                <input
                  v-model.number="form.discount"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-24 px-2 py-1 border border-gray-300 rounded text-right"
                  placeholder="0.00"
                />
              </div>
              <div class="border-t border-gray-300 pt-2 mt-2">
                <div class="flex justify-between text-lg font-bold">
                  <span>Total:</span>
                  <span class="text-blue-600">{{ formatPrice(form.total) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="Optional notes about this order..."
            ></textarea>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end gap-3">
            <button
              type="button"
              @click="router.visit('/admin/sales/orders')"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="form.processing || form.items.length === 0"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ form.processing ? 'Creating...' : 'Create Order' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
