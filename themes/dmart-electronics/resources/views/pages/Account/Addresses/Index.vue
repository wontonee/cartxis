<script setup lang="ts">
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import ThemeLayout from '../../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import { MapPin, Plus, Trash2, Pencil, Check } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Address {
    id: number; first_name: string; last_name: string; address_line1: string;
    address_line2?: string; city: string; state: string; postal_code: string;
    country: string; phone?: string; is_default: boolean; type: 'shipping' | 'billing';
}

interface Country {
    name: string;
    code: string;
}

interface Props {
    addresses: Address[];
    countries: Country[];
    theme: { name: string; slug: string };
}

const props = defineProps<Props>();
const page = usePage();
const showForm = ref(false);
const editingId = ref<number | null>(null);
const flash = computed(() => (page.props as any).flash || {});

const form = useForm({
    first_name: '', last_name: '', address_line1: '', address_line2: '',
    city: '', state: '', postal_code: '', country: '', phone: '', is_default: false,
    address_type: 'shipping' as 'shipping' | 'billing',
});

const openAdd = () => {
    editingId.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (addr: Address) => {
    editingId.value = addr.id;
    form.first_name = addr.first_name;
    form.last_name = addr.last_name;
    form.address_line1 = addr.address_line1;
    form.address_line2 = addr.address_line2 || '';
    form.city = addr.city;
    form.state = addr.state;
    form.postal_code = addr.postal_code;
    form.country = addr.country;
    form.phone = addr.phone || '';
    form.is_default = addr.is_default;
    form.address_type = addr.type || 'shipping';
    showForm.value = true;
};

const submit = () => {
    if (editingId.value) {
        form.put(`/account/addresses/${editingId.value}`, { onSuccess: () => { showForm.value = false; form.reset(); } });
    } else {
        form.post('/account/addresses', { onSuccess: () => { showForm.value = false; form.reset(); } });
    }
};

const deleteAddress = (id: number) => {
    if (confirm('Are you sure you want to delete this address?')) {
        router.delete(`/account/addresses/${id}`);
    }
};

const breadcrumbs = [
    { label: 'My Account', url: '/account' },
    { label: 'Addresses' },
];
</script>

<template>
    <ThemeLayout>
        <Head title="My Addresses" />
        <Breadcrumb :items="breadcrumbs" />

        <section class="py-8 lg:py-12">
            <div class="dmart-container max-w-4xl">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-extrabold text-title font-title">My Addresses</h1>
                    <button @click="openAdd" class="dmart-btn dmart-btn-primary text-sm"><Plus class="w-4 h-4" /> Add Address</button>
                </div>

                <div v-if="flash.success" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ flash.success }}
                </div>
                <div v-if="flash.error" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ flash.error }}
                </div>

                <!-- Address Form -->
                <Transition name="slide-down">
                    <div v-if="showForm" class="border rounded-2xl p-6 mb-6">
                        <h3 class="font-bold mb-4 font-title">
                            {{ editingId ? 'Edit Address' : 'New Address' }}
                        </h3>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5">First Name *</label>
                                    <input v-model="form.first_name" type="text" required class="w-full border rounded-xl px-4 py-3 text-sm focus:border-theme-1 focus:ring-0" />
                                    <p v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5">Last Name *</label>
                                    <input v-model="form.last_name" type="text" required class="w-full border rounded-xl px-4 py-3 text-sm focus:border-theme-1 focus:ring-0" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5">Address Line 1 *</label>
                                <input v-model="form.address_line1" type="text" required class="w-full border rounded-xl px-4 py-3 text-sm focus:border-theme-1 focus:ring-0" />
                                <p v-if="form.errors.address_line1" class="text-red-500 text-xs mt-1">{{ form.errors.address_line1 }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5">Address Line 2</label>
                                <input v-model="form.address_line2" type="text" class="w-full border rounded-xl px-4 py-3 text-sm focus:border-theme-1 focus:ring-0" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5">City *</label>
                                    <input v-model="form.city" type="text" required class="w-full border rounded-xl px-4 py-3 text-sm focus:border-theme-1 focus:ring-0" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5">State *</label>
                                    <input v-model="form.state" type="text" required class="w-full border rounded-xl px-4 py-3 text-sm focus:border-theme-1 focus:ring-0" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5">Postal Code *</label>
                                    <input v-model="form.postal_code" type="text" required class="w-full border rounded-xl px-4 py-3 text-sm focus:border-theme-1 focus:ring-0" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5">Country *</label>
                                    <select v-model="form.country" required class="w-full border rounded-xl px-4 py-3 text-sm focus:border-theme-1 focus:ring-0">
                                        <option value="" disabled>Select country</option>
                                        <option v-for="country in countries" :key="country.code" :value="country.name">
                                            {{ country.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5">Phone *</label>
                                <input v-model="form.phone" type="tel" required class="w-full border rounded-xl px-4 py-3 text-sm focus:border-theme-1 focus:ring-0" />
                                <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5">Address Type *</label>
                                <select v-model="form.address_type" required class="w-full border rounded-xl px-4 py-3 text-sm focus:border-theme-1 focus:ring-0">
                                    <option value="shipping">Shipping</option>
                                    <option value="billing">Billing</option>
                                </select>
                            </div>
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input v-model="form.is_default" type="checkbox" class="rounded accent-theme-1" />
                                Set as default address
                            </label>
                            <div class="flex gap-3">
                                <button type="submit" :disabled="form.processing" class="dmart-btn dmart-btn-primary py-3">
                                    {{ form.processing ? 'Saving...' : editingId ? 'Update Address' : 'Save Address' }}
                                </button>
                                <button type="button" @click="showForm = false" class="dmart-btn dmart-btn-outline py-3">Cancel</button>
                            </div>
                        </form>
                    </div>
                </Transition>

                <!-- Address List -->
                <div v-if="addresses.length > 0" class="grid md:grid-cols-2 gap-4">
                    <div v-for="addr in addresses" :key="addr.id" class="border rounded-2xl p-5 relative">
                        <span v-if="addr.is_default" class="absolute top-3 right-3 flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            <Check class="w-3 h-3" /> Default
                        </span>
                        <div class="flex items-start gap-3">
                            <MapPin class="w-5 h-5 mt-0.5 flex-shrink-0 text-theme-1" />
                            <div class="text-sm text-gray-600">
                                <div class="font-semibold text-title">{{ addr.first_name }} {{ addr.last_name }}</div>
                                <div>{{ addr.address_line1 }}<span v-if="addr.address_line2">, {{ addr.address_line2 }}</span></div>
                                <div>{{ addr.city }}, {{ addr.state }} {{ addr.postal_code }}</div>
                                <div>{{ addr.country }}</div>
                                <div class="text-gray-400 capitalize">{{ addr.type }} address</div>
                                <div v-if="addr.phone" class="text-gray-400">{{ addr.phone }}</div>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <button @click="openEdit(addr)" class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 border rounded-lg hover:bg-gray-50">
                                <Pencil class="w-3 h-3" /> Edit
                            </button>
                            <button @click="deleteAddress(addr.id)" class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 border rounded-lg text-red-500 hover:bg-red-50">
                                <Trash2 class="w-3 h-3" /> Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else-if="!showForm" class="text-center py-16 border rounded-xl">
                    <MapPin class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                    <p class="text-gray-500 mb-4">No addresses saved yet.</p>
                    <button @click="openAdd" class="dmart-btn dmart-btn-primary"><Plus class="w-4 h-4" /> Add Your First Address</button>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
