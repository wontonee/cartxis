<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const featuredCategories = ref([
    { id: 1, name: 'Electronics', icon: '', description: 'Latest gadgets and tech', productsCount: 0 },
    { id: 2, name: 'Fashion', icon: '', description: 'Trendy clothing and accessories', productsCount: 0 },
    { id: 3, name: 'Home & Garden', icon: '', description: 'Furniture and decor', productsCount: 0 },
    { id: 4, name: 'Sports', icon: '', description: 'Equipment and gear', productsCount: 0 }
]);

const features = ref([
    { icon: '', title: 'Free Shipping', description: 'On orders over $50' },
    { icon: '', title: 'Secure Payment', description: '100% secure transactions' },
    { icon: '', title: '24/7 Support', description: 'Dedicated customer service' },
    { icon: '', title: 'Easy Returns', description: '30-day return policy' }
]);

const statusFeatures = ref([
    { name: 'Admin Panel', status: 'ready', icon: '' },
    { name: 'Theme System', status: 'ready', icon: '' },
    { name: 'Product Reviews Backend', status: 'ready', icon: '' },
    { name: 'Authentication', status: 'ready', icon: '' },
    { name: 'Product Catalog', status: 'coming-soon', icon: '' },
    { name: 'Shopping Cart', status: 'coming-soon', icon: '' },
    { name: 'Checkout System', status: 'coming-soon', icon: '' },
    { name: 'Payment Integration', status: 'coming-soon', icon: '' }
]);
</script>

<template>
    <Head title="Welcome to Vortex Store" />
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-indigo-600">Vortex</h1>
                    </div>
                    <nav class="hidden md:flex items-center space-x-8">
                        <a href="#" class="text-gray-700 hover:text-indigo-600">Shop</a>
                        <a href="#" class="text-gray-700 hover:text-indigo-600">Categories</a>
                        <a href="#" class="text-gray-700 hover:text-indigo-600">Deals</a>
                        <a href="#" class="text-gray-700 hover:text-indigo-600">About</a>
                    </nav>
                    <div class="flex items-center space-x-4">
                        <div class="relative hidden md:block">
                            <input type="text" placeholder="Search products..." class="w-64 rounded-lg border border-gray-300 px-4 py-2 pl-10 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <button class="relative rounded-lg p-2 text-gray-600 hover:bg-gray-100">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-xs text-white">0</span>
                        </button>
                        <Link href="/admin/login" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Login</Link>
                        <Link href="/register" class="rounded-lg border border-indigo-600 px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50">Register</Link>
                    </div>
                </div>
            </div>
        </header>
        <section class="bg-gradient-to-r from-indigo-600 to-purple-600 py-20 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-5xl font-bold mb-4">Welcome to Vortex Store</h2>
                    <p class="text-xl mb-8 text-indigo-100">Your one-stop shop for everything you need</p>
                    <div class="flex justify-center space-x-4">
                        <button class="rounded-lg bg-white px-8 py-3 text-lg font-medium text-indigo-600 hover:bg-gray-100">Shop Now</button>
                        <button class="rounded-lg border-2 border-white px-8 py-3 text-lg font-medium text-white hover:bg-white/10">Learn More</button>
                    </div>
                </div>
            </div>
        </section>
        <section class="py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div v-for="feature in features" :key="feature.title" class="text-center">
                        <div class="text-5xl mb-4">{{ feature.icon }}</div>
                        <h3 class="text-lg font-semibold mb-2">{{ feature.title }}</h3>
                        <p class="text-gray-600">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="py-16 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-12">Shop by Category</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div v-for="category in featuredCategories" :key="category.id" class="group cursor-pointer">
                        <div class="rounded-lg border-2 border-gray-200 p-8 text-center transition-all hover:border-indigo-500 hover:shadow-lg">
                            <div class="text-6xl mb-4">{{ category.icon }}</div>
                            <h3 class="text-xl font-semibold mb-2 group-hover:text-indigo-600">{{ category.name }}</h3>
                            <p class="text-gray-600 mb-2">{{ category.description }}</p>
                            <p class="text-sm text-gray-500">{{ category.productsCount }} products</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="py-16 bg-gray-100">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-4">Platform Status</h2>
                <p class="text-center text-gray-600 mb-12">See what's ready and what's coming soon</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl mx-auto">
                    <div v-for="feature in statusFeatures" :key="feature.name" :class="['flex items-center justify-between rounded-lg p-4', feature.status === 'ready' ? 'bg-green-50 border-2 border-green-200' : 'bg-yellow-50 border-2 border-yellow-200']">
                        <span class="font-medium" :class="feature.status === 'ready' ? 'text-green-900' : 'text-yellow-900'">{{ feature.name }}</span>
                        <span class="text-2xl">{{ feature.icon }}</span>
                    </div>
                </div>
            </div>
        </section>
        <section class="py-16 bg-indigo-600 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold mb-4">Stay Updated</h2>
                    <p class="mb-8 text-indigo-100">Subscribe to our newsletter for exclusive deals and updates</p>
                    <div class="flex justify-center">
                        <div class="flex w-full max-w-md">
                            <input type="email" placeholder="Enter your email" class="flex-1 rounded-l-lg px-4 py-3 text-gray-900 focus:outline-none" />
                            <button class="rounded-r-lg bg-purple-600 px-6 py-3 font-medium hover:bg-purple-700">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <footer class="bg-gray-900 text-gray-300 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-xl font-bold text-white mb-4">Vortex</h3>
                        <p class="text-sm">Your trusted e-commerce platform</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white mb-4">Quick Links</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white">Shop</a></li>
                            <li><a href="#" class="hover:text-white">Categories</a></li>
                            <li><a href="#" class="hover:text-white">Deals</a></li>
                            <li><a href="#" class="hover:text-white">About Us</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white mb-4">Customer Service</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white">Contact Us</a></li>
                            <li><a href="#" class="hover:text-white">Shipping Info</a></li>
                            <li><a href="#" class="hover:text-white">Returns</a></li>
                            <li><a href="#" class="hover:text-white">FAQ</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white mb-4">Contact</h4>
                        <ul class="space-y-2 text-sm">
                            <li>Email: info@vortex.test</li>
                            <li>Phone: (555) 123-4567</li>
                            <li>Address: 123 Store St</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm">
                    <p>&copy; 2025 Vortex. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
