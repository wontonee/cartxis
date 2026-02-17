<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, reactive, nextTick } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../components/Breadcrumb.vue';
import { useCurrency } from '@/composables/useCurrency';
import {
    MapPin, CreditCard, Truck, Lock, ChevronRight, Plus, Check,
    AlertCircle, Loader2,
} from 'lucide-vue-next';

interface CartItem {
    id: number; product_id: number;
    product: { id: number; name: string; slug: string; image: string | null };
    quantity: number; price: number; total: number;
    // Legacy shape compat
    name?: string; slug?: string; image?: string | null; subtotal?: number;
}
interface PaymentMethod { id: string; name: string; description: string; icon?: string; instructions?: string; }
interface ShippingOption { id: number; name: string; description?: string; cost: number; }
interface Address {
    id: number; first_name: string; last_name: string; address_line1: string;
    address_line2?: string; city: string; state: string; postal_code: string;
    country: string; phone?: string; is_default: boolean;
}

const props = defineProps<{
    cartItems: CartItem[];
    cartSummary: {
        subtotal: number;
        taxes: { breakdown: any[]; total: number } | number;
        shipping: { options: ShippingOption[]; selected: ShippingOption | null; cost: number } | number;
        total: number;
    };
    checkoutConfig?: {
        allow_guest_checkout?: boolean;
        checkout_require_account?: boolean;
        require_terms_acceptance?: boolean;
        enable_newsletter_signup?: boolean;
        enable_order_notes?: boolean;
    };
    userAddresses?: Address[];
    paymentMethods: PaymentMethod[];
    countries?: { id: number; name: string; code: string; phone_code: string | null }[];
    cartEmpty?: boolean;
    theme?: { name: string; slug: string };
    siteConfig?: { name: string };
}>();

const { formatPrice } = useCurrency();
const page = usePage();

// Normalize cart summary — shipping cost is reactive to the user's selection
const selectedShippingCost = computed(() => {
    if (selectedShippingMethodId.value) {
        const opt = shippingOptions.value.find((o: ShippingOption) => o.id === selectedShippingMethodId.value);
        if (opt) return opt.cost;
    }
    const s = props.cartSummary?.shipping;
    return typeof s === 'object' && s !== null ? (s as any).cost || 0 : (s || 0);
});

const summary = computed(() => {
    const s = props.cartSummary;
    const subtotal = s?.subtotal || 0;
    const taxes = typeof s?.taxes === 'object' && s.taxes !== null ? (s.taxes as any).total || 0 : (s?.taxes || 0);
    const shipping = selectedShippingCost.value;
    return {
        subtotal,
        taxes,
        shipping,
        total: subtotal + taxes + shipping,
    };
});

const shippingOptions = computed<ShippingOption[]>(() => {
    const s = props.cartSummary?.shipping;
    if (typeof s === 'object' && s !== null && 'options' in s) return (s as any).options || [];
    return [];
});

// Wizard steps
const step = ref(1);
const steps = [
    { num: 1, label: 'Shipping', icon: MapPin },
    { num: 2, label: 'Payment', icon: CreditCard },
    { num: 3, label: 'Review', icon: Check },
];

// Validation errors per field
const errors = reactive<Record<string, string>>({});
const clearError = (field: string) => { delete errors[field]; };

// Whether using a saved address or entering new one
const addresses = computed(() => props.userAddresses || []);
const selectedAddressId = ref(addresses.value.find(a => a.is_default)?.id || addresses.value[0]?.id || null);
const showNewAddress = ref(!addresses.value.length);

// Shipping form fields
const shippingForm = reactive({
    first_name: '', last_name: '', address_line1: '', address_line2: '',
    city: '', state: '', postal_code: '', country: '', phone: '',
});

// Guest fields
const email = ref('');
const phone = ref('');
const isAuthenticated = computed(() => !!(page.props.auth as any)?.user);

// Shipping method
const selectedShippingMethodId = ref(shippingOptions.value?.[0]?.id || null);

