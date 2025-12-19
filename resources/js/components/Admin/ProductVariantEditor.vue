<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from '@/lib/axios';
import { useCurrency } from '@/composables/useCurrency';

const { formatPrice } = useCurrency();

interface Attribute {
  id: number;
  code: string;
  name: string;
  type: string;
  is_configurable: boolean;
  options: AttributeOption[];
}

interface AttributeOption {
  id: number;
  label: string;
  value: string;
  swatch_value: string | null;
}

interface Variant {
  id?: number;
  sku: string;
  name: string;
  price: string;
  special_price: string | null;
  quantity: number;
  is_enabled: boolean;
  attributes: Record<number, number>; // attribute_id => option_id
  image_id?: number | null;
}

interface Props {
  productId: number;
  basePrice: string;
  baseSku: string;
  configurable_attributes?: Attribute[];
  existing_variants?: Variant[];
}

const props = withDefaults(defineProps<Props>(), {
  configurable_attributes: () => [],
  existing_variants: () => [],
});

const emit = defineEmits(['update:variants']);

// State
const variants = ref<Variant[]>(props.existing_variants || []);
const selectedAttributes = ref<Record<number, number[]>>({});
const showVariantForm = ref(false);
const editingVariant = ref<Variant | null>(null);
const generating = ref(false);

// Initialize selected attributes
props.configurable_attributes.forEach(attr => {
  selectedAttributes.value[attr.id] = [];
});

// Generate all combinations of selected attribute options
const generateVariants = () => {
  if (Object.keys(selectedAttributes.value).length === 0) {
    alert('Please select at least one configurable attribute');
    return;
  }

  generating.value = true;

  // Get all selected options for each attribute
  const attributeSelections: Array<{ attributeId: number; optionIds: number[] }> = [];
  
  for (const [attrId, optionIds] of Object.entries(selectedAttributes.value)) {
    if (optionIds.length > 0) {
      attributeSelections.push({
        attributeId: parseInt(attrId),
        optionIds: optionIds,
      });
    }
  }

  if (attributeSelections.length === 0) {
    alert('Please select attribute options to generate variants');
    generating.value = false;
    return;
  }

  // Generate combinations
  const combinations = cartesianProduct(attributeSelections);
  
  // Create variant for each combination
  const newVariants: Variant[] = combinations.map((combo, index) => {
    // Build variant name from attribute options
    const variantNameParts: string[] = [];
    const attributes: Record<number, number> = {};
    
    combo.forEach(({ attributeId, optionId }) => {
      const attr = props.configurable_attributes.find(a => a.id === attributeId);
      const option = attr?.options.find(o => o.id === optionId);
      if (option) {
        variantNameParts.push(option.label);
      }
      attributes[attributeId] = optionId;
    });

    // Generate SKU
    const variantSuffix = variantNameParts.map(part => 
      part.substring(0, 3).toUpperCase()
    ).join('-');

    return {
      sku: `${props.baseSku}-${variantSuffix}-${Date.now()}-${index}`,
      name: variantNameParts.join(' / '),
      price: props.basePrice,
      special_price: null,
      quantity: 0,
      is_enabled: true,
      attributes: attributes,
    };
  });

  variants.value = [...variants.value, ...newVariants];
  generating.value = false;
  emit('update:variants', variants.value);
};

// Helper function to generate cartesian product
function cartesianProduct(
  selections: Array<{ attributeId: number; optionIds: number[] }>
): Array<Array<{ attributeId: number; optionId: number }>> {
  if (selections.length === 0) return [[]];
  
  const [first, ...rest] = selections;
  const restProduct = cartesianProduct(rest);
  
  const result: Array<Array<{ attributeId: number; optionId: number }>> = [];
  
  for (const optionId of first.optionIds) {
    for (const combo of restProduct) {
      result.push([
        { attributeId: first.attributeId, optionId },
        ...combo,
      ]);
    }
  }
  
  return result;
}

// Get attribute option label
const getOptionLabel = (attributeId: number, optionId: number): string => {
  const attr = props.configurable_attributes.find(a => a.id === attributeId);
  const option = attr?.options.find(o => o.id === optionId);
  return option?.label || 'Unknown';
};

// Get attribute name
const getAttributeName = (attributeId: number): string => {
  const attr = props.configurable_attributes.find(a => a.id === attributeId);
  return attr?.name || 'Unknown';
};

// Remove variant
const removeVariant = (index: number) => {
  if (confirm('Are you sure you want to remove this variant?')) {
    variants.value.splice(index, 1);
    emit('update:variants', variants.value);
  }
};

// Edit variant
const editVariant = (variant: Variant) => {
  editingVariant.value = { ...variant };
  showVariantForm.value = true;
};

// Save edited variant
const saveVariant = () => {
  if (!editingVariant.value) return;
  
  const index = variants.value.findIndex(v => v.sku === editingVariant.value!.sku);
  if (index !== -1) {
    variants.value[index] = editingVariant.value;
    emit('update:variants', variants.value);
  }
  
  showVariantForm.value = false;
  editingVariant.value = null;
};

// Bulk update prices
const bulkUpdatePrices = () => {
  const newPrice = prompt('Enter new price for all variants:', props.basePrice);
  if (newPrice && !isNaN(parseFloat(newPrice))) {
    variants.value.forEach(variant => {
      variant.price = parseFloat(newPrice).toFixed(2);
    });
    emit('update:variants', variants.value);
  }
};

