<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import ProductCard from '../../components/ProductCard.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useCart } from '@/composables/useCart';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Pagination, Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';

const { formatPrice } = useCurrency();
const { addToCart, loading: cartLoading } = useCart();
const heroAddingToCart = ref(false);

const handleHeroAddToCart = async (slide: any) => {
    const product = slide?.product;
    if (!product) {
        // No product linked — fall back to navigating to /products
        window.location.href = slide?.slug ? `/product/${slide.slug}` : '/products';
        return;
    }
    heroAddingToCart.value = true;
    try {
        const result = await addToCart(product.id, 1);
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: result?.message || 'Product added to cart!', type: 'success' }
        }));
    } catch (e) {
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: 'Failed to add to cart', type: 'error' }
        }));
    } finally {
        heroAddingToCart.value = false;
    }
};

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    special_price: number | null;
    image: string | null;
    rating: number;
    reviews_count: number;
    in_stock: boolean;
    brand?: { id: number; name: string; slug: string };
    badges?: Array<{ text: string; class: string }>;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    image?: string | null;
    products_count?: number;
}

interface Props {
    theme?: any;
    featuredCategories?: Category[];
    featuredProducts?: Product[];
    newProducts?: Product[];
    cmsBlocks?: Record<string, any>;
    siteConfig?: any;
}

const props = defineProps<Props>();

// Parse CMS block content (may be JSON string)
function parseCmsContent(block: any): any {
    if (!block) return {};
    let data = block.data || {};
    if (block.content && typeof block.content === 'string') {
        try {
            const parsed = JSON.parse(block.content);
            if (typeof parsed === 'object') data = { ...data, ...parsed };
        } catch { /* content is plain text, ignore */ }
    }
    return data;
}

// CMS-driven marquee text
const marqueeText = computed(() => {
    const m = props.cmsBlocks?.marquee;
    if (m) {
        const d = parseCmsContent(m);
        return d.text || m.content || 'Limited Time Offer';
    }
    return 'Limited Time Offer';
});

// CMS-driven banner
const bannerData = computed(() => {
    const b = props.cmsBlocks?.banner;
    const d = b ? parseCmsContent(b) : {};
    return {
        heading: d.heading || 'See The Worlds Like Birds',
        buttonText: d.button_text || 'View All Products',
        buttonUrl: d.button_url || '/products',
        image: d.image || '/images/homepage-1/banner-1.png',
        videoUrl: d.video_url || '',
    };
});

// CMS-driven offer cards
const offerCard1 = computed(() => {
    const b = props.cmsBlocks?.offer_1;
    const d = b ? parseCmsContent(b) : {};
    return {
        badge: d.badge || 'GET 30% OFF',
        heading: d.heading || 'New power double',
        buttonText: d.button_text || 'View All',
        buttonUrl: d.button_url || '/products',
        image: d.image || '/images/homepage-1/offer-1.png',
        bgColor: d.bg_color || '#FFDEDC',
    };
});
const offerCard2 = computed(() => {
    const b = props.cmsBlocks?.offer_2;
    const d = b ? parseCmsContent(b) : {};
    return {
        badge: d.badge || 'GET 30% OFF',
        heading: d.heading || 'New power double',
        buttonText: d.button_text || 'View All',
        buttonUrl: d.button_url || '/products',
        image: d.image || '/images/homepage-1/offer-2.png',
        bgColor: d.bg_color || '#CCD9EB',
    };
});

