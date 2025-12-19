<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useCurrency } from '@/composables/useCurrency';
import ThemeLayout from '@/../../themes/vortex-default/resources/views/layouts/ThemeLayout.vue';

interface CartItem {
  id: number;
  product_id: number;
  product: {
    id: number;
    name: string;
    slug: string;
    image?: string;
  };
  quantity: number;
  price: number;
  total: number;
}

interface ShippingOption {
  id: number;
  name: string;
  description: string;
  cost: number;
  estimated_days: string;
}

interface CartSummary {
  subtotal: number;
  taxes: {
    breakdown: Array<{ name: string; amount: number }>;
    total: number;
  };
  shipping: {
    options: ShippingOption[];
    selected: ShippingOption | null;
    cost: number;
  };
  total: number;
}

interface Address {
  id?: number;
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
  is_default?: boolean;
}

interface PaymentMethod {
  id: number;
  code: string;
  name: string;
  description: string;
  instructions?: string;
  is_default: boolean;
  fee: number;
  is_available: boolean;
}

interface CheckoutConfig {
  allow_guest_checkout: boolean;
  checkout_require_account?: boolean;
  require_terms_acceptance: boolean;
  enable_newsletter_signup: boolean;
  enable_order_notes: boolean;
}

interface Props {
  cartItems: CartItem[];
  cartSummary: CartSummary;
  checkoutConfig: CheckoutConfig;
  userAddresses: Address[];
  paymentMethods: PaymentMethod[];
  cartEmpty: boolean;
  error?: string;
}

const props = defineProps<Props>();

const page = usePage();
const { formatPrice } = useCurrency();

const processing = ref(false);
const selectedAddressId = ref<number | null>(props.userAddresses.find(a => a.is_default)?.id || null);
const useNewAddress = ref(!selectedAddressId.value);
const selectedShippingId = ref(props.cartSummary.shipping.selected?.id || null);
const selectedPaymentMethod = ref(props.paymentMethods.find(m => m.is_default)?.code || props.paymentMethods[0]?.code || 'cod');
const billingSameAsShipping = ref(true);
const termsAccepted = ref(false);
const newsletterSignup = ref(false);

// Field-specific errors
const fieldErrors = ref<Record<string, string>>({});

// Checkout mode selection (when both guest and account creation are allowed)
const checkoutMode = ref<'guest' | 'create_account'>('guest');

// Form data
const guestEmail = ref('');
const guestPhone = ref('');
const orderNotes = ref('');
const accountPassword = ref('');
const accountPasswordConfirm = ref('');

const shippingAddress = ref<Address>({
  first_name: '',
  last_name: '',
  company: '',
  address_line1: '',
  address_line2: '',
  city: '',
  state: '',
  postal_code: '',
  country: 'US',
  phone: '',
});

const billingAddress = ref<Address>({
  first_name: '',
  last_name: '',
  company: '',
  address_line1: '',
  address_line2: '',
  city: '',
  state: '',
  postal_code: '',
  country: 'US',
  phone: '',
});

const isAuthenticated = computed(() => {
  const auth = (page.props as any).auth;
  return !!(auth && auth.user);
});

const currentUser = computed(() => {
  const auth = (page.props as any).auth;
  return auth?.user || null;
});

const selectedAddress = computed(() => {
  if (!selectedAddressId.value) return null;
  return props.userAddresses.find(a => a.id === selectedAddressId.value);
});

const selectAddress = (addressId: number) => {
  selectedAddressId.value = addressId;
  useNewAddress.value = false;
};

const selectShipping = (shippingId: number) => {
  selectedShippingId.value = shippingId;
  fieldErrors.value = {}; // Clear any previous errors
  // Update shipping in backend session
  router.post('/checkout/shipping', {
    shipping_method_id: shippingId,
  }, {
    preserveScroll: true,
    preserveState: true,
  });
};

