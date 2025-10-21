<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    icon: string;
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

interface PlatformStatus {
    ready: Array<{ name: string; icon: string }>;
    coming_soon: Array<{ name: string; icon: string }>;
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
    platformStatus: PlatformStatus;
    siteConfig: {
        name: string;
        url: string;
        description: string;
    };
}

const props = defineProps<Props>();

const primaryColor = computed(() => props.theme.settings.primary_color);
const secondaryColor = computed(() => props.theme.settings.secondary_color);

// Hero slider data
const heroSlides = ref([
    {
        id: 1,
        title: 'Summer Collection 2025',
        subtitle: 'New Arrivals',
        description: 'Discover the latest trends in fashion',
        buttonText: 'Shop Now',
        image: '/images/hero/slide1.jpg',
        badge: 'New'
    },
    {
        id: 2,
        title: 'Electronics Sale',
        subtitle: 'Up to 50% Off',
        description: 'Limited time offers on top brands',
        buttonText: 'View Deals',
        image: '/images/hero/slide2.jpg',
        badge: 'Sale'
    },
    {
        id: 3,
        title: 'Home & Living',
        subtitle: 'Transform Your Space',
        description: 'Premium quality home essentials',
        buttonText: 'Explore',
        image: '/images/hero/slide3.jpg',
        badge: 'Trending'
    }
]);

const currentSlide = ref(0);

// Dummy featured products (will be replaced with real data)
const dummyProducts = ref([
    { id: 1, name: 'Wireless Headphones', price: 89.99, image: null, rating: 4.5, reviews: 128 },
    { id: 2, name: 'Smart Watch Pro', price: 299.99, image: null, rating: 4.8, reviews: 256 },
    { id: 3, name: 'Laptop Stand', price: 49.99, image: null, rating: 4.3, reviews: 89 },
    { id: 4, name: 'USB-C Hub', price: 39.99, image: null, rating: 4.6, reviews: 167 },
    { id: 5, name: 'Mechanical Keyboard', price: 129.99, image: null, rating: 4.7, reviews: 203 },
    { id: 6, name: 'Ergonomic Mouse', price: 59.99, image: null, rating: 4.4, reviews: 142 },
    { id: 7, name: 'Monitor 27"', price: 349.99, image: null, rating: 4.9, reviews: 312 },
    { id: 8, name: 'Desk Lamp LED', price: 34.99, image: null, rating: 4.2, reviews: 95 }
]);

// Deal of the day
const dealOfDay = ref({
    product: 'Premium Wireless Speaker',
    originalPrice: 199.99,
    salePrice: 129.99,
    discount: 35,
    endsIn: '23:45:30',
    image: null
});

// Brands
const brands = ref([
    { id: 1, name: 'Brand 1', logo: null },
    { id: 2, name: 'Brand 2', logo: null },
    { id: 3, name: 'Brand 3', logo: null },
    { id: 4, name: 'Brand 4', logo: null },
    { id: 5, name: 'Brand 5', logo: null },
    { id: 6, name: 'Brand 6', logo: null }
]);

// Testimonials
const testimonials = ref([
    {
        id: 1,
        name: 'Sarah Johnson',
        role: 'Verified Buyer',
        rating: 5,
        comment: 'Amazing quality products and fast shipping! Will definitely order again.',
        avatar: null
    },
    {
        id: 2,
        name: 'Michael Chen',
        role: 'Verified Buyer',
        rating: 5,
        comment: 'Excellent customer service and great prices. Highly recommended!',
        avatar: null
    },
    {
        id: 3,
        name: 'Emily Davis',
        role: 'Verified Buyer',
        rating: 4,
        comment: 'Good variety of products. The shopping experience was smooth.',
        avatar: null
    }
]);

