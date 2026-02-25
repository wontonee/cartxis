<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useStorefrontMenu } from '@/composables/useStorefrontMenu';
import { computed, ref } from 'vue';
import axios from 'axios';

interface Props {
    theme?: any;
    siteConfig?: { name: string; url: string; description: string };
}

const props = withDefaults(defineProps<Props>(), {
    theme: null,
    siteConfig: () => ({ name: 'Cartxis', url: '/', description: 'E-commerce Platform' }),
});

const currentYear = new Date().getFullYear();
const { menus, loading, getMenuUrl } = useStorefrontMenu();

const footerSections = computed(() => {
    if (!menus.value.footer || menus.value.footer.length === 0) return [];
    return menus.value.footer.filter((item: any) => item.children && item.children.length > 0);
});

const page = usePage();

// Contact & social from dedicated Inertia props (editable in admin Theme Settings)
const contact = computed(() => (page.props as any).contactInfo ?? {
    phone: '+208-555-0112',
    email: 'example@gmail.com',
    hours: 'Sunday - Fri: 9 AM - 6 PM',
    address: '4517 Washington Ave.',
});

const social = computed(() => (page.props as any).socialLinks ?? {
    facebook: '#',
    twitter: '#',
    instagram: '#',
    linkedin: '#',
});

const footerNewsletter = computed(() => (page.props as any).footerNewsletter ?? {
    heading: 'Newsletter',
    description: 'Sign up to searing weekly newsletter to get the latest updates.',
    placeholder: 'Enter Email Address',
    button_text: 'Subscribe',
});

const newsletterEmail = ref('');
const newsletterLoading = ref(false);
const newsletterMessage = ref('');
const newsletterError = ref(false);

const submitNewsletter = async () => {
    if (newsletterLoading.value) return;

    const email = newsletterEmail.value.trim();
    if (!email) {
        newsletterError.value = true;
        newsletterMessage.value = 'Please enter your email address.';
        return;
    }

    newsletterLoading.value = true;
    newsletterMessage.value = '';
    newsletterError.value = false;

    try {
        const { data } = await axios.post('/newsletter/subscribe', { email });
        newsletterError.value = false;
        newsletterMessage.value = data?.message || 'Successfully subscribed!';
        newsletterEmail.value = '';
    } catch (error: any) {
        newsletterError.value = true;
        newsletterMessage.value = error?.response?.data?.message || 'Subscription failed. Please try again.';
    } finally {
        newsletterLoading.value = false;
    }
};
</script>