// Generic payment gateway handler - works with any gateway extension
const initiatePaymentGateway = (paymentResponse: any) => {
  console.log('Initiating payment gateway with response:', paymentResponse);
  
  const paymentData = paymentResponse.payment_data;
  const gatewayType = paymentResponse.gateway_type || 'frontend_integration';
  
  // Gateway returned frontend integration data (modal, script, etc.)
  if (gatewayType === 'frontend_integration' && paymentData) {
    // Load external script if provided (e.g., Razorpay, Stripe)
    if (paymentData.script_url) {
      const scriptId = `payment-script-${paymentData.gateway_code || 'external'}`;
      
      // Check if script already loaded
      if (!document.getElementById(scriptId)) {
        const script = document.createElement('script');
        script.id = scriptId;
        script.src = paymentData.script_url;
        script.onload = () => {
          console.log('Payment gateway script loaded');
          openPaymentModal(paymentData);
        };
        script.onerror = () => {
          console.error('Failed to load payment gateway script');
          processing.value = false;
        };
        document.body.appendChild(script);
      } else {
        openPaymentModal(paymentData);
      }
    } else {
      // No external script needed, open modal directly
      openPaymentModal(paymentData);
    }
  }
};

const openPaymentModal = (paymentData: any) => {
  console.log('Opening payment modal for:', paymentData.gateway_code || 'unknown gateway');
  
  // Razorpay integration
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
      handler: function (response: any) {
        console.log('Payment successful:', response);
        // Redirect to callback URL with payment details
        const params = new URLSearchParams(response).toString();
        window.location.href = paymentData.callback_url + '?' + params;
      },
      modal: {
        ondismiss: function() {
          console.log('Payment modal dismissed');
          processing.value = false;
          if (paymentData.cancel_url) {
            window.location.href = paymentData.cancel_url;
          }
        }
      }
    };
    const rzp = new (window as any).Razorpay(options);
    rzp.open();
  }
  // Stripe integration
  else if (paymentData.stripe_session_id && (window as any).Stripe) {
    const stripe = (window as any).Stripe(paymentData.stripe_key);
    stripe.redirectToCheckout({ sessionId: paymentData.stripe_session_id })
      .then((result: any) => {
        if (result.error) {
          console.error('Stripe error:', result.error.message);
          processing.value = false;
        }
      });
  }
  // Add more gateway integrations here as plugins are added
  // PayPal, Square, etc.
  else {
    console.error('Unsupported payment gateway or missing configuration');
    processing.value = false;
  }
};

// Watch for payment response in flash data
watch(() => page.props.flash, (flash: any) => {
  console.log('Flash watcher triggered:', flash);
  if (flash?.payment_response) {
    console.log('Payment response detected in flash:', flash.payment_response);
    if (flash.payment_response.success && flash.payment_response.payment_data) {
      console.log('Initiating payment gateway integration');
      initiatePaymentGateway(flash.payment_response);
    }
  }
}, { deep: true, immediate: true });

