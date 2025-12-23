<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string;
    products_count: number;
    image: string | null;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    image: string | null;
}

interface CMSBlock {
    id: number;
    title: string;
    content: string;
    type: string;
    data?: {
        title?: string;
        description?: string;
        image?: string;
        alt?: string;
        cta_text?: string;
        cta_url?: string;
    };
}

interface Props {
    theme: {
        name: string;
        slug: string;
        settings: {
            primary_color: string;
            secondary_color: string;
            features: Record<string, boolean>;
        };
    };
    featuredCategories: Category[];
    featuredProducts: Product[];
    newProducts?: Product[];
    cmsBlocks?: {
        hero?: CMSBlock;
        deal?: CMSBlock;
        features?: CMSBlock;
        testimonials?: CMSBlock;
        brands?: CMSBlock;
    };
    siteConfig: {
        name: string;
        url: string;
        description: string;
    };
}

const props = defineProps<Props>();

const primaryColor = computed(() => props.theme.settings.primary_color);
const secondaryColor = computed(() => props.theme.settings.secondary_color);

// Hero slider - uses CMS banner block data
const heroSlides = ref([
    {
        id: 1,
        title: props.cmsBlocks?.hero?.data?.title || props.cmsBlocks?.hero?.title || 'Welcome to Our Store',
        subtitle: '',
        description: props.cmsBlocks?.hero?.data?.description || 'Discover the latest products',
        buttonText: props.cmsBlocks?.hero?.data?.cta_text || 'Shop Now',
        buttonUrl: props.cmsBlocks?.hero?.data?.cta_url || '/products',
        image: props.cmsBlocks?.hero?.data?.image || '/images/hero/slide1.jpg',
        imageAlt: props.cmsBlocks?.hero?.data?.alt || 'Hero banner',
        badge: 'New',
        isBannerType: props.cmsBlocks?.hero?.type === 'banner'
    }
]);

const currentSlide = ref(0);

// Auto-advance slides
setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % heroSlides.value.length;
}, 5000);

const goToSlide = (index: number) => {
    currentSlide.value = index;
};

// Format price helper
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
};

// Newsletter subscription
const newsletterEmail = ref('');
const newsletterLoading = ref(false);
const newsletterMessage = ref('');
const newsletterSuccess = ref(false);

const subscribeNewsletter = async () => {
    if (!newsletterEmail.value) {
        newsletterMessage.value = 'Please enter your email address';
        newsletterSuccess.value = false;
        return;
    }

    newsletterLoading.value = true;
    newsletterMessage.value = '';

    try {
        const response = await fetch('/newsletter/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ email: newsletterEmail.value })
        });

        const data = await response.json();

        newsletterMessage.value = data.message;
        newsletterSuccess.value = data.success;

        if (data.success) {
            newsletterEmail.value = '';
            // Auto-clear success message after 5 seconds
            setTimeout(() => {
                newsletterMessage.value = '';
            }, 5000);
        }
    } catch {
        newsletterMessage.value = 'An error occurred. Please try again later.';
        newsletterSuccess.value = false;
    } finally {
        newsletterLoading.value = false;
    }
};
</script>