// Payment - ensure value is always a string for backend validation
const selectedPayment = ref(String(props.paymentMethods?.[0]?.id || props.paymentMethods?.[0]?.code || ''));

// Terms & extras
const termsAccepted = ref(false);
const orderNotes = ref('');

// The selected address object (for review step display)
const selectedAddress = computed(() => {
    if (!showNewAddress.value && selectedAddressId.value) {
        return addresses.value.find(a => a.id === selectedAddressId.value);
    }
    if (showNewAddress.value && shippingForm.first_name) {
        return shippingForm;
    }
    return null;
});

// ---------- Step validation ----------

const validateStep1 = (): boolean => {
    ['email', 'first_name', 'last_name', 'address_line1', 'city', 'state', 'postal_code', 'country', 'phone'].forEach(f => delete errors[f]);
    let valid = true;

    if (!isAuthenticated.value) {
        if (!email.value.trim()) {
            errors.email = 'Email address is required';
            valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            errors.email = 'Please enter a valid email address';
            valid = false;
        }
    }

    if (showNewAddress.value) {
        if (!shippingForm.first_name.trim()) { errors.first_name = 'First name is required'; valid = false; }
        if (!shippingForm.last_name.trim()) { errors.last_name = 'Last name is required'; valid = false; }
        if (!shippingForm.address_line1.trim()) { errors.address_line1 = 'Address is required'; valid = false; }
        if (!shippingForm.city.trim()) { errors.city = 'City is required'; valid = false; }
        if (!shippingForm.state.trim()) { errors.state = 'State is required'; valid = false; }
        if (!shippingForm.postal_code.trim()) { errors.postal_code = 'Postal code is required'; valid = false; }
        if (!shippingForm.country.trim()) { errors.country = 'Country is required'; valid = false; }
        if (!shippingForm.phone.trim()) { errors.phone = 'Phone number is required'; valid = false; }
    } else if (!selectedAddressId.value) {
        errors.address_line1 = 'Please select or add a shipping address';
        valid = false;
    }

    return valid;
};

const validateStep2 = (): boolean => {
    delete errors.payment_method;
    if (!selectedPayment.value) {
        errors.payment_method = 'Please select a payment method';
        return false;
    }
    return true;
};

const validateStep3 = (): boolean => {
    delete errors.terms;
    if (props.checkoutConfig?.require_terms_acceptance !== false && !termsAccepted.value) {
        errors.terms = 'You must accept the terms and conditions';
        return false;
    }
    return true;
};

const goToStep = (s: number) => {
    if (s > step.value) {
        if (step.value === 1 && !validateStep1()) return;
        if (step.value === 2 && s > 2 && !validateStep2()) return;
    }
    step.value = s;
};

// ---------- Place Order ----------

const processing = ref(false);
const serverError = ref('');