const features = [
    { icon: '🚚', title: 'Free Shipping', description: 'On orders over $50' },
    { icon: '🔒', title: 'Secure Payment', description: '100% secure transactions' },
    { icon: '💬', title: '24/7 Support', description: 'Dedicated customer service' },
    { icon: '↩️', title: 'Easy Returns', description: '30-day return policy' }
];

// Auto-advance slides
setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % heroSlides.value.length;
}, 5000);

const goToSlide = (index: number) => {
    currentSlide.value = index;
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
                                    class="inline-block px-4 py-2 rounded-full text-sm font-semibold mb-4 bg-white/20 backdrop-blur-sm"
                                >
                                    {{ slide.badge }}
                                </span>
                                <h1 class="text-5xl md:text-6xl font-bold mb-4 leading-tight">
                                    {{ slide.title }}
                                </h1>
                                <p class="text-xl md:text-2xl mb-2 opacity-90">
                                    {{ slide.subtitle }}
                                </p>
                                <p class="text-lg mb-8 opacity-80">
                                    {{ slide.description }}
                                </p>
                                <button 
                                    class="px-8 py-4 bg-white text-gray-900 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-all hover:scale-105 shadow-xl"
                                >
                                    {{ slide.buttonText }} →
                                </button>
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
        <section class="bg-white border-y border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div v-for="feature in features" :key="feature.title" class="text-center">
                        <div class="text-4xl mb-3">{{ feature.icon }}</div>
                        <h3 class="font-semibold text-gray-900 mb-1">{{ feature.title }}</h3>
                        <p class="text-sm text-gray-600">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Showcase -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Shop by Category</h2>
                    <p class="text-lg text-gray-600">Explore our wide range of products</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div 
                        v-for="category in featuredCategories" 
                        :key="category.id" 
                        class="group relative bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 cursor-pointer transform hover:-translate-y-2"
                    >
                        <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                            <div class="text-7xl group-hover:scale-110 transition-transform duration-300">
                                {{ category.icon }}
                            </div>
                        </div>
                        <div class="p-6 text-center">
                            <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">
                                {{ category.name }}
                            </h3>
                            <p class="text-sm text-gray-600 mb-2">{{ category.description }}</p>
                            <p class="text-xs text-gray-500">{{ category.products_count }} Products</p>
                        </div>
                        <div 
                            class="absolute inset-0 border-2 border-transparent group-hover:border-indigo-500 rounded-2xl transition-colors pointer-events-none"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Deal of the Day -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-red-500 to-pink-500 rounded-3xl overflow-hidden shadow-2xl">
                    <div class="grid md:grid-cols-2 gap-8 items-center p-8 md:p-12">
                        <div class="text-white">
                            <span class="inline-block px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold mb-4">
                                🔥 Deal of the Day
                            </span>
                            <h2 class="text-4xl md:text-5xl font-bold mb-4">{{ dealOfDay.product }}</h2>
                            <div class="flex items-baseline gap-4 mb-6">
                                <span class="text-5xl font-bold">${{ dealOfDay.salePrice }}</span>
                                <span class="text-2xl line-through opacity-75">${{ dealOfDay.originalPrice }}</span>
                                <span class="px-3 py-1 bg-white text-red-500 rounded-full text-sm font-bold">
                                    -{{ dealOfDay.discount }}%
                                </span>
                            </div>
                            <div class="mb-6">
                                <p class="text-sm mb-2 opacity-90">Offer ends in:</p>
                                <div class="flex gap-4 text-2xl font-bold font-mono">
                                    <div class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg">
                                        <span>23</span>
                                        <span class="text-xs block opacity-75">Hours</span>
                                    </div>
                                    <div class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg">
                                        <span>45</span>
                                        <span class="text-xs block opacity-75">Minutes</span>
                                    </div>
                                    <div class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg">
                                        <span>30</span>
                                        <span class="text-xs block opacity-75">Seconds</span>
                                    </div>
                                </div>
                            </div>
                            <button class="px-8 py-4 bg-white text-red-500 rounded-lg font-bold text-lg hover:bg-gray-100 transition-all hover:scale-105 shadow-xl">
                                Grab This Deal →
                            </button>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 flex items-center justify-center h-64">
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
                    <button class="px-6 py-3 border-2 border-gray-900 text-gray-900 rounded-lg font-semibold hover:bg-gray-900 hover:text-white transition-all">
                        View All →
                    </button>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div 
                        v-for="product in dummyProducts" 
                        :key="product.id"
                        class="group bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 cursor-pointer"
                    >
                        <div class="relative aspect-square bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                            <div class="absolute inset-0 flex items-center justify-center text-6xl opacity-50">
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
                            <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">
                                {{ product.name }}
                            </h3>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex text-yellow-400 text-sm">
                                    ★★★★★
                                </div>
                                <span class="text-xs text-gray-500">({{ product.reviews }})</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-gray-900">${{ product.price }}</span>
                                <button class="w-10 h-10 bg-indigo-600 text-white rounded-lg flex items-center justify-center hover:bg-indigo-700 transition-all hover:scale-110">
                                    🛒
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Brands -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">Trusted Brands</h2>
                <div class="grid grid-cols-3 md:grid-cols-6 gap-8">
                    <div 
                        v-for="brand in brands" 
                        :key="brand.id"
                        class="flex items-center justify-center p-6 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer grayscale hover:grayscale-0"
                    >
                        <div class="text-4xl">🏷️</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">What Our Customers Say</h2>
                    <p class="text-lg text-gray-600">Real reviews from real customers</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    <div 
                        v-for="testimonial in testimonials" 
                        :key="testimonial.id"
                        class="bg-white rounded-2xl p-8 shadow-md hover:shadow-xl transition-all"
                    >
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-indigo-400 to-purple-400 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                {{ testimonial.name.charAt(0) }}
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ testimonial.name }}</h4>
                                <p class="text-sm text-gray-600">{{ testimonial.role }}</p>
                            </div>
                        </div>
                        <div class="flex text-yellow-400 mb-4">
                            <span v-for="i in testimonial.rating" :key="i">★</span>
                        </div>
                        <p class="text-gray-700 italic">"{{ testimonial.comment }}"</p>
                    </div>
                </div>
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
                <div class="flex flex-col sm:flex-row gap-4 max-w-xl mx-auto">
                    <input
                        type="email"
                        placeholder="Enter your email address"
                        class="flex-1 px-6 py-4 rounded-lg text-gray-900 focus:outline-none focus:ring-4 focus:ring-white/50"
                    />
                    <button class="px-8 py-4 bg-white text-gray-900 rounded-lg font-bold hover:bg-gray-100 transition-all hover:scale-105 shadow-xl">
                        Subscribe
                    </button>
                </div>
                <p class="text-sm mt-4 opacity-75">Join 10,000+ subscribers. Unsubscribe anytime.</p>
            </div>
        </section>

        <!-- Platform Status (Development Info) -->
        <section v-if="platformStatus" class="py-16 bg-white border-t">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Platform Development Status</h3>
                    <p class="text-gray-600">Track our progress as we build amazing features</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                    <div 
                        v-for="feature in platformStatus.ready" 
                        :key="feature.name"
                        class="flex items-center justify-between rounded-xl p-4 bg-green-50 border-2 border-green-200"
                    >
                        <span class="font-medium text-green-900 text-sm">{{ feature.name }}</span>
                        <span class="text-xl">{{ feature.icon }}</span>
                    </div>
                    <div 
                        v-for="feature in platformStatus.coming_soon" 
                        :key="feature.name"
                        class="flex items-center justify-between rounded-xl p-4 bg-yellow-50 border-2 border-yellow-200"
                    >
                        <span class="font-medium text-yellow-900 text-sm">{{ feature.name }}</span>
                        <span class="text-xl">{{ feature.icon }}</span>
                    </div>
                </div>
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
