<?php

declare(strict_types=1);

namespace Cartxis\CMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\CMS\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'url_key' => 'about-us',
                'content' => '<h1>About Us</h1><p>Welcome to our store! We are committed to providing the best products and services to our customers.</p><p>Founded in 2025, we have grown to become one of the leading e-commerce platforms in the region.</p>',
                'meta_title' => 'About Us - Learn More About Our Company',
                'meta_description' => 'Learn more about our company, our mission, and our commitment to excellence.',
                'meta_keywords' => 'about us, company, mission, values',
                'status' => 'published',
            ],
            [
                'title' => 'Terms and Conditions',
                'url_key' => 'terms-and-conditions',
                'content' => '<h1>Terms and Conditions</h1><h2>1. Introduction</h2><p>These terms and conditions govern your use of our website and services.</p><h2>2. Acceptance of Terms</h2><p>By accessing and using this website, you accept and agree to be bound by these terms.</p><h2>3. User Accounts</h2><p>You are responsible for maintaining the confidentiality of your account information.</p>',
                'meta_title' => 'Terms and Conditions',
                'meta_description' => 'Read our terms and conditions for using our website and services.',
                'meta_keywords' => 'terms, conditions, legal, agreement',
                'status' => 'published',
            ],
            [
                'title' => 'Privacy Policy',
                'url_key' => 'privacy-policy',
                'content' => '<h1>Privacy Policy</h1><h2>Information We Collect</h2><p>We collect information that you provide directly to us, including name, email address, and payment information.</p><h2>How We Use Your Information</h2><p>We use the information we collect to process orders, communicate with you, and improve our services.</p><h2>Data Security</h2><p>We implement appropriate security measures to protect your personal information.</p>',
                'meta_title' => 'Privacy Policy',
                'meta_description' => 'Our privacy policy explains how we collect, use, and protect your personal information.',
                'meta_keywords' => 'privacy, policy, data protection, security',
                'status' => 'published',
            ],
            [
                'title' => 'Contact Us',
                'url_key' => 'contact-us',
                'content' => '<h1>Contact Us</h1><p>We would love to hear from you! Please reach out to us using the information below.</p><h2>Email</h2><p>support@cartxis.com</p><h2>Phone</h2><p>+1 (555) 123-4567</p><h2>Address</h2><p>123 Commerce Street<br>Tech City, TC 12345</p>',
                'meta_title' => 'Contact Us',
                'meta_description' => 'Get in touch with us. We are here to help!',
                'meta_keywords' => 'contact, support, help, customer service',
                'status' => 'published',
            ],
            [
                'title' => 'Shipping Information',
                'url_key' => 'shipping-information',
                'content' => '<h1>Shipping Information</h1><h2>Shipping Methods</h2><p>We offer various shipping methods to suit your needs.</p><h2>Delivery Times</h2><p>Standard shipping: 5-7 business days<br>Express shipping: 2-3 business days<br>Overnight shipping: Next business day</p><h2>Shipping Costs</h2><p>Shipping costs are calculated based on the weight and destination of your order.</p>',
                'meta_title' => 'Shipping Information',
                'meta_description' => 'Learn about our shipping methods, delivery times, and costs.',
                'meta_keywords' => 'shipping, delivery, freight, logistics',
                'status' => 'published',
            ],
            [
                'title' => 'Return Policy',
                'url_key' => 'return-policy',
                'content' => '<h1>Return Policy</h1><h2>30-Day Return Window</h2><p>You may return most items within 30 days of delivery for a full refund.</p><h2>Return Conditions</h2><p>Items must be unused, in original packaging, and in resalable condition.</p><h2>How to Return</h2><p>Contact our customer service team to initiate a return. We will provide you with a prepaid return label.</p>',
                'meta_title' => 'Return Policy',
                'meta_description' => 'Our return policy allows you to return items within 30 days for a full refund.',
                'meta_keywords' => 'returns, refunds, exchange, warranty',
                'status' => 'published',
            ],
            [
                'title' => 'FAQ',
                'url_key' => 'faq',
                'content' => '<h1>Frequently Asked Questions</h1><h2>How do I place an order?</h2><p>Browse our products, add items to your cart, and proceed to checkout.</p><h2>What payment methods do you accept?</h2><p>We accept credit cards, debit cards, and PayPal.</p><h2>How can I track my order?</h2><p>You will receive a tracking number via email once your order ships.</p>',
                'meta_title' => 'Frequently Asked Questions',
                'meta_description' => 'Find answers to common questions about our products and services.',
                'meta_keywords' => 'faq, questions, answers, help',
                'status' => 'published',
            ],
        ];

        foreach ($pages as $pageData) {
            Page::firstOrCreate(
                ['url_key' => $pageData['url_key']],
                $pageData
            );
        }
    }
}