// Bulk update quantities
const bulkUpdateQuantities = () => {
  const newQuantity = prompt('Enter new quantity for all variants:', '0');
  if (newQuantity && !isNaN(parseInt(newQuantity))) {
    variants.value.forEach(variant => {
      variant.quantity = parseInt(newQuantity);
    });
    emit('update:variants', variants.value);
  }
};

// Total variants count
const totalVariants = computed(() => variants.value.length);
const enabledVariants = computed(() => variants.value.filter(v => v.is_enabled).length);
const totalStock = computed(() => variants.value.reduce((sum, v) => sum + v.quantity, 0));
</script>

<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-gray-900">Product Variants</h3>
      <div class="flex gap-2">
        <span class="text-sm text-gray-600">
          {{ enabledVariants }} / {{ totalVariants }} enabled | Stock: {{ totalStock }}
        </span>
      </div>
    </div>

    <!-- Attribute Selection -->
    <div v-if="configurable_attributes.length > 0" class="mb-6">
      <h4 class="text-sm font-medium text-gray-700 mb-3">1. Select Configurable Attributes</h4>
      
      <div class="space-y-4">
        <div
          v-for="attribute in configurable_attributes"
          :key="attribute.id"
          class="border border-gray-200 rounded-lg p-4"
        >
          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ attribute.name }}
          </label>
          
          <div class="flex flex-wrap gap-2">
            <label
              v-for="option in attribute.options"
              :key="option.id"
              class="inline-flex items-center cursor-pointer"
            >
              <input
                type="checkbox"
                v-model="selectedAttributes[attribute.id]"
                :value="option.id"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-700">
                {{ option.label }}
                <span
                  v-if="option.swatch_value && attribute.code === 'color'"
                  class="inline-block w-4 h-4 ml-1 border border-gray-300 rounded"
                  :style="{ backgroundColor: option.swatch_value }"
                ></span>
              </span>
            </label>
          </div>
        </div>
      </div>

      <button
        @click="generateVariants"
        :disabled="generating"
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"
      >
        <span v-if="generating">Generating...</span>
        <span v-else>Generate Variants</span>
      </button>
    </div>

    <!-- No Configurable Attributes -->
    <div v-else class="text-center py-8 bg-gray-50 rounded-lg">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
      </svg>
      <p class="mt-2 text-sm text-gray-600">
        No configurable attributes available. Please create attributes first.
      </p>
    </div>

    <!-- Variants List -->
    <div v-if="variants.length > 0" class="mt-8">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-sm font-medium text-gray-700">2. Manage Variants ({{ variants.length }})</h4>
        <div class="flex gap-2">
          <button
            @click="bulkUpdatePrices"
            class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors cursor-pointer"
          >
            Bulk Update Prices
          </button>
          <button
            @click="bulkUpdateQuantities"
            class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors cursor-pointer"
          >
            Bulk Update Stock
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Variant</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(variant, index) in variants" :key="index">
              <td class="px-4 py-3 text-sm text-gray-900">
                {{ variant.sku }}
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ variant.name }}</div>
                <div class="text-xs text-gray-500 mt-1">
                  <span
                    v-for="(optionId, attrId) in variant.attributes"
                    :key="attrId"
                    class="inline-block mr-2"
                  >
                    {{ getAttributeName(Number(attrId)) }}: {{ getOptionLabel(Number(attrId), optionId) }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm text-gray-900">{{ formatPrice(variant.price) }}</div>
                <div v-if="variant.special_price" class="text-xs text-green-600">
                  Sale: {{ formatPrice(variant.special_price) }}
                </div>
              </td>
              <td class="px-4 py-3">
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': variant.quantity > 10,
                    'bg-yellow-100 text-yellow-800': variant.quantity > 0 && variant.quantity <= 10,
                    'bg-red-100 text-red-800': variant.quantity === 0
                  }"
                >
                  {{ variant.quantity }} units
                </span>
              </td>
              <td class="px-4 py-3">
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': variant.is_enabled,
                    'bg-gray-100 text-gray-800': !variant.is_enabled
                  }"
                >
                  {{ variant.is_enabled ? 'Enabled' : 'Disabled' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <button
                    @click="editVariant(variant)"
                    class="text-blue-600 hover:text-blue-800 text-sm cursor-pointer"
                  >
                    Edit
                  </button>
                  <button
                    @click="removeVariant(index)"
                    class="text-red-600 hover:text-red-800 text-sm cursor-pointer"
                  >
                    Remove
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Edit Variant Modal -->
    <div
      v-if="showVariantForm && editingVariant"
      class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Variant</h3>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
              <input
                v-model="editingVariant.sku"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
              <input
                v-model="editingVariant.price"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Special Price</label>
              <input
                v-model="editingVariant.special_price"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
              <input
                v-model.number="editingVariant.quantity"
                type="number"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div class="flex items-center">
              <input
                v-model="editingVariant.is_enabled"
                type="checkbox"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <label class="ml-2 text-sm text-gray-700">Enabled</label>
            </div>
          </div>

          <div class="flex gap-3 mt-6">
            <button
              @click="saveVariant"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors cursor-pointer"
            >
              Save Changes
            </button>
            <button
              @click="showVariantForm = false; editingVariant = null"
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