<template>
    <footer class="footer-section bg-title">
        <!-- Contact Info Bar -->
        <div class="dmart-content-container">
            <div class="dmart-contact-bar">
                <div class="dmart-contact-item">
                    <div class="icon"><i class="fa-solid fa-phone-volume"></i></div>
                    <div class="content">
                        <p>Call Us 7/24</p>
                        <h3><a :href="'tel:' + contact.phone">{{ contact.phone }}</a></h3>
                    </div>
                </div>
                <div class="dmart-contact-item">
                    <div class="icon"><i class="fa-solid fa-envelope"></i></div>
                    <div class="content">
                        <p>Make a Quote</p>
                        <h3><a :href="'mailto:' + contact.email">{{ contact.email }}</a></h3>
                    </div>
                </div>
                <div class="dmart-contact-item">
                    <div class="icon"><i class="fa-solid fa-headset"></i></div>
                    <div class="content">
                        <p>Opening Hour</p>
                        <h3>{{ contact.hours }}</h3>
                    </div>
                </div>
                <div class="dmart-contact-item">
                    <div class="icon"><i class="fa-solid fa-street-view"></i></div>
                    <div class="content">
                        <p>Location</p>
                        <h3>{{ contact.address }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Widgets -->
        <div class="dmart-footer-widgets">
            <div class="dmart-content-container">
                <div class="grid gap-5 xl:grid-cols-4 lg:grid-cols-3 md:grid-cols-2">
                    <!-- Logo + Description + Social -->
                    <div class="dmart-footer-widget">
                        <div class="dmart-widget-head">
                            <Link href="/">
                                <span class="text-2xl font-bold text-white font-title">
                                    {{ siteConfig?.name || 'Cartxis' }}
                                </span>
                            </Link>
                        </div>
                        <div class="dmart-footer-content">
                            <p>{{ siteConfig?.description || 'Phasellus ultricies aliquam volutpat ullamcorper laoreet neque, a lacinia curabitur lacinia mollis' }}</p>
                            <div class="dmart-footer-social">
                                <a v-if="social.facebook" :href="social.facebook"><i class="fab fa-facebook-f"></i></a>
                                <a v-if="social.twitter" :href="social.twitter"><i class="fab fa-twitter"></i></a>
                                <a v-if="social.instagram" :href="social.instagram"><i class="fab fa-instagram"></i></a>
                                <a v-if="social.linkedin" :href="social.linkedin"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Footer Menus -->
                    <template v-if="!loading && footerSections.length > 0">
                        <div v-for="section in footerSections.slice(0, 2)" :key="section.id" class="lg:pl-[3rem]">
                            <div class="dmart-footer-widget">
                                <div class="dmart-widget-head">
                                    <h3>{{ section.title }}</h3>
                                </div>
                                <ul class="dmart-footer-list">
                                    <li v-for="child in section.children" :key="child.id">
                                        <Link :href="getMenuUrl(child)">
                                            <i class="fa-solid fa-chevrons-right"></i>
                                            {{ child.title }}
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="lg:pl-[3rem]">
                            <div class="dmart-footer-widget">
                                <div class="dmart-widget-head">
                                    <h3>Costumers Support</h3>
                                </div>
                                <ul class="dmart-footer-list">
                                    <li><Link href="/products"><i class="fa-solid fa-chevrons-right"></i> Store List</Link></li>
                                    <li><Link href="/contact-us"><i class="fa-solid fa-chevrons-right"></i> Opening Hours</Link></li>
                                    <li><Link href="/contact-us"><i class="fa-solid fa-chevrons-right"></i> Contact Us</Link></li>
                                    <li><Link href="/privacy-policy"><i class="fa-solid fa-chevrons-right"></i> Return Policy</Link></li>
                                </ul>
                            </div>
                        </div>
                        <div class="lg:pl-[3rem]">
                            <div class="dmart-footer-widget">
                                <div class="dmart-widget-head">
                                    <h3>Quick Links</h3>
                                </div>
                                <ul class="dmart-footer-list">
                                    <li><Link href="/about-us"><i class="fa-solid fa-chevrons-right"></i> About Us</Link></li>
                                    <li><Link href="/testimonial"><i class="fa-solid fa-chevrons-right"></i> Testimonial</Link></li>
                                    <li><Link href="/faqs"><i class="fa-solid fa-chevrons-right"></i> Faq</Link></li>
                                    <li><Link href="/blog"><i class="fa-solid fa-chevrons-right"></i> Blog</Link></li>
                                </ul>
                            </div>
                        </div>
                    </template>

                    <!-- Newsletter -->
                    <div class="dmart-footer-widget">
                        <div class="dmart-widget-head">
                            <h3>{{ footerNewsletter.heading }}</h3>
                        </div>
                        <div class="dmart-footer-content">
                            <p>{{ footerNewsletter.description }}</p>
                            <form @submit.prevent="submitNewsletter" class="dmart-footer-input">
                                <input v-model="newsletterEmail" type="email" :placeholder="footerNewsletter.placeholder" :disabled="newsletterLoading" />
                                <button type="submit" class="newsletter-btn" :disabled="newsletterLoading" :aria-label="footerNewsletter.button_text">
                                    <i class="fa-regular fa-paper-plane"></i>
                                </button>
                            </form>
                            <p
                                v-if="newsletterMessage"
                                class="mt-3 text-sm"
                                :class="newsletterError ? 'text-red-300' : 'text-green-300'"
                            >
                                {{ newsletterMessage }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="dmart-footer-bottom">
            <div class="dmart-content-container">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <p>
                        &copy; All Copyright {{ currentYear }} by <Link href="/">{{ siteConfig?.name || 'Cartxis' }}</Link>
                    </p>
                    <ul class="dmart-credit-cards">
                        <li><a href="#"><img loading="lazy" src="/images/footer/visa-logo.png" alt="Visa" /></a></li>
                        <li><a href="#"><img loading="lazy" src="/images/footer/mastercard-logo.png" alt="Mastercard" /></a></li>
                        <li><a href="#"><img loading="lazy" src="/images/footer/payoneer-logo.png" alt="Payoneer" /></a></li>
                        <li><a href="#"><img loading="lazy" src="/images/footer/affirm-logo.png" alt="Affirm" /></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
</template>