// Hero slides
const heroSlides = computed(() => {
    const formatHeroPrice = (price: any) => {
        if (!price) return '';
        const num = parseFloat(String(price).replace(/[^0-9.]/g, ''));
        if (isNaN(num)) return String(price);
        return formatPrice(num);
    };

    const calcDiscount = (p: any) => {
        if (p?.special_price && p?.price && p.special_price < p.price) {
            return `${Math.round((1 - p.special_price / p.price) * 100)}% <span class="color-text">discount</span> for all items`;
        }
        return null;
    };

    // Get up to 3 featured products for the hero
    const products = (props.featuredProducts || []).slice(0, 3);

    const slides = props.cmsBlocks?.hero_slides;
    if (Array.isArray(slides) && slides.length) {
        return slides.map((block: any, i: number) => {
            const d = parseCmsContent(block);
            const p = products[i] || null;
            return {
                title: p?.name || d.heading || d.title || 'Featured Product',
                subtitle: calcDiscount(p) || d.subheading || d.subtitle || 'Best deals today',
                description: p?.short_description || d.description || p?.name || '',
                image: p?.image || d.image || null,
                price: formatHeroPrice(p?.special_price || p?.price || d.price),
                oldPrice: p?.price && p?.special_price ? formatHeroPrice(p.price) : (d.old_price ? formatHeroPrice(d.old_price) : null),
                colors: d.colors || null,
                slug: p?.slug || null,
                product: p,
            };
        });
    }
    // Try single hero block
    const hero = props.cmsBlocks?.hero;
    if (hero) {
        const d = parseCmsContent(hero);
        const p = products[0] || null;
        return [{
            title: p?.name || d.heading || d.title || 'Featured Product',
            subtitle: calcDiscount(p) || d.subheading || d.subtitle || 'Best deals today',
            description: p?.short_description || d.description || p?.name || '',
            image: p?.image || d.image || null,
            price: formatHeroPrice(p?.special_price || p?.price || d.price),
            oldPrice: p?.price && p?.special_price ? formatHeroPrice(p.price) : (d.old_price ? formatHeroPrice(d.old_price) : null),
            colors: d.colors || null,
            slug: p?.slug || null,
            product: p,
        }];
    }
    // Fallback: use actual products data
    if (products.length) {
        return products.map((p: any) => ({
            title: p.name,
            subtitle: calcDiscount(p) || 'Best deal today',
            description: p.short_description || p.name,
            image: p.image || null,
            price: formatHeroPrice(p.special_price || p.price),
            oldPrice: p.special_price ? formatHeroPrice(p.price) : null,
            colors: null,
            slug: p.slug,
            product: p,
        }));
    }
    return [
        {
            title: 'Electric charging smart tea maker',
            subtitle: '59% discount for all items',
            description: 'Sell globally in minutes with localized currencies, languages, and experiences in every market.',
            image: null,
            price: '$125.75',
            slug: null,
            product: null,
        },
    ];
});

// Category Font Awesome icons
const categoryIcons = [
    'fa-tablet-screen-button', 'fa-laptop', 'fa-headphones', 'fa-plug',
    'fa-mobile-screen', 'fa-gamepad', 'fa-camera', 'fa-tv', 'fa-keyboard', 'fa-hard-drive',
];
function getCategoryIcon(index: number): string {
    return categoryIcons[index % categoryIcons.length];
}

// Best seller tabs
type TabFilter = 'latest' | 'popular' | 'on-sale';
const activeTab = ref<TabFilter>('latest');
const bestSellerTabs: { key: TabFilter; label: string }[] = [
    { key: 'latest', label: 'Latest' },
    { key: 'popular', label: 'Popular' },
    { key: 'on-sale', label: 'On-Sale' },
];
const filteredProducts = computed(() => {
    const products = props.featuredProducts || [];
    switch (activeTab.value) {
        case 'popular': return [...products].sort((a, b) => (b.rating || 0) - (a.rating || 0));
        case 'on-sale': return products.filter((p) => p.special_price && p.special_price < p.price);
        default: return products;
    }
});

// Featured Products tabs
const activeFeaturedTab = ref('all');
const featuredCategoryTabs = computed(() => {
    const cats = props.featuredCategories?.slice(0, 5) || [];
    return [{ slug: 'all', name: 'All' }, ...cats.map((c) => ({ slug: c.slug, name: c.name }))];
});
const filteredFeaturedProducts = computed(() => {
    const products = props.newProducts || props.featuredProducts || [];
    if (activeFeaturedTab.value === 'all') return products;
    return products;
});

