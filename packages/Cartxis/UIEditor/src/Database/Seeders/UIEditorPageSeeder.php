<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\CMS\Models\Page;
use Cartxis\UIEditor\Models\PageLayout;

class UIEditorPageSeeder extends Seeder
{
    /**
     * Seed published UIEditor layouts for all default CMS pages.
     * Idempotent — skips any page that already has a published layout.
     */
    public function run(): void
    {
        foreach ($this->pages() as $urlKey => $layoutData) {
            $page = Page::where('url_key', $urlKey)->first();

            if (! $page) {
                $this->command->warn("  ↳ Page [{$urlKey}] not found — skipping layout.");
                continue;
            }

            $exists = PageLayout::where('page_id', $page->id)
                ->where('status', PageLayout::STATUS_PUBLISHED)
                ->exists();

            if ($exists) {
                $this->command->info("  ↳ Published layout for [{$urlKey}] already exists — skipping.");
                continue;
            }

            // Remove any orphaned drafts before creating the published version
            PageLayout::where('page_id', $page->id)->delete();

            PageLayout::create([
                'page_type'    => PageLayout::TYPE_CMS_PAGE,
                'page_id'      => $page->id,
                'layout_data'  => $layoutData,
                'status'       => PageLayout::STATUS_PUBLISHED,
                'published_at' => now(),
                'created_by'   => null,
                'updated_by'   => null,
            ]);

            $this->command->info("  ✓ Layout seeded for [{$urlKey}]");
        }
    }

    // ─── Layout definitions ───────────────────────────────────────────────────

    private function pages(): array
    {
        return [
            'about-us'             => $this->aboutUs(),
            'careers'              => $this->careers(),
            'contact-us'           => $this->contactUs(),
            'help'                 => $this->helpCenter(),
            'shipping-and-returns' => $this->shippingAndReturns(),
            'faq'                  => $this->faq(),
            'privacy-policy'       => $this->privacyPolicy(),
            'terms-and-conditions' => $this->termsOfService(),
            'track-order'          => $this->trackOrders(),
        ];
    }

    // ─── Helper: build a standard page-hero section ──────────────────────────