const submitOrder = () => {
  // Clear previous errors
  fieldErrors.value = {};

  // Validate email for guest checkout
  if (!isAuthenticated.value && !guestEmail.value) {
    fieldErrors.value.email = 'Email address is required';
  }

  // Validate shipping address - name
  if (!shippingAddress.value.first_name) {
    fieldErrors.value.first_name = 'First name is required';
  }
  if (!shippingAddress.value.last_name) {
    fieldErrors.value.last_name = 'Last name is required';
  }

  // Validate street address
  if (!shippingAddress.value.address_line1) {
    fieldErrors.value.address = 'Street address is required';
  }

  // Validate city, state, postal code
  if (!shippingAddress.value.city) {
    fieldErrors.value.city = 'City is required';
  }
  if (!shippingAddress.value.state) {
    fieldErrors.value.state = 'State is required';
  }
  if (!shippingAddress.value.postal_code) {
    fieldErrors.value.postal_code = 'Postal code is required';
  }

  // Validate phone number
  if (!shippingAddress.value.phone) {
    fieldErrors.value.phone = 'Phone number is required';
  }

  // Validate shipping method selection
  if (!selectedShippingId.value) {
    fieldErrors.value.shipping_method = 'Please select a shipping method';
  }

  // Validate terms acceptance
  if (!termsAccepted.value && props.checkoutConfig.require_terms_acceptance) {
    fieldErrors.value.terms = 'You must accept the terms and conditions';
  }

  // Validate account creation if user chose to create account OR if account is required without guest option
  const shouldCreateAccount = !isAuthenticated.value && (
    (props.checkoutConfig.checkout_require_account && !props.checkoutConfig.allow_guest_checkout) ||
    (props.checkoutConfig.checkout_require_account && props.checkoutConfig.allow_guest_checkout && checkoutMode.value === 'create_account')
  );

  if (shouldCreateAccount) {
    if (!accountPassword.value || accountPassword.value.length < 8) {
      fieldErrors.value.password = 'Password must be at least 8 characters';
    } else if (accountPassword.value !== accountPasswordConfirm.value) {
      fieldErrors.value.password_confirmation = 'Passwords do not match';
    }
  }

  // If there are any errors, scroll to top and stop
  if (Object.keys(fieldErrors.value).length > 0) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  processing.value = true;

  const formData: any = {
    email: isAuthenticated.value ? currentUser.value?.email : guestEmail.value,
    phone: guestPhone.value,
    shipping_method_id: selectedShippingId.value,
    payment_method: selectedPaymentMethod.value,
    billing_same_as_shipping: billingSameAsShipping.value,
    terms_accepted: termsAccepted.value,
    newsletter_signup: newsletterSignup.value,
    order_notes: orderNotes.value,
  };

  // Add account password if creating account
  if (shouldCreateAccount) {
    formData.password = accountPassword.value;
    formData.password_confirmation = accountPasswordConfirm.value;
    formData.create_account = true;
  }

  // Add shipping address
  if (isAuthenticated.value && !useNewAddress.value && selectedAddress.value) {
    formData.shipping_address = selectedAddress.value;
  } else {
    formData.shipping_address = shippingAddress.value;
  }

  // Add billing address if different
  if (!billingSameAsShipping.value) {
    formData.billing_address = billingAddress.value;
  }

  // Submit checkout - let the backend determine how to handle the payment method
  router.post('/checkout', formData, {
    preserveState: false,
    preserveScroll: false,
    onSuccess: () => {
      console.log('Checkout success, checking for gateway response');
      
      // Use nextTick to ensure page props are updated
      nextTick(() => {
        // Check if there's a redirect URL in flash data (for hosted payment pages)
        const redirectUrl = page.props.flash?.redirect_url;
        console.log('Redirect URL from flash:', redirectUrl);
        
        if (redirectUrl) {
          console.log('Redirecting to payment gateway hosted page');
          window.location.href = redirectUrl;
          return;
        }
        
        // Check if there's payment data for frontend integration (modal-based)
        const paymentResponse = page.props.flash?.payment_response;
        console.log('Payment response from flash:', paymentResponse);
        
        if (paymentResponse && paymentResponse.success && paymentResponse.payment_data) {
          console.log('Initiating frontend payment integration');
          initiatePaymentGateway(paymentResponse);
        }
      });
    },
    onError: (errors) => {
      console.log('Checkout validation errors:', errors);
      processing.value = false;
    },
    onFinish: () => {
      // processing.value will be false if there were errors
      // For successful redirects/integrations, page will navigate away or modal opens
    },
  });
};
</script>