const placeOrder = () => {
    if (!validateStep1() || !validateStep2() || !validateStep3()) {
        if (errors.email || errors.first_name || errors.last_name || errors.address_line1 || errors.city || errors.state || errors.postal_code || errors.country || errors.phone) {
            step.value = 1;
        } else if (errors.payment_method) {
            step.value = 2;
        }
        return;
    }

    // Build shipping address from selection or form
    const shippingAddress = showNewAddress.value
        ? { ...shippingForm }
        : (() => {
            const a = addresses.value.find(a => a.id === selectedAddressId.value);
            return a ? {
                first_name: a.first_name, last_name: a.last_name,
                address_line1: a.address_line1, address_line2: a.address_line2,
                city: a.city, state: a.state, postal_code: a.postal_code,
                country: a.country, phone: a.phone,
            } : {};
        })();

    const form = useForm({
        email: isAuthenticated.value ? (page.props.auth as any).user.email : email.value,
        phone: phone.value || (shippingAddress as any).phone || '',
        shipping_address: shippingAddress,
        shipping_method_id: selectedShippingMethodId.value || shippingOptions.value?.[0]?.id || 1,
        payment_method: String(selectedPayment.value),
        billing_same_as_shipping: true,
        terms_accepted: termsAccepted.value,
        order_notes: orderNotes.value || '',
        newsletter_signup: false,
    });

    processing.value = true;
    serverError.value = '';

    form.post('/checkout', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            nextTick(() => {
                const flash = (page.props as any).flash;

                // External redirect (PhonePe, Stripe Checkout, etc.)
                const redirectUrl = flash?.redirect_url;
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                    return;
                }

                // Frontend-integrated gateway (Razorpay modal, etc.)
                const paymentResponse = flash?.payment_response;

                // Handle payment failure (e.g., invalid API keys)
                if (paymentResponse && !paymentResponse.success) {
                    serverError.value = paymentResponse.message || 'Payment processing failed. Please try again.';
                    processing.value = false;
                    return;
                }

                if (paymentResponse?.success && paymentResponse.payment_data) {
                    if (paymentResponse.gateway_type === 'redirect') {
                        const url = paymentResponse.redirect_url
                            || paymentResponse.payment_data.approve_url;
                        if (url) { window.location.href = url; return; }
                    }
                    if (paymentResponse.gateway_type === 'form_post' && paymentResponse.payment_data) {
                        const f = document.createElement('form');
                        f.method = 'POST';
                        f.action = paymentResponse.payment_data.action || '';
                        Object.entries(paymentResponse.payment_data.fields || {}).forEach(([k, v]) => {
                            const inp = document.createElement('input');
                            inp.type = 'hidden'; inp.name = k; inp.value = v as string;
                            f.appendChild(inp);
                        });
                        document.body.appendChild(f);
                        f.submit();
                        return;
                    }
                    // Frontend-integrated gateway (Razorpay modal, Stripe, etc.)
                    if (paymentResponse.gateway_type === 'frontend_integration' && paymentResponse.payment_data) {
                        const pd = paymentResponse.payment_data;
                        if (pd.script_url) {
                            const sid = `payment-script-${pd.gateway_code || 'external'}`;
                            if (!document.getElementById(sid)) {
                                const scr = document.createElement('script');
                                scr.id = sid;
                                scr.src = pd.script_url;
                                scr.onload = () => openPaymentModal(pd);
                                scr.onerror = () => { processing.value = false; serverError.value = 'Failed to load payment gateway'; };
                                document.body.appendChild(scr);
                            } else {
                                openPaymentModal(pd);
                            }
                        } else {
                            openPaymentModal(pd);
                        }
                        return;
                    }
                }

                // No gateway redirect — probably COD, success page handled by Inertia
                processing.value = false;
            });
        },
        onError: (errs) => {
            processing.value = false;
            Object.entries(errs).forEach(([key, msg]) => {
                const flatKey = key.replace('shipping_address.', '');
                errors[flatKey] = msg as string;
            });
            if (errs.email || Object.keys(errs).some(k => k.startsWith('shipping_address'))) {
                step.value = 1;
            } else if (errs.payment_method || errs.shipping_method_id) {
                step.value = 2;
            }
            serverError.value = Object.values(errs)[0] as string || 'Please fix the errors and try again';
        },
        onFinish: () => { processing.value = false; },
    });
};

const breadcrumbs = [
    { label: 'Cart', url: '/cart' },
    { label: 'Checkout' },
];

// --- Payment modal handler (Razorpay, Stripe, etc.) ---
const openPaymentModal = (paymentData: any) => {
    // Razorpay
    if (paymentData.razorpay_order_id && (window as any).Razorpay) {
        const options = {
            key: paymentData.razorpay_key_id,
            amount: paymentData.amount,
            currency: paymentData.currency,
            name: paymentData.name,
            description: paymentData.description,
            order_id: paymentData.razorpay_order_id,
            prefill: paymentData.prefill,
            theme: paymentData.theme,
            handler: (response: any) => {
                const params = new URLSearchParams(response).toString();
                window.location.href = paymentData.callback_url + '?' + params;
            },
            modal: {
                ondismiss: () => {
                    processing.value = false;
                    if (paymentData.cancel_url) window.location.href = paymentData.cancel_url;
                },
            },
        };
        const rzp = new (window as any).Razorpay(options);
        rzp.open();
        return;
    }
    // Stripe Checkout
    if (paymentData.stripe_session_id && (window as any).Stripe) {
        const stripe = (window as any).Stripe(paymentData.stripe_key);
        stripe.redirectToCheckout({ sessionId: paymentData.stripe_session_id });
        return;
    }
    // Fallback — redirect if callback_url exists
    if (paymentData.callback_url) {
        window.location.href = paymentData.callback_url;
        return;
    }
    processing.value = false;
};