    private function heroSection(string $id, string $title, string $subtitle, string $bgColor = '#1e40af'): array
    {
        return [
            'id'       => "sec_{$id}_hero",
            'type'     => 'section',
            'settings' => [
                'background_color' => $bgColor,
                'padding_top'      => 72,
                'padding_bottom'   => 72,
                'full_width'       => true,
            ],
            'columns' => [
                [
                    'id'       => "col_{$id}_hero",
                    'width'    => 12,
                    'settings' => ['padding' => 16, 'align' => 'center'],
                    'blocks'   => [
                        [
                            'id'       => "blk_{$id}_hero_title",
                            'type'     => 'heading',
                            'settings' => [
                                'level' => 'h1',
                                'text'  => $title,
                                'align' => 'center',
                                'color' => '#ffffff',
                            ],
                        ],
                        [
                            'id'       => "blk_{$id}_hero_sub",
                            'type'     => 'text',
                            'settings' => [
                                'content' => "<p style=\"text-align:center;color:rgba(255,255,255,0.80);font-size:1.125rem;margin-top:0.75rem\">{$subtitle}</p>",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ─── Helper: light content section ───────────────────────────────────────

    private function contentSection(string $id, array $blocks, string $bgColor = '#ffffff', int $paddingTop = 64, int $paddingBottom = 64): array
    {
        return [
            'id'       => "sec_{$id}",
            'type'     => 'section',
            'settings' => [
                'background_color' => $bgColor,
                'padding_top'      => $paddingTop,
                'padding_bottom'   => $paddingBottom,
                'full_width'       => false,
            ],
            'columns' => [
                [
                    'id'       => "col_{$id}",
                    'width'    => 12,
                    'settings' => ['padding' => 16, 'align' => 'left'],
                    'blocks'   => $blocks,
                ],
            ],
        ];
    }

    // ─── Helper: 3-column icon-box section ───────────────────────────────────

    private function iconBoxSection(string $id, array $boxes, string $bgColor = '#f9fafb'): array
    {
        $columns = array_map(function ($box, $i) use ($id) {
            return [
                'id'       => "col_{$id}_box{$i}",
                'width'    => 4,
                'settings' => ['padding' => 24, 'align' => 'center'],
                'blocks'   => [
                    [
                        'id'       => "blk_{$id}_box{$i}",
                        'type'     => 'icon_box',
                        'settings' => [
                            'icon'        => $box['icon'],
                            'title'       => $box['title'],
                            'description' => $box['description'],
                            'align'       => 'center',
                            'icon_color'  => '#1e40af',
                            'title_color' => '#111827',
                            'text_color'  => '#6b7280',
                        ],
                    ],
                ],
            ];
        }, $boxes, array_keys($boxes));

        return [
            'id'       => "sec_{$id}_boxes",
            'type'     => 'section',
            'settings' => [
                'background_color' => $bgColor,
                'padding_top'      => 64,
                'padding_bottom'   => 64,
                'full_width'       => false,
            ],
            'columns' => $columns,
        ];
    }

    // ─── Page layouts ─────────────────────────────────────────────────────────

    private function aboutUs(): array
    {
        return [
            'version'  => '1.0',
            'sections' => [
                $this->heroSection('about', 'About Us', 'We are committed to providing the best products and services to our customers.', '#1e3a5f'),
                $this->iconBoxSection('about', [
                    ['icon' => 'star', 'title' => 'Our Mission', 'description' => 'To deliver exceptional e-commerce experiences through quality products and outstanding customer service.'],
                    ['icon' => 'users', 'title' => 'Our Team', 'description' => 'A diverse group of passionate professionals united by a shared goal: making shopping easier and better.'],
                    ['icon' => 'shield-check', 'title' => 'Our Values', 'description' => 'Integrity, transparency, and customer-first thinking guide everything we do.'],
                ]),
                $this->contentSection('about_story', [
                    [
                        'id'       => 'blk_about_story_h',
                        'type'     => 'heading',
                        'settings' => ['level' => 'h2', 'text' => 'Our Story', 'align' => 'left', 'color' => '#111827'],
                    ],
                    [
                        'id'       => 'blk_about_story_t',
                        'type'     => 'text',
                        'settings' => ['content' => '<p style="color:#4b5563;line-height:1.8;font-size:1.0625rem">Founded in 2025, we set out with a simple idea: make online shopping as enjoyable and trustworthy as possible. From a small team with a big dream, we have grown into a platform that connects thousands of customers with the products they love — backed by reliable delivery, easy returns, and friendly support every step of the way.</p>'],
                    ],
                    [
                        'id'       => 'blk_about_story_cta',
                        'type'     => 'button',
                        'settings' => ['text' => 'Shop Now', 'url' => '/products', 'style' => 'primary', 'align' => 'left'],
                    ],
                ]),
            ],
        ];
    }

    private function careers(): array
    {
        return [
            'version'  => '1.0',
            'sections' => [
                $this->heroSection('careers', 'Work With Us', 'Help us shape the future of e-commerce. Great people, innovative products, real impact.', '#0f172a'),
                $this->iconBoxSection('careers', [
                    ['icon' => 'trending-up', 'title' => 'Grow Fast', 'description' => 'Accelerate your career with mentorship, learning budgets, and internal mobility.'],
                    ['icon' => 'heart', 'title' => 'Great Culture', 'description' => 'Collaborative, inclusive, and results-focused. We celebrate wins together.'],
                    ['icon' => 'globe', 'title' => 'Work Anywhere', 'description' => 'Fully remote-friendly roles with flexible hours and async-first communication.'],
                ]),
                $this->contentSection('careers_roles', [
                    [
                        'id'       => 'blk_careers_open_h',
                        'type'     => 'heading',
                        'settings' => ['level' => 'h2', 'text' => 'Open Positions', 'align' => 'left', 'color' => '#111827'],
                    ],
                    [
                        'id'       => 'blk_careers_open_t',
                        'type'     => 'text',
                        'settings' => ['content' => '<p style="color:#4b5563;line-height:1.8">We are always looking for talented engineers, designers, marketers and operations professionals. New roles are posted regularly — check back soon or send your CV to <a href="mailto:careers@cartxis.com" style="color:#1e40af">careers@cartxis.com</a> and we will keep you in mind for future openings.</p>'],
                    ],
                    [
                        'id'       => 'blk_careers_cta',
                        'type'     => 'button',
                        'settings' => ['text' => 'Send Your CV', 'url' => '/contact-us', 'style' => 'primary', 'align' => 'left'],
                    ],
                ]),
            ],
        ];
    }

    private function contactUs(): array
    {
        return [
            'version'  => '1.0',
            'sections' => [
                $this->heroSection('contact', 'Get In Touch', 'We would love to hear from you. Our support team is here to help.', '#1e40af'),
                $this->iconBoxSection('contact', [
                    ['icon' => 'mail', 'title' => 'Email Us', 'description' => 'support@cartxis.com — We reply within 24 hours on business days.'],
                    ['icon' => 'phone', 'title' => 'Call Us', 'description' => '+1 (555) 123-4567 — Available Mon–Fri, 9 AM to 6 PM EST.'],
                    ['icon' => 'map-pin', 'title' => 'Our Office', 'description' => '123 Commerce Street, Tech City, TC 12345'],
                ], '#ffffff'),
                $this->contentSection('contact_info', [
                    [
                        'id'       => 'blk_contact_faq_h',
                        'type'     => 'heading',
                        'settings' => ['level' => 'h2', 'text' => 'Frequently Asked Questions', 'align' => 'center', 'color' => '#111827'],
                    ],
                    [
                        'id'       => 'blk_contact_faq_t',
                        'type'     => 'text',
                        'settings' => ['content' => '<p style="text-align:center;color:#4b5563">Looking for quick answers? Browse our FAQ section before reaching out.</p>'],
                    ],
                    [
                        'id'       => 'blk_contact_faq_cta',
                        'type'     => 'button',
                        'settings' => ['text' => 'Visit FAQ', 'url' => '/faq', 'style' => 'outline', 'align' => 'center'],
                    ],
                ], '#f9fafb'),
            ],
        ];
    }

    private function helpCenter(): array
    {
        return [
            'version'  => '1.0',
            'sections' => [
                $this->heroSection('help', 'Help Center', 'Find answers, guides and support resources to help you succeed.', '#0369a1'),
                $this->iconBoxSection('help', [
                    ['icon' => 'user', 'title' => 'My Account', 'description' => 'Manage your profile, saved addresses, and notification preferences.'],
                    ['icon' => 'package', 'title' => 'Orders & Tracking', 'description' => 'View order history, track live shipments, and download invoices.'],
                    ['icon' => 'refresh-cw', 'title' => 'Returns & Refunds', 'description' => 'Understand our return window and how refunds are processed.'],
                    ['icon' => 'credit-card', 'title' => 'Payments & Billing', 'description' => 'Learn about accepted payment methods, failed payments, and invoices.'],
                    ['icon' => 'truck', 'title' => 'Shipping Info', 'description' => 'Estimated delivery times, shipping costs, and carriers we use.'],
                    ['icon' => 'message-circle', 'title' => 'Contact Support', 'description' => 'Can\'t find your answer? Our team is ready to help you directly.'],
                ]),
                $this->contentSection('help_cta', [
                    [
                        'id'       => 'blk_help_cta_h',
                        'type'     => 'heading',
                        'settings' => ['level' => 'h2', 'text' => 'Still need help?', 'align' => 'center', 'color' => '#111827'],
                    ],
                    [
                        'id'       => 'blk_help_cta_t',
                        'type'     => 'text',
                        'settings' => ['content' => '<p style="text-align:center;color:#4b5563">Our support team is available Mon–Fri, 9 AM to 6 PM EST. Response time is typically under 4 hours.</p>'],
                    ],
                    [
                        'id'       => 'blk_help_cta_btn',
                        'type'     => 'button',
                        'settings' => ['text' => 'Contact Support', 'url' => '/contact-us', 'style' => 'primary', 'align' => 'center'],
                    ],
                ], '#f0f9ff'),
            ],
        ];
    }

    private function shippingAndReturns(): array
    {
        return [
            'version'  => '1.0',
            'sections' => [
                $this->heroSection('shipping', 'Shipping & Returns', 'Fast, reliable delivery and hassle-free returns — that is our promise to you.', '#065f46'),
                $this->iconBoxSection('shipping', [
                    ['icon' => 'truck', 'title' => 'Standard Shipping', 'description' => '5–7 business days. Free on all orders over $50.'],
                    ['icon' => 'zap', 'title' => 'Express Shipping', 'description' => '2–3 business days. Flat rate of $12.99.'],
                    ['icon' => 'clock', 'title' => 'Overnight', 'description' => 'Next business day. Flat rate of $24.99. Order by 12 PM EST.'],
                ], '#f0fdf4'),
                $this->contentSection('returns', [
                    [
                        'id'       => 'blk_returns_h',
                        'type'     => 'heading',
                        'settings' => ['level' => 'h2', 'text' => 'Our Return Policy', 'align' => 'left', 'color' => '#111827'],
                    ],
                    [
                        'id'       => 'blk_returns_t',
                        'type'     => 'text',
                        'settings' => ['content' => '<ul style="color:#4b5563;line-height:2;padding-left:1.25rem;list-style:disc"><li><strong>30-day return window</strong> from the date of delivery.</li><li>Items must be <strong>unused</strong>, in original packaging, and in resalable condition.</li><li>Contact our support team to initiate a return — we provide a prepaid return label.</li><li>Refunds are processed within <strong>5–7 business days</strong> after we receive the item.</li><li>Sale items and personalised orders are non-refundable.</li></ul>'],
                    ],
                    [
                        'id'       => 'blk_returns_cta',
                        'type'     => 'button',
                        'settings' => ['text' => 'Start a Return', 'url' => '/contact-us', 'style' => 'primary', 'align' => 'left'],
                    ],
                ]),
            ],
        ];
    }

    private function faq(): array
    {
        return [
            'version'  => '1.0',
            'sections' => [
                $this->heroSection('faq', 'Frequently Asked Questions', 'Quick answers to the questions we get most often.', '#4338ca'),
                $this->contentSection('faq_ordering', [
                    [
                        'id'   => 'blk_faq_orders_h',
                        'type' => 'heading',
                        'settings' => ['level' => 'h2', 'text' => 'Ordering', 'align' => 'left', 'color' => '#111827'],
                    ],
                    [
                        'id'   => 'blk_faq_orders_t',
                        'type' => 'text',
                        'settings' => ['content' => '<h3 style="font-size:1rem;font-weight:600;color:#111827;margin-top:1.25rem">How do I place an order?</h3><p style="color:#4b5563;margin-top:0.25rem">Browse our products, add items to your cart, and proceed to checkout. You can check out as a guest or create an account for faster future purchases.</p><h3 style="font-size:1rem;font-weight:600;color:#111827;margin-top:1.25rem">Can I modify or cancel my order?</h3><p style="color:#4b5563;margin-top:0.25rem">Orders can be modified or cancelled within 1 hour of placement. Contact our support team immediately at support@cartxis.com.</p><h3 style="font-size:1rem;font-weight:600;color:#111827;margin-top:1.25rem">Do you offer bulk or wholesale pricing?</h3><p style="color:#4b5563;margin-top:0.25rem">Yes — contact us at support@cartxis.com for bulk pricing and custom quotes.</p>'],
                    ],
                ]),
                $this->contentSection('faq_payments', [
                    [
                        'id'   => 'blk_faq_pay_h',
                        'type' => 'heading',
                        'settings' => ['level' => 'h2', 'text' => 'Payments', 'align' => 'left', 'color' => '#111827'],
                    ],
                    [
                        'id'   => 'blk_faq_pay_t',
                        'type' => 'text',
                        'settings' => ['content' => '<h3 style="font-size:1rem;font-weight:600;color:#111827;margin-top:1.25rem">What payment methods do you accept?</h3><p style="color:#4b5563;margin-top:0.25rem">We accept Visa, Mastercard, American Express, PayPal, Stripe, and Razorpay.</p><h3 style="font-size:1rem;font-weight:600;color:#111827;margin-top:1.25rem">Is my payment information secure?</h3><p style="color:#4b5563;margin-top:0.25rem">Yes. All payments are processed through PCI-DSS compliant payment gateways. We never store your card details directly.</p>'],
                    ],
                ], '#f9fafb'),
                $this->contentSection('faq_shipping', [
                    [
                        'id'   => 'blk_faq_ship_h',
                        'type' => 'heading',
                        'settings' => ['level' => 'h2', 'text' => 'Shipping & Returns', 'align' => 'left', 'color' => '#111827'],
                    ],
                    [
                        'id'   => 'blk_faq_ship_t',
                        'type' => 'text',
                        'settings' => ['content' => '<h3 style="font-size:1rem;font-weight:600;color:#111827;margin-top:1.25rem">How can I track my order?</h3><p style="color:#4b5563;margin-top:0.25rem">You will receive a tracking number via email once your order ships. You can also track orders using the <a href="/checkout/track-order" style="color:#1e40af">Track Order</a> tool.</p><h3 style="font-size:1rem;font-weight:600;color:#111827;margin-top:1.25rem">What is your return policy?</h3><p style="color:#4b5563;margin-top:0.25rem">We offer a 30-day return window from the date of delivery. See our <a href="/shipping-and-returns" style="color:#1e40af">Shipping &amp; Returns</a> page for full details.</p>'],
                    ],
                ]),
            ],
        ];
    }

    private function privacyPolicy(): array
    {
        return [
            'version'  => '1.0',
            'sections' => [
                $this->heroSection('privacy', 'Privacy Policy', 'Last updated: March 2026. Your privacy matters to us.', '#374151'),
                $this->contentSection('privacy_content', [
                    [
                        'id'   => 'blk_privacy_text',
                        'type' => 'text',
                        'settings' => [
                            'content' => '
<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:0">1. Information We Collect</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">We collect information you provide directly to us, including name, email address, shipping address, and payment information when you place orders or create an account.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">2. How We Use Your Information</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">We use collected information to process orders, communicate with you about your purchases, send transactional emails, improve our services, and comply with legal obligations.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">3. Data Sharing</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">We do not sell your personal data. We share data with trusted service providers (payment processors, shipping carriers) strictly to fulfil your orders, and only as required by law.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">4. Cookies</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">We use cookies to keep your session active, remember cart contents, and analyse site usage. You can disable cookies in your browser, but some site features may not function correctly.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">5. Data Security</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">We implement industry-standard security measures including TLS encryption, access controls, and regular security audits to protect your personal information.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">6. Your Rights</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">You have the right to access, correct, or delete your personal data at any time. Contact us at <a href="mailto:privacy@cartxis.com" style="color:#1e40af">privacy@cartxis.com</a> to exercise these rights.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">7. Contact</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">For privacy-related enquiries, please contact our Data Protection Officer at <a href="mailto:privacy@cartxis.com" style="color:#1e40af">privacy@cartxis.com</a>.</p>
',
                        ],
                    ],
                ]),
            ],
        ];
    }

    private function termsOfService(): array
    {
        return [
            'version'  => '1.0',
            'sections' => [
                $this->heroSection('terms', 'Terms of Service', 'Please read these terms carefully before using our platform.', '#374151'),
                $this->contentSection('terms_content', [
                    [
                        'id'   => 'blk_terms_text',
                        'type' => 'text',
                        'settings' => [
                            'content' => '
<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:0">1. Acceptance of Terms</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">By accessing and using this website, you accept and agree to be bound by these Terms of Service and our Privacy Policy. If you do not agree, please discontinue use of the site.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">2. Use of the Platform</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">You agree to use this platform only for lawful purposes. You may not use this site in a way that damages the platform or interferes with other users\' access.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">3. User Accounts</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">You are responsible for maintaining the confidentiality of your account credentials and for all activity that occurs under your account. Notify us immediately of any unauthorised use.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">4. Orders & Payments</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">All orders are subject to product availability and acceptance. We reserve the right to refuse or cancel orders at our discretion. Prices are subject to change without notice.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">5. Intellectual Property</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">All content on this site — including text, graphics, logos, and software — is the property of Cartxis Commerce and is protected by applicable intellectual property laws.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">6. Limitation of Liability</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">To the maximum extent permitted by law, Cartxis Commerce shall not be liable for any indirect, incidental, or consequential damages arising from your use of the platform.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">7. Governing Law</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">These terms are governed by and construed in accordance with applicable laws. Any disputes shall be resolved through binding arbitration or in the courts of the relevant jurisdiction.</p>

<h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-top:2rem">8. Changes to Terms</h2>
<p style="color:#4b5563;line-height:1.8;margin-top:0.5rem">We reserve the right to update these terms at any time. Continued use of the platform after changes constitutes acceptance of the revised terms.</p>
',
                        ],
                    ],
                ]),
            ],
        ];
    }

    private function trackOrders(): array
    {
        return [
            'version'  => '1.0',
            'sections' => [
                $this->heroSection('track', 'Track Your Order', 'Enter your order number below to get real-time status updates on your shipment.', '#0c4a6e'),
                $this->iconBoxSection('track', [
                    ['icon' => 'package', 'title' => 'Order Placed', 'description' => 'Your order has been received and is being prepared for dispatch.'],
                    ['icon' => 'truck', 'title' => 'In Transit', 'description' => 'Your package is on its way. Track it using the order number below.'],
                    ['icon' => 'check-circle', 'title' => 'Delivered', 'description' => 'Your order has been delivered. Please check with your household if you were away.'],
                ], '#f0f9ff'),
            ],
        ];
    }
}