<template>
    <ThemeLayout>
        <Head :title="`${siteConfig.name} - Premium E-Commerce Store`" />
        
        <!-- Hero Slider -->
        <section class="relative h-[500px] md:h-[600px] overflow-hidden">
            <div class="absolute inset-0">
                <TransitionGroup name="fade">
                    <div
                        v-for="(slide, index) in heroSlides"
                        v-show="index === currentSlide"
                        :key="slide.id"
                        class="absolute inset-0"
                    >
                        <div 
                            class="absolute inset-0 bg-gradient-to-r from-gray-900/90 to-gray-900/50"
                            :style="{ 
                                background: `linear-gradient(135deg, ${primaryColor}dd 0%, ${secondaryColor}dd 100%)` 
                            }"
                        />
                        <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center">
                            <div class="max-w-2xl text-white">
                                <span 
                                    v-if="slide.badge"
                                    class="inline-block px-4 py-2 rounded-full text-sm font-semibold mb-4 bg-white/20 backdrop-blur-sm"
                                >
                                    {{ slide.badge }}
                                </span>
                                <h1 class="text-5xl md:text-6xl font-bold mb-4 leading-tight">
                                    {{ slide.title }}
                                </h1>
                                <p v-if="slide.subtitle" class="text-xl md:text-2xl mb-2 opacity-90">
                                    {{ slide.subtitle }}
                                </p>
                                <p v-if="slide.description" class="text-lg mb-8 opacity-80">
                                    {{ slide.description }}
                                </p>
                                
                                <a 
                                    :href="slide.buttonUrl"
                                    class="inline-block px-8 py-4 bg-white text-gray-900 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-all hover:scale-105 shadow-xl"
                                >
                                    {{ slide.buttonText }} →
                                </a>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>
            </div>
            
            <!-- Slide indicators -->
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 flex gap-3 z-10">
                <button
                    v-for="(slide, index) in heroSlides"
                    :key="slide.id"
                    @click="goToSlide(index)"
                    class="w-3 h-3 rounded-full transition-all"
                    :class="index === currentSlide ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/75'"
                />
            </div>
        </section>

        <!-- Features Bar -->
        <section v-if="cmsBlocks?.features" class="bg-white border-y border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div v-html="cmsBlocks.features.content" class="prose max-w-none"></div>
            </div>
        </section>

        <!-- Categories Showcase -->
                <!-- Categories Showcase -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Shop by Category</h2>
                    <p class="text-lg text-gray-600">Explore our wide range of products</p>
                </div>
                <div v-if="featuredCategories && featuredCategories.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <a 
                        v-for="category in featuredCategories" 
                        :key="category.id"
                        :href="`/products?category=${category.slug}`"
                        class="group relative bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 cursor-pointer transform hover:-translate-y-2"
                    >
                        <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                            <div v-if="category.image" class="w-full h-full">
                                <img :src="category.image" :alt="category.name" class="w-full h-full object-cover">
                            </div>
                            <div v-else class="text-7xl group-hover:scale-110 transition-transform duration-300">
                                🏷️
                            </div>
                        </div>
                        <div class="p-6 text-center">
                            <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">
                                {{ category.name }}
                            </h3>
                            <p v-if="category.description" class="text-sm text-gray-600 mb-2 line-clamp-2">{{ category.description }}</p>
                            <p class="text-xs text-gray-500">{{ category.products_count }} Products</p>
                        </div>
                        <div 
                            class="absolute inset-0 border-2 border-transparent group-hover:border-indigo-500 rounded-2xl transition-colors pointer-events-none"
                        />
                    </a>
                </div>
                <div v-else class="text-center text-gray-500 py-12">
                    <p>No categories available yet.</p>
                </div>
            </div>
        </section>

        <!-- Deal of the Day (Only show if CMS block exists) -->
        <section v-if="cmsBlocks?.deal" class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-red-500 to-pink-500 rounded-3xl overflow-hidden shadow-2xl">
                    <div class="grid md:grid-cols-2 gap-8 items-center p-8 md:p-12">
                        <div class="text-white">
                            <span class="inline-block px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold mb-4">
                                🔥 Deal of the Day
                            </span>
                            <h2 class="text-4xl md:text-5xl font-bold mb-4">{{ cmsBlocks.deal.title }}</h2>
                            <div v-html="cmsBlocks.deal.content" class="text-white prose prose-invert"></div>
                            <button 
                                class="mt-6 px-8 py-4 bg-white text-red-500 rounded-full text-lg font-bold hover:bg-gray-100 transition-all hover:scale-105 shadow-lg"
                            >
                                Grab This Deal →
                            </button>
                        </div>
                        <div class="flex justify-center">
                            <div class="text-white text-8xl">🎧</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-12">
                    <div>
                        <h2 class="text-4xl font-bold text-gray-900 mb-2">Featured Products</h2>
                        <p class="text-lg text-gray-600">Handpicked items just for you</p>
                    </div>
                    <a href="/products" class="px-6 py-3 border-2 border-gray-900 text-gray-900 rounded-lg font-semibold hover:bg-gray-900 hover:text-white transition-all">
                        View All →
                    </a>
                </div>
                <div v-if="featuredProducts && featuredProducts.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <a 
                        v-for="product in featuredProducts.slice(0, 8)" 
                        :key="product.id"
                        :href="`/product/${product.slug}`"
                        class="group bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 cursor-pointer"
                    >
                        <div class="relative aspect-square bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                            <div v-if="product.image" class="w-full h-full">
                                <img :src="product.image" :alt="product.name" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            </div>
                            <div v-else class="absolute inset-0 flex items-center justify-center text-6xl opacity-50">
                                📦
                            </div>
                            <div class="absolute top-4 right-4">
                                <button class="w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                                    ♡
                                </button>
                            </div>
                            <div class="absolute bottom-4 left-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="w-full py-3 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800 transition-all">
                                    Quick View
                                </button>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors line-clamp-2">
                                {{ product.name }}
                            </h3>
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-2xl font-bold text-gray-900">{{ formatPrice(product.price) }}</span>
                                <button class="w-10 h-10 bg-indigo-600 text-white rounded-lg flex items-center justify-center hover:bg-indigo-700 transition-all hover:scale-110">
                                    🛒
                                </button>
                            </div>
                        </div>
                    </a>
                </div>
                <div v-else class="text-center text-gray-500 py-12">
                    <p>No featured products available yet.</p>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section v-if="cmsBlocks?.testimonials" class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-html="cmsBlocks.testimonials.content" class="prose max-w-none"></div>
            </div>
        </section>

        <!-- Brands Section -->
        <section v-if="cmsBlocks?.brands" class="py-12 bg-white border-y border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-html="cmsBlocks.brands.content" class="prose max-w-none"></div>
            </div>
        </section>

        <!-- Newsletter -->
        <section 
            class="py-20 text-white"
            :style="{ background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)` }"
        >
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="text-6xl mb-6">📧</div>
                <h2 class="text-4xl font-bold mb-4">Subscribe to Our Newsletter</h2>
                <p class="text-xl mb-8 opacity-90">Get exclusive deals, new arrivals, and special offers directly in your inbox</p>
                <form @submit.prevent="subscribeNewsletter" class="flex flex-col sm:flex-row gap-4 max-w-xl mx-auto">
                    <input
                        v-model="newsletterEmail"
                        type="email"
                        placeholder="Enter your email address"
                        class="flex-1 px-6 py-4 rounded-lg text-gray-900 border-2 border-white/30 focus:outline-none focus:ring-4 focus:ring-white/50 focus:border-white"
                        :disabled="newsletterLoading"
                        required
                    />
                    <button 
                        type="submit"
                        class="px-8 py-4 bg-white text-gray-900 rounded-lg font-bold hover:bg-gray-100 transition-all hover:scale-105 shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                        :disabled="newsletterLoading"
                    >
                        <span v-if="newsletterLoading">Subscribing...</span>
                        <span v-else>Subscribe</span>
                    </button>
                </form>
                <div v-if="newsletterMessage" class="mt-4 px-4 py-3 rounded-lg" :class="newsletterSuccess ? 'bg-green-500/20 text-white' : 'bg-red-500/20 text-white'">
                    {{ newsletterMessage }}
                </div>
                <p class="text-sm mt-4 opacity-75">Join 10,000+ subscribers. Unsubscribe anytime.</p>
            </div>
        </section>

    </ThemeLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 1s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