const flashError = computed(() => (page.props.flash as any)?.error);
const flashSuccess = computed(() => (page.props.flash as any)?.success);
</script>

<template>
    <ThemeLayout>
        <Head title="Checkout" />

        <Breadcrumb :items="breadcrumbs" />

        <section class="py-8 lg:py-12">
            <div class="max-w-[960px] mx-auto px-4">

                <!-- Flash / Server errors -->
                <div v-if="flashError || serverError" class="mb-6 flex items-start gap-3 p-4 rounded-xl bg-red-50 border border-red-200">
                    <AlertCircle class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
                    <p class="text-sm text-red-700">{{ flashError || serverError }}</p>
                </div>
                <div v-if="flashSuccess" class="mb-6 flex items-start gap-3 p-4 rounded-xl bg-green-50 border border-green-200">
                    <Check class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <p class="text-sm text-green-700">{{ flashSuccess }}</p>
                </div>

                <!-- Steps Indicator -->
                <div class="flex items-center justify-center gap-4 mb-10">
                    <template v-for="(s, i) in steps" :key="s.num">
                        <button
                            @click="goToStep(s.num)"
                            class="flex items-center gap-2 text-sm font-semibold transition-colors"
                            :class="step >= s.num ? 'text-theme-1' : 'text-gray-400'"
                        >
                            <span
                                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold"
                                :class="step >= s.num ? 'bg-theme-1 text-white' : 'bg-gray-200 text-gray-500'"
                            >{{ s.num }}</span>
                            <span class="hidden sm:inline">{{ s.label }}</span>
                        </button>
                        <ChevronRight v-if="i < steps.length - 1" class="w-4 h-4 text-gray-300" />
                    </template>
                </div>

                <div class="lg:grid lg:grid-cols-[1fr_380px] gap-8">
                    <!-- Main Content -->
                    <div>
                        <!-- Step 1: Shipping -->
                        <div v-show="step === 1">
                            <h2 class="text-xl font-bold mb-6 text-title font-title">
                                <MapPin class="w-5 h-5 inline mr-2" /> Shipping Information
                            </h2>

                            <!-- Email for guests -->
                            <div v-if="!isAuthenticated" class="mb-6">
                                <label class="block text-sm font-semibold mb-1.5">Email Address *</label>
                                <input
                                    v-model="email"
                                    type="email"
                                    placeholder="your@email.com"
                                    class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-0 transition-colors"
                                    :class="errors.email ? 'border-red-400 bg-red-50/50' : 'border-gray-200 focus:border-theme-1'"
                                    @input="clearError('email')"
                                />
                                <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email }}</p>
                            </div>

                            <!-- Saved Addresses -->
                            <div v-if="addresses.length && !showNewAddress" class="space-y-3 mb-4">
                                <label
                                    v-for="addr in addresses"
                                    :key="addr.id"
                                    class="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-colors"
                                    :class="selectedAddressId === addr.id ? 'border-theme-1 bg-red-50/30' : 'border-gray-200'"
                                >
                                    <input type="radio" :value="addr.id" v-model="selectedAddressId" class="mt-1 accent-theme-1" />
                                    <div class="text-sm">
                                        <div class="font-semibold">{{ addr.first_name }} {{ addr.last_name }}</div>
                                        <div class="text-gray-500">{{ addr.address_line1 }}<span v-if="addr.address_line2">, {{ addr.address_line2 }}</span></div>
                                        <div class="text-gray-500">{{ addr.city }}, {{ addr.state }} {{ addr.postal_code }}, {{ addr.country }}</div>
                                        <div v-if="addr.phone" class="text-gray-400">{{ addr.phone }}</div>
                                    </div>
                                </label>
                                <button @click="showNewAddress = true" class="flex items-center gap-2 text-sm font-semibold py-2 text-theme-1">
                                    <Plus class="w-4 h-4" /> Add New Address
                                </button>
                            </div>

                            <!-- New Address Form -->
                            <div v-if="showNewAddress" class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold mb-1.5">First Name *</label>
                                        <input v-model="shippingForm.first_name" type="text"
                                            class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-0 transition-colors"
                                            :class="errors.first_name ? 'border-red-400 bg-red-50/50' : 'border-gray-200 focus:border-theme-1'"
                                            @input="clearError('first_name')"
                                        />
                                        <p v-if="errors.first_name" class="mt-1 text-xs text-red-500">{{ errors.first_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold mb-1.5">Last Name *</label>
                                        <input v-model="shippingForm.last_name" type="text"
                                            class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-0 transition-colors"
                                            :class="errors.last_name ? 'border-red-400 bg-red-50/50' : 'border-gray-200 focus:border-theme-1'"
                                            @input="clearError('last_name')"
                                        />
                                        <p v-if="errors.last_name" class="mt-1 text-xs text-red-500">{{ errors.last_name }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5">Address *</label>
                                    <input v-model="shippingForm.address_line1" type="text"
                                        class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-0 transition-colors"
                                        :class="errors.address_line1 ? 'border-red-400 bg-red-50/50' : 'border-gray-200 focus:border-theme-1'"
                                        @input="clearError('address_line1')"
                                    />
                                    <p v-if="errors.address_line1" class="mt-1 text-xs text-red-500">{{ errors.address_line1 }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5">Address Line 2</label>
                                    <input v-model="shippingForm.address_line2" type="text"
                                        class="w-full border rounded-xl px-4 py-3 text-sm border-gray-200 focus:border-theme-1 focus:ring-0"
                                    />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold mb-1.5">City *</label>
                                        <input v-model="shippingForm.city" type="text"
                                            class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-0 transition-colors"
                                            :class="errors.city ? 'border-red-400 bg-red-50/50' : 'border-gray-200 focus:border-theme-1'"
                                            @input="clearError('city')"
                                        />
                                        <p v-if="errors.city" class="mt-1 text-xs text-red-500">{{ errors.city }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold mb-1.5">State *</label>
                                        <input v-model="shippingForm.state" type="text"
                                            class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-0 transition-colors"
                                            :class="errors.state ? 'border-red-400 bg-red-50/50' : 'border-gray-200 focus:border-theme-1'"
                                            @input="clearError('state')"
                                        />
                                        <p v-if="errors.state" class="mt-1 text-xs text-red-500">{{ errors.state }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold mb-1.5">Postal Code *</label>
                                        <input v-model="shippingForm.postal_code" type="text"
                                            class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-0 transition-colors"
                                            :class="errors.postal_code ? 'border-red-400 bg-red-50/50' : 'border-gray-200 focus:border-theme-1'"
                                            @input="clearError('postal_code')"
                                        />
                                        <p v-if="errors.postal_code" class="mt-1 text-xs text-red-500">{{ errors.postal_code }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold mb-1.5">Country *</label>
                                        <select v-model="shippingForm.country"
                                            class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-0 transition-colors bg-white"
                                            :class="errors.country ? 'border-red-400 bg-red-50/50' : 'border-gray-200 focus:border-theme-1'"
                                            @change="clearError('country')"
                                        >
                                            <option value="">Select country...</option>
                                            <option v-for="c in (props.countries || [])" :key="c.code" :value="c.name">{{ c.name }}</option>
                                        </select>
                                        <p v-if="errors.country" class="mt-1 text-xs text-red-500">{{ errors.country }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5">Phone *</label>
                                    <input v-model="shippingForm.phone" type="tel"
                                        class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-0 transition-colors"
                                        :class="errors.phone ? 'border-red-400 bg-red-50/50' : 'border-gray-200 focus:border-theme-1'"
                                        @input="clearError('phone')"
                                    />
                                    <p v-if="errors.phone" class="mt-1 text-xs text-red-500">{{ errors.phone }}</p>
                                </div>
                                <button v-if="addresses.length" @click="showNewAddress = false" class="text-sm text-theme-1">
                                    ← Use saved address
                                </button>
                            </div>

                            <!-- Shipping Method -->
                            <div v-if="shippingOptions.length > 1" class="mt-6">
                                <h3 class="text-sm font-bold mb-3"><Truck class="w-4 h-4 inline mr-1" /> Shipping Method</h3>
                                <div class="space-y-2">
                                    <label v-for="opt in shippingOptions" :key="opt.id"
                                        class="flex items-center justify-between gap-3 p-3 border-2 rounded-xl cursor-pointer transition-colors"
                                        :class="selectedShippingMethodId === opt.id ? 'border-theme-1 bg-red-50/30' : 'border-gray-200'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <input type="radio" :value="opt.id" v-model="selectedShippingMethodId" class="accent-theme-1" />
                                            <div>
                                                <div class="text-sm font-medium">{{ opt.name }}</div>
                                                <div v-if="opt.description" class="text-xs text-gray-400">{{ opt.description }}</div>
                                            </div>
                                        </div>
                                        <span class="text-sm font-semibold">{{ opt.cost ? formatPrice(opt.cost) : 'Free' }}</span>
                                    </label>
                                </div>
                            </div>

                            <button @click="goToStep(2)" class="dmart-btn dmart-btn-primary py-3 mt-6">
                                Continue to Payment <ChevronRight class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Step 2: Payment -->
                        <div v-show="step === 2">
                            <h2 class="text-xl font-bold mb-6 text-title font-title">
                                <CreditCard class="w-5 h-5 inline mr-2" /> Payment Method
                            </h2>
                            <div class="space-y-3">
                                <label
                                    v-for="pm in paymentMethods"
                                    :key="pm.id"
                                    class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-colors"
                                    :class="selectedPayment === pm.id ? 'border-theme-1 bg-red-50/30' : 'border-gray-200'"
                                >
                                    <input type="radio" :value="pm.id" v-model="selectedPayment" class="accent-theme-1" @change="clearError('payment_method')" />
                                    <div>
                                        <div class="font-semibold text-sm">{{ pm.name }}</div>
                                        <div class="text-xs text-gray-500">{{ pm.description }}</div>
                                    </div>
                                </label>
                                <p v-if="errors.payment_method" class="text-xs text-red-500">{{ errors.payment_method }}</p>
                            </div>
                            <div class="flex gap-3 mt-6">
                                <button @click="step = 1" class="dmart-btn dmart-btn-outline py-3">Back</button>
                                <button @click="goToStep(3)" class="dmart-btn dmart-btn-primary py-3 flex-1">
                                    Review Order <ChevronRight class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Review -->
                        <div v-show="step === 3">
                            <h2 class="text-xl font-bold mb-6 text-title font-title">
                                <Check class="w-5 h-5 inline mr-2" /> Review Order
                            </h2>

                            <!-- Shipping Summary -->
                            <div v-if="selectedAddress" class="border rounded-xl p-4 mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-sm">Shipping Address</span>
                                    <button @click="step = 1" class="text-xs text-theme-1">Change</button>
                                </div>
                                <p class="text-sm text-gray-600">
                                    {{ selectedAddress.first_name }} {{ selectedAddress.last_name }}<br />
                                    {{ (selectedAddress as any).address_line1 || (selectedAddress as any).address_line_1 }},
                                    {{ selectedAddress.city }}, {{ selectedAddress.state }} {{ selectedAddress.postal_code }}
                                </p>
                            </div>

                            <!-- Payment Summary -->
                            <div class="border rounded-xl p-4 mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-sm">Payment Method</span>
                                    <button @click="step = 2" class="text-xs text-theme-1">Change</button>
                                </div>
                                <p class="text-sm text-gray-600">{{ paymentMethods?.find(p => p.id === selectedPayment)?.name }}</p>
                            </div>

                            <!-- Cart Items -->
                            <div class="border rounded-xl p-4">
                                <div class="font-semibold text-sm mb-3">Items ({{ cartItems?.length }})</div>
                                <div v-for="item in cartItems" :key="item.id" class="flex items-center gap-3 py-2 border-b last:border-0">
                                    <img :src="item.product?.image || item.image || '/images/placeholder.png'" :alt="item.product?.name || item.name" class="w-14 h-14 rounded-lg object-cover" />
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium truncate">{{ item.product?.name || item.name }}</div>
                                        <div class="text-xs text-gray-400">Qty: {{ item.quantity }}</div>
                                    </div>
                                    <div class="text-sm font-bold text-theme-1">{{ formatPrice(item.total || item.subtotal || 0) }}</div>
                                </div>
                            </div>

                            <!-- Order Notes -->
                            <div v-if="checkoutConfig?.enable_order_notes !== false" class="mt-4">
                                <label class="block text-sm font-semibold mb-1.5">Order Notes (optional)</label>
                                <textarea v-model="orderNotes" rows="2"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-theme-1 focus:ring-0"
                                    placeholder="Special instructions for your order..."
                                ></textarea>
                            </div>

                            <!-- Terms Checkbox -->
                            <div class="mt-4">
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="termsAccepted"
                                        class="mt-0.5 accent-theme-1"
                                        @change="clearError('terms')"
                                    />
                                    <span class="text-sm text-gray-600">
                                        I agree to the
                                        <Link href="/terms-conditions" class="underline text-theme-1">Terms &amp; Conditions</Link>
                                        and
                                        <Link href="/privacy-policy" class="underline text-theme-1">Privacy Policy</Link>
                                    </span>
                                </label>
                                <p v-if="errors.terms" class="mt-1 text-xs text-red-500">{{ errors.terms }}</p>
                            </div>

                            <div class="flex gap-3 mt-6">
                                <button @click="step = 2" class="dmart-btn dmart-btn-outline py-3">Back</button>
                                <button
                                    @click="placeOrder"
                                    :disabled="processing"
                                    class="dmart-btn dmart-btn-primary py-3.5 flex-1 text-base disabled:opacity-50"
                                >
                                    <Loader2 v-if="processing" class="w-4 h-4 animate-spin" />
                                    <Lock v-else class="w-4 h-4" />
                                    {{ processing ? 'Processing...' : `Place Order — ${formatPrice(summary.total)}` }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div class="mt-8 lg:mt-0">
                        <div class="rounded-2xl border p-6 sticky top-24 space-y-4">
                            <h3 class="text-lg font-bold text-title font-title">
                                Order Summary
                            </h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Subtotal</span>
                                    <span class="font-semibold">{{ formatPrice(summary.subtotal) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Shipping</span>
                                    <span class="font-semibold" :class="{ 'text-green-600': !summary.shipping }">
                                        {{ summary.shipping ? formatPrice(summary.shipping) : 'Free' }}
                                    </span>
                                </div>
                                <div v-if="summary.taxes" class="flex justify-between">
                                    <span class="text-gray-500">Tax</span>
                                    <span class="font-semibold">{{ formatPrice(summary.taxes) }}</span>
                                </div>
                            </div>
                            <div class="border-t pt-4 flex justify-between">
                                <span class="font-bold text-title">Total</span>
                                <span class="text-xl font-extrabold text-theme-1">{{ formatPrice(summary.total) }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-400 pt-2">
                                <Lock class="w-3.5 h-3.5" /> Secure 256-bit SSL encryption
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>