<template>
  <ThemeLayout>
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-8">Checkout</h1>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">{{ $page.props.flash.error }}</p>
          </div>
        </div>
      </div>

      <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-green-700">{{ $page.props.flash.success }}</p>
          </div>
        </div>
      </div>

      <div v-if="cartEmpty" class="text-center py-12">
        <p class="text-xl text-gray-600 mb-4">{{ error || 'Your cart is empty' }}</p>
        <a href="/products" class="text-blue-600 hover:underline">Continue Shopping</a>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Checkout Form -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Contact Information -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-6">Contact Information</h2>
            
            <div v-if="!isAuthenticated">
              <!-- Checkout Mode Toggle (when both guest and account creation are enabled) -->
              <div v-if="checkoutConfig.allow_guest_checkout && checkoutConfig.checkout_require_account" class="mb-6">
                <div class="grid grid-cols-2 gap-4 p-1 bg-gray-100 rounded-lg">
                  <button
                    type="button"
                    @click="checkoutMode = 'guest'"
                    :class="[
                      'py-3 px-4 rounded-md font-medium transition-all duration-200',
                      checkoutMode === 'guest' 
                        ? 'bg-white text-blue-600 shadow-sm' 
                        : 'text-gray-600 hover:text-gray-900'
                    ]"
                  >
                    <svg class="w-5 h-5 inline-block mr-2 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Checkout as Guest
                  </button>
                  <button
                    type="button"
                    @click="checkoutMode = 'create_account'"
                    :class="[
                      'py-3 px-4 rounded-md font-medium transition-all duration-200',
                      checkoutMode === 'create_account' 
                        ? 'bg-white text-blue-600 shadow-sm' 
                        : 'text-gray-600 hover:text-gray-900'
                    ]"
                  >
                    <svg class="w-5 h-5 inline-block mr-2 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Create Account
                  </button>
                </div>
                <p v-if="checkoutMode === 'guest'" class="text-sm text-gray-600 mt-3">
                  Continue as a guest. You can create an account later.
                </p>
                <p v-else class="text-sm text-gray-600 mt-3">
                  Create an account to track your orders and save your information for faster checkout.
                </p>
              </div>

              <!-- Email and Phone Fields -->
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium mb-2">Email Address *</label>
                  <input
                    v-model="guestEmail"
                    type="email"
                    required
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2',
                      fieldErrors.email ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                    ]"
                    placeholder="you@example.com"
                  />
                  <p v-if="fieldErrors.email" class="mt-1 text-sm text-red-600">{{ fieldErrors.email }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-2">Phone Number</label>
                  <input
                    v-model="guestPhone"
                    type="tel"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="+1 (555) 000-0000"
                  />
                </div>
              </div>
              
              <!-- Account Creation Password Fields (when creating account OR when account is required with no toggle) -->
              <div v-if="(checkoutConfig.allow_guest_checkout && checkoutConfig.checkout_require_account && checkoutMode === 'create_account') || (checkoutConfig.checkout_require_account && !checkoutConfig.allow_guest_checkout)" class="pt-4 mt-4 border-t space-y-4">
                <h3 class="text-lg font-semibold text-blue-600">Account Password</h3>
                
                <div>
                  <label class="block text-sm font-medium mb-2">Password *</label>
                  <input
                    v-model="accountPassword"
                    type="password"
                    required
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2',
                      fieldErrors.password ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                    ]"
                    placeholder="Enter a secure password"
                    minlength="8"
                  />
                  <p v-if="!fieldErrors.password" class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                  <p v-if="fieldErrors.password" class="mt-1 text-sm text-red-600">{{ fieldErrors.password }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-2">Confirm Password *</label>
                  <input
                    v-model="accountPasswordConfirm"
                    type="password"
                    required
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2',
                      fieldErrors.password_confirmation ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                    ]"
                    placeholder="Re-enter your password"
                  />
                  <p v-if="fieldErrors.password_confirmation" class="mt-1 text-sm text-red-600">{{ fieldErrors.password_confirmation }}</p>
                </div>
              </div>
            </div>
            
            <div v-else class="text-sm text-gray-600">
              <p>Logged in as: <strong>{{ currentUser?.email }}</strong></p>
            </div>
          </div>

          <!-- Shipping Address -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Shipping Address</h2>
            
            <!-- Saved Addresses (for authenticated users) -->
            <div v-if="isAuthenticated && userAddresses.length > 0" class="mb-4">
              <div class="space-y-2 mb-4">
                <label
                  v-for="address in userAddresses"
                  :key="address.id"
                  class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                  :class="{ 'border-blue-500 bg-blue-50': selectedAddressId === address.id }"
                >
                  <input
                    type="radio"
                    :checked="selectedAddressId === address.id"
                    @change="selectAddress(address.id!)"
                    class="mt-1"
                  />
                  <div class="ml-3">
                    <p class="font-medium">{{ address.first_name }} {{ address.last_name }}</p>
                    <p class="text-sm text-gray-600">{{ address.address_line1 }}</p>
                    <p v-if="address.address_line2" class="text-sm text-gray-600">{{ address.address_line2 }}</p>
                    <p class="text-sm text-gray-600">{{ address.city }}, {{ address.state }} {{ address.postal_code }}</p>
                    <p class="text-sm text-gray-600">{{ address.country }}</p>
                  </div>
                </label>
              </div>
              
              <button
                @click="useNewAddress = true; selectedAddressId = null"
                type="button"
                class="text-blue-600 hover:underline text-sm"
              >
                + Use a different address
              </button>
            </div>

            <!-- New Address Form -->
            <div v-if="!isAuthenticated || useNewAddress" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-2">First Name *</label>
                <input
                  v-model="shippingAddress.first_name"
                  type="text"
                  required
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2',
                    fieldErrors.first_name ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                  ]"
                />
                <p v-if="fieldErrors.first_name" class="mt-1 text-sm text-red-600">{{ fieldErrors.first_name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium mb-2">Last Name *</label>
                <input
                  v-model="shippingAddress.last_name"
                  type="text"
                  required
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2',
                    fieldErrors.last_name ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                  ]"
                />
                <p v-if="fieldErrors.last_name" class="mt-1 text-sm text-red-600">{{ fieldErrors.last_name }}</p>
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-2">Company (optional)</label>
                <input
                  v-model="shippingAddress.company"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-2">Address *</label>
                <input
                  v-model="shippingAddress.address_line1"
                  type="text"
                  required
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2',
                    fieldErrors.address ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                  ]"
                  placeholder="Street address"
                />
                <p v-if="fieldErrors.address" class="mt-1 text-sm text-red-600">{{ fieldErrors.address }}</p>
              </div>
              <div class="md:col-span-2">
                <input
                  v-model="shippingAddress.address_line2"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                  placeholder="Apartment, suite, etc. (optional)"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-2">City *</label>
                <input
                  v-model="shippingAddress.city"
                  type="text"
                  required
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2',
                    fieldErrors.city ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                  ]"
                />
                <p v-if="fieldErrors.city" class="mt-1 text-sm text-red-600">{{ fieldErrors.city }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium mb-2">State *</label>
                <input
                  v-model="shippingAddress.state"
                  type="text"
                  required
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2',
                    fieldErrors.state ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                  ]"
                />
                <p v-if="fieldErrors.state" class="mt-1 text-sm text-red-600">{{ fieldErrors.state }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium mb-2">Postal Code *</label>
                <input
                  v-model="shippingAddress.postal_code"
                  type="text"
                  required
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2',
                    fieldErrors.postal_code ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                  ]"
                />
                <p v-if="fieldErrors.postal_code" class="mt-1 text-sm text-red-600">{{ fieldErrors.postal_code }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium mb-2">Country *</label>
                <select
                  v-model="shippingAddress.country"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                  <option value="US">United States</option>
                  <option value="CA">Canada</option>
                  <option value="IN">India</option>
                  <option value="GB">United Kingdom</option>
                </select>
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-2">Phone Number *</label>
                <input
                  v-model="shippingAddress.phone"
                  type="tel"
                  required
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2',
                    fieldErrors.phone ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                  ]"
                />
                <p v-if="fieldErrors.phone" class="mt-1 text-sm text-red-600">{{ fieldErrors.phone }}</p>
              </div>
            </div>
          </div>

          <!-- Shipping Method -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Shipping Method</h2>
            
            <div class="space-y-2">
              <label
                v-for="option in cartSummary.shipping.options"
                :key="option.id"
                class="flex items-center justify-between p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                :class="{ 'border-blue-500 bg-blue-50': selectedShippingId === option.id }"
              >
                <div class="flex items-center">
                  <input
                    type="radio"
                    :checked="selectedShippingId === option.id"
                    @change="selectShipping(option.id)"
                    class="mr-3"
                  />
                  <div>
                    <p class="font-medium">{{ option.name }}</p>
                    <p class="text-sm text-gray-600">{{ option.description }}</p>
                    <p v-if="option.estimated_days" class="text-xs text-gray-500 mt-1">
                      Estimated delivery: {{ option.estimated_days }}
                    </p>
                  </div>
                </div>
                <div class="font-bold">
                  {{ option.cost === 0 ? 'FREE' : formatPrice(option.cost) }}
                </div>
              </label>
            </div>
            <p v-if="fieldErrors.shipping_method" class="mt-2 text-sm text-red-600">{{ fieldErrors.shipping_method }}</p>
          </div>

          <!-- Payment Method -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Payment Method</h2>
            
            <div class="space-y-2">
              <label
                v-for="method in paymentMethods"
                :key="method.id"
                class="flex items-center justify-between p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                :class="{ 'border-blue-500 bg-blue-50': selectedPaymentMethod === method.code }"
              >
                <div class="flex items-center flex-1">
                  <input
                    type="radio"
                    :value="method.code"
                    v-model="selectedPaymentMethod"
                    class="mr-3"
                  />
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <p class="font-medium">{{ method.name }}</p>
                      <span v-if="method.fee > 0" class="text-xs text-gray-500">
                        (+ {{ formatPrice(method.fee) }} fee)
                      </span>
                    </div>
                    <p class="text-sm text-gray-600">{{ method.description }}</p>
                    <p v-if="method.instructions && selectedPaymentMethod === method.code" class="text-xs text-blue-600 mt-1">
                      {{ method.instructions }}
                    </p>
                  </div>
                </div>
                <div v-if="method.is_default" class="text-xs text-blue-600 font-medium">
                  Default
                </div>
              </label>
            </div>
          </div>

          <!-- Order Notes (optional) -->
          <div v-if="checkoutConfig.enable_order_notes" class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Order Notes (Optional)</h2>
            <textarea
              v-model="orderNotes"
              rows="3"
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="Add any special instructions for your order..."
            ></textarea>
          </div>

          <!-- Terms & Newsletter -->
          <div class="bg-white p-6 rounded-lg shadow space-y-4">
            <div v-if="checkoutConfig.require_terms_acceptance">
              <label class="flex items-start">
                <input
                  v-model="termsAccepted"
                  type="checkbox"
                  required
                  class="mt-1 mr-3"
                />
                <span class="text-sm">
                  I agree to the <a href="/terms" class="text-blue-600 hover:underline">terms and conditions</a> *
                </span>
              </label>
              <p v-if="fieldErrors.terms" class="mt-1 ml-6 text-sm text-red-600">{{ fieldErrors.terms }}</p>
            </div>
            
            <div v-if="checkoutConfig.enable_newsletter_signup">
              <label class="flex items-start">
                <input
                  v-model="newsletterSignup"
                  type="checkbox"
                  class="mt-1 mr-3"
                />
                <span class="text-sm">
                  Subscribe to our newsletter for updates and exclusive offers
                </span>
              </label>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white p-6 rounded-lg shadow sticky top-4">
            <h2 class="text-xl font-bold mb-4">Order Summary</h2>
            
            <!-- Cart Items -->
            <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
              <div v-for="item in cartItems" :key="item.id" class="flex items-center gap-3">
                <img
                  v-if="item.product.image"
                  :src="item.product.image"
                  :alt="item.product.name"
                  class="w-16 h-16 object-cover rounded"
                />
                <div class="flex-1">
                  <p class="text-sm font-medium">{{ item.product.name }}</p>
                  <p class="text-xs text-gray-600">Qty: {{ item.quantity }}</p>
                </div>
                <p class="text-sm font-bold">{{ formatPrice(item.total) }}</p>
              </div>
            </div>

            <div class="border-t pt-4 space-y-2">
              <div class="flex justify-between text-sm">
                <span>Subtotal</span>
                <span>{{ formatPrice(cartSummary.subtotal) }}</span>
              </div>
              
              <div class="flex justify-between text-sm">
                <span>Shipping</span>
                <span>{{ cartSummary.shipping.cost === 0 ? 'FREE' : formatPrice(cartSummary.shipping.cost) }}</span>
              </div>
              
              <div class="flex justify-between text-sm">
                <span>Tax</span>
                <span>{{ formatPrice(cartSummary.taxes.total) }}</span>
              </div>
              
              <div class="border-t pt-2 flex justify-between text-lg font-bold">
                <span>Total</span>
                <span>{{ formatPrice(cartSummary.total) }}</span>
              </div>
            </div>

            <button
              @click="submitOrder"
              :disabled="processing"
              class="w-full mt-6 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ processing ? 'Processing...' : 'Place Order' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </ThemeLayout>
</template>