// Testimonials
const testimonials = [
    { text: 'One of the most powerful takeaways from this store is the emphasis on quality and customer satisfaction. The products exceeded all my expectations.', name: 'Ronald Richards', role: 'Marketing Coordinator', rating: 3 },
    { text: 'The customer service experience is unmatched. Quick delivery, excellent packaging, and the product quality exceeded my expectations by far.', name: 'Dianne Russell', role: 'Project Manager', rating: 4 },
    { text: 'This store truly sets the standard for online electronics shopping. From browsing to checkout, every step was seamless and professional.', name: 'Jerome Bell', role: 'Software Engineer', rating: 5 },
    { text: 'From the very first purchase, I was impressed by the attention to detail. The product descriptions are accurate and delivery was super fast.', name: 'Cameron Williamson', role: 'UI/UX Designer', rating: 4 },
];

const colorSwatches = computed(() => heroSlides.value[0]?.colors || ['#770215', '#E9CF10', '#35424B']);
const swiperModules = [Autoplay, Pagination, Navigation];

defineOptions({ layout: ThemeLayout });
</script>

<template>
    <div>
        <!-- ===== HERO / INTRO ===== -->
        <section class="dmart-intro-section">
            <div class="dmart-container">
                <div class="dmart-intro-wrapper">
                    <!-- Floating product thumbnails on the right (white circles) -->
                    <div class="dmart-thumb-shape-wrapper">
                        <div v-for="(slide, si) in heroSlides.slice(0, 3)" :key="'shape-' + si" class="thumbShape">
                            <img loading="lazy" :src="slide.image || `/images/homepage-1/introThumbShape1_${si + 1}.png`" :alt="slide.title" />
                        </div>
                    </div>

                    <div class="grid gap-y-5 grid-cols-1 lg:grid-cols-2">
                        <!-- Content -->
                        <div class="col-span-1">
                            <div class="dmart-intro-content">
                                <div class="subtitle">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" class="inline">
                                        <g clip-path="url(#clip_tag)">
                                            <path d="M12.8333 6.10352e-05H7.98425C7.66347 6.10352e-05 7.21514 0.185895 6.9885 0.412368L0.34046 7.06037C-0.113477 7.51379 -0.113477 8.2572 0.34046 8.71008L5.29046 13.6599C5.74338 14.1133 6.48606 14.1133 6.93966 13.6594L13.5877 7.01242C13.8141 6.78598 14 6.33693 14 6.01684V1.16678C14 0.525228 13.4748 6.10352e-05 12.8333 6.10352e-05ZM10.4998 4.66675C9.85547 4.66675 9.33311 4.14384 9.33311 3.50004C9.33311 2.85517 9.85547 2.33332 10.4998 2.33332C11.1442 2.33332 11.6667 2.85517 11.6667 3.50004C11.6667 4.14384 11.1442 4.66675 10.4998 4.66675Z" fill="#0A111E"/>
                                        </g>
                                        <defs><clipPath id="clip_tag"><rect width="14" height="14" fill="white"/></clipPath></defs>
                                    </svg>
                                    <span v-html="heroSlides[0]?.subtitle"></span>
                                </div>
                                <h1>{{ heroSlides[0]?.title }}</h1>
                                <p>{{ heroSlides[0]?.description }}</p>

                                <!-- Pricing Card -->
                                <div class="dmart-pricing-card">
                                    <div class="price-box">
                                        <div class="price-label">Price</div>
                                        <div class="price-value">{{ heroSlides[0]?.price }}</div>
                                        <div v-if="heroSlides[0]?.oldPrice" class="text-xs text-gray-400 line-through mt-0.5">{{ heroSlides[0]?.oldPrice }}</div>
                                    </div>
                                    <div class="color-box">
                                        <div class="color-label">Color</div>
                                        <div class="color-plate">
                                            <span v-for="(color, ci) in (heroSlides[0]?.colors || colorSwatches)" :key="ci" class="color-swatch" :class="{ active: ci === 0 }" :style="{ backgroundColor: color }"></span>
                                        </div>
                                    </div>
                                    <Link v-if="heroSlides[0]?.product?.image" :href="heroSlides[0]?.slug ? `/product/${heroSlides[0].slug}` : '/products'" class="w-[50px] h-[50px] rounded-full overflow-hidden flex-shrink-0 ring-2 ring-gray-200">
                                        <img loading="lazy" :src="heroSlides[0].product.image" :alt="heroSlides[0]?.title" class="w-full h-full object-cover" />
                                    </Link>
                                    <a v-else href="#" class="w-[50px] h-[50px] rounded-full overflow-hidden flex-shrink-0">
                                        <img loading="lazy" src="/images/homepage-1/video-1.png" alt="play" class="w-full h-full object-cover" />
                                    </a>
                                </div>

                                <div class="dmart-intro-content btn-wrapper">
                                    <button
                                        @click="handleHeroAddToCart(heroSlides[0])"
                                        class="dmart-btn dmart-btn-primary rounded-lg px-8 py-4 text-base whitespace-nowrap"
                                        :disabled="heroAddingToCart"
                                    >
                                        {{ heroAddingToCart ? 'Adding...' : 'Add To Cart' }}
                                    </button>
                                    <Link :href="heroSlides[0]?.slug ? `/product/${heroSlides[0].slug}` : '/products'" class="dmart-btn border-2 border-title bg-title text-white rounded-lg px-8 py-4 text-base whitespace-nowrap hover:opacity-90">
                                        View Now
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Slider -->
                        <div class="col-span-1">
                            <div class="dmart-thumb-slider">
                                <div class="introThumbShape hidden lg:block">
                                    <img loading="lazy" src="/images/homepage-1/introThumbShape1_4.png" alt="shape" />
                                </div>
                                <div class="intro-thumb">
                                    <Swiper :modules="swiperModules" :slides-per-view="1" :autoplay="{ delay: 4000, disableOnInteraction: false }" :pagination="{ clickable: true }" :loop="heroSlides.length > 1" class="dmart-intro-swiper">
                                        <SwiperSlide v-for="(slide, si) in heroSlides" :key="si">
                                            <Link :href="slide.slug ? `/product/${slide.slug}` : '/products'" class="flex items-center justify-center p-8 min-h-[400px]">
                                                <img :src="slide.image || `/images/homepage-1/introThumb1_${si + 1}.png`" :alt="slide.title" class="max-h-[380px] object-contain" />
                                            </Link>
                                        </SwiperSlide>
                                    </Swiper>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== MARQUEE ===== -->
        <section class="dmart-container my-5">
            <div class="dmart-marquee-section">
                <div class="dmart-marquee-track">
                    <template v-for="n in 20" :key="n">
                        <p>{{ marqueeText }}</p>
                        <i class="fa-solid fa-star star-icon"></i>
                    </template>
                </div>
            </div>
        </section>

        <!-- ===== POPULAR CATEGORIES ===== -->
        <section class="dmart-section-padding" v-if="featuredCategories?.length">
            <div class="dmart-content-container">
                <div class="flex flex-col my-5 lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <span class="dmart-tag"><i class="fa-solid fa-layer-group text-theme-1 text-xs"></i> Browse</span>
                        <h2 class="dmart-section-title mt-4">Popular Categories</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="dmart-slider-arrow cat-prev"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="dmart-slider-arrow cat-next"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                </div>
                <div class="relative dmart-category-swiper-wrap">
                    <Swiper :modules="swiperModules" :slides-per-view="2" :space-between="20" :grab-cursor="true" :navigation="{ nextEl: '.cat-next', prevEl: '.cat-prev' }" :breakpoints="{ 640: { slidesPerView: 3 }, 768: { slidesPerView: 4 }, 1024: { slidesPerView: 5 }, 1280: { slidesPerView: 6 } }">
                        <SwiperSlide v-for="(cat, ci) in featuredCategories" :key="cat.id">
                            <Link :href="`/category/${cat.slug}`" class="dmart-category-box">
                                <div class="icon"><i :class="`fa-solid ${getCategoryIcon(ci)}`"></i></div>
                                <div>
                                    <h6>{{ cat.name }}</h6>
                                    <p>{{ cat.products_count || 0 }} items</p>
                                </div>
                            </Link>
                        </SwiperSlide>
                    </Swiper>
                </div>
            </div>
        </section>

        <!-- ===== BEST SELLERS ===== -->
        <section class="dmart-content-container" v-if="featuredProducts?.length">
            <hr class="border-border-1" />
            <div class="my-10">
                <div class="flex flex-col my-5 lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <span class="dmart-tag"><i class="fa-solid fa-fire text-theme-1 text-xs"></i> Best Sellers</span>
                        <h2 class="dmart-section-title mt-4">Best Seller</h2>
                    </div>
                    <div class="flex items-center flex-wrap gap-2.5">
                        <button v-for="tab in bestSellerTabs" :key="tab.key" class="dmart-btn-outline" :class="{ active: activeTab === tab.key }" @click="activeTab = tab.key">{{ tab.label }}</button>
                        <Link href="/shop" class="dmart-btn-secondary">View All</Link>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-7">
                    <TransitionGroup name="fade" appear>
                        <ProductCard v-for="product in filteredProducts.slice(0, 8)" :key="product.id" :product="product" variant="default" />
                    </TransitionGroup>
                </div>
            </div>
        </section>

        <!-- ===== BANNER ===== -->
        <section class="dmart-content-container my-10">
            <div class="dmart-banner-gradient">
                <div class="w-full grid lg:grid-cols-2 lg:gap-16 md:gap-12 gap-10 xl:gap-24">
                    <div class="xl:p-14 lg:p-12 md:p-10 p-8 relative">
                        <button v-if="bannerData.videoUrl" class="play-btn mt-5 ml-5"><i class="fa-solid fa-play text-white"></i></button>
                        <h2 class="xl:text-5xl lg:text-4xl text-3xl font-light text-white mt-10 leading-tight">{{ bannerData.heading }}</h2>
                        <Link :href="bannerData.buttonUrl" class="dmart-btn-white-pill mt-7 block w-fit">{{ bannerData.buttonText }}</Link>
                    </div>
                    <div class="xl:px-14 lg:px-12 md:px-10 px-8 xl:py-11 lg:py-10 md:py-8 py-6 flex items-center justify-center">
                        <img loading="lazy" :src="bannerData.image" alt="Promotion" class="max-h-[300px] object-contain" />
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== FEATURED PRODUCTS ===== -->
        <section class="dmart-content-container my-10" v-if="(newProducts?.length || featuredProducts?.length)">
            <div class="flex items-center flex-col justify-center">
                <span class="dmart-tag"><i class="fa-solid fa-star text-theme-1 text-xs"></i> Featured Products</span>
                <h2 class="dmart-section-title mt-5">Our Featured Products</h2>
            </div>
            <hr class="border-border-1 my-7" />
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2 mb-8">
                <div class="flex items-center flex-wrap gap-2.5">
                    <button v-for="tab in featuredCategoryTabs" :key="tab.slug" class="dmart-btn-outline" :class="{ active: activeFeaturedTab === tab.slug }" @click="activeFeaturedTab = tab.slug">{{ tab.name }}</button>
                </div>
                <Link href="/shop" class="dmart-btn-secondary">View All</Link>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-7">
                <ProductCard v-for="product in filteredFeaturedProducts.slice(0, 8)" :key="'fp-' + product.id" :product="product" variant="default" />
            </div>
        </section>

        <!-- ===== OFFER PRODUCTS ===== -->
        <section class="dmart-content-container my-10">
            <div class="grid md:grid-cols-2 2xl:grid-cols-5 gap-[30px]">
                <div class="2xl:col-span-2 rounded-[20px] px-10 pb-[60px] flex items-center justify-center flex-col" :style="{ backgroundColor: offerCard1.bgColor }">
                    <div class="text-center my-8"><img loading="lazy" :src="offerCard1.image" alt="Offer" class="max-h-[200px] object-contain mx-auto" /></div>
                    <div class="flex items-center justify-between w-full">
                        <div>
                            <span class="text-theme-1 uppercase font-semibold text-sm">{{ offerCard1.badge }}</span>
                            <h3 class="text-2xl font-bold text-title mt-1">{{ offerCard1.heading }}</h3>
                        </div>
                        <Link :href="offerCard1.buttonUrl" class="dmart-btn dmart-btn-primary">{{ offerCard1.buttonText }}</Link>
                    </div>
                </div>
                <div class="2xl:col-span-3 rounded-[20px] px-10 py-[60px] flex justify-center items-center flex-col" :style="{ backgroundColor: offerCard2.bgColor }">
                    <div class="text-center mb-8"><img loading="lazy" :src="offerCard2.image" alt="Offer" class="max-h-[200px] object-contain mx-auto" /></div>
                    <div class="flex items-center w-full justify-between mt-auto">
                        <div>
                            <span class="text-theme-1 uppercase font-semibold text-sm">{{ offerCard2.badge }}</span>
                            <h3 class="text-2xl font-bold text-title mt-1">{{ offerCard2.heading }}</h3>
                        </div>
                        <Link :href="offerCard2.buttonUrl" class="dmart-btn dmart-btn-dark">{{ offerCard2.buttonText }}</Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== HOT SELL ===== -->
        <section class="bg-bg-2 dmart-section-padding" v-if="featuredProducts?.length">
            <div class="dmart-content-container">
                <div class="grid gap-y-6 mb-8 md:grid-cols-2 items-center">
                    <div>
                        <span class="dmart-tag mb-3"><i class="fa-solid fa-fire-flame-curved text-theme-1 text-xs"></i> Hot Sell</span>
                        <h2 class="dmart-section-title">Products Of The Week</h2>
                    </div>
                    <div class="flex items-center md:justify-end">
                        <Link href="/shop" class="dmart-btn border-2 border-border-1 bg-transparent text-title rounded-lg hover:bg-title hover:text-white hover:border-title">View All</Link>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <ProductCard v-for="product in featuredProducts.slice(0, 4)" :key="'hot-' + product.id" :product="product" variant="default" />
                </div>
            </div>
        </section>

        <!-- ===== TESTIMONIALS ===== -->
        <section class="dmart-section-padding">
            <div class="dmart-content-container">
                <div class="text-center mb-8">
                    <span class="dmart-tag mb-3 inline-flex"><i class="fa-solid fa-quote-left text-theme-1 text-xs"></i> Testimonial</span>
                    <h2 class="dmart-section-title">What Our Clients Say</h2>
                </div>
                <Swiper :modules="swiperModules" :slides-per-view="1" :space-between="30" :autoplay="{ delay: 5000, disableOnInteraction: false }" :pagination="{ clickable: true }" :breakpoints="{ 768: { slidesPerView: 2 }, 1280: { slidesPerView: 3 } }" class="pb-12">
                    <SwiperSlide v-for="(t, ti) in testimonials" :key="ti">
                        <div class="dmart-testimonial-card h-full">
                            <i class="fa-solid fa-quote-right quote-icon"></i>
                            <p class="text-text leading-relaxed mb-6">{{ t.text }}</p>
                            <div class="flex items-center gap-4 mt-auto">
                                <div class="w-[60px] h-[60px] rounded-full bg-bg-6 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-user text-xl text-icon"></i>
                                </div>
                                <div>
                                    <h6 class="font-bold text-title text-base">{{ t.name }}</h6>
                                    <span class="text-sm text-text">{{ t.role }}</span>
                                    <div class="dmart-stars mt-1">
                                        <template v-for="s in 5" :key="s">
                                            <i :class="s <= t.rating ? 'fa-solid fa-star text-rating' : 'fa-regular fa-star text-[#CFCFCF]'" class="text-sm"></i>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </SwiperSlide>
                </Swiper>
            </div>
        </section>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.4s ease; }
.fade-enter-from { opacity: 0; transform: translateY(10px); }
.fade-leave-to { opacity: 0; transform: translateY(-10px); }
</style>