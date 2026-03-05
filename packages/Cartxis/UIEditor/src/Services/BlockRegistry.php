<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Services;

class BlockRegistry
{
    /** @var array<string, array<string, mixed>> */
    private array $blocks = [];

    /**
     * Register all built-in block type definitions.
     */
    public function registerDefaults(): void
    {
        // ── Layout ────────────────────────────────────────────────────────────
        $this->register([
            'type'     => 'spacer',
            'label'    => 'Spacer',
            'icon'     => 'arrow-down-up',
            'category' => 'layout',
            'defaults' => ['height' => 40],
        ]);

        $this->register([
            'type'     => 'divider',
            'label'    => 'Divider',
            'icon'     => 'minus',
            'category' => 'layout',
            'defaults' => ['style' => 'solid', 'color' => '#e5e7eb', 'width' => '100%'],
        ]);

        $this->register([
            'type'     => 'header',
            'label'    => 'Header / Nav Bar',
            'icon'     => 'navigation',
            'category' => 'layout',
            'defaults' => [
                'logo_type'          => 'text',
                'logo_text'          => 'My Store',
                'logo_image'         => null,
                'logo_url'           => '/',
                'menu_source'        => 'header',
                'show_search'        => true,
                'show_cart'          => true,
                'show_auth_buttons'  => true,
                'sticky'             => true,
                'background_color'   => '#ffffff',
                'text_color'         => '#111827',
                'accent_color'       => '#2563eb',
            ],
        ]);

        $this->register([
            'type'     => 'footer',
            'label'    => 'Footer',
            'icon'     => 'layout-panel-bottom',
            'category' => 'layout',
            'defaults' => [
                'menu_source'         => 'footer',
                'logo_type'           => 'text',
                'logo_text'           => 'My Store',
                'logo_image'          => null,
                'logo_url'            => '/',
                'tagline'             => '',
                'copyright'           => '',
                'show_social'         => true,
                'show_payment_icons'  => true,
                'social_links'        => [
                    'facebook'  => '',
                    'twitter'   => '',
                    'instagram' => '',
                    'youtube'   => '',
                    'linkedin'  => '',
                ],
                'background_color'   => '#111827',
                'text_color'         => '#9ca3af',
                'heading_color'      => '#ffffff',
                'accent_color'       => '#3b82f6',
                'border_color'       => '#1f2937',
            ],
        ]);

        // ── Content ───────────────────────────────────────────────────────────
        $this->register([
            'type'     => 'heading',
            'label'    => 'Heading',
            'icon'     => 'type',
            'category' => 'content',
            'defaults' => ['level' => 'h2', 'text' => 'Your Heading', 'align' => 'left', 'color' => '#111827'],
        ]);

        $this->register([
            'type'     => 'text',
            'label'    => 'Text',
            'icon'     => 'align-left',
            'category' => 'content',
            'defaults' => ['content' => '<p>Enter your text here.</p>'],
        ]);

        $this->register([
            'type'     => 'image',
            'label'    => 'Image',
            'icon'     => 'image',
            'category' => 'content',
            'defaults' => ['image' => null, 'alt' => '', 'caption' => '', 'link' => '', 'border_radius' => 0, 'width' => '100%'],
        ]);

        $this->register([
            'type'     => 'video',
            'label'    => 'Video',
            'icon'     => 'youtube',
            'category' => 'content',
            'defaults' => ['url' => '', 'autoplay' => false, 'controls' => true, 'ratio' => '16:9'],
        ]);

        $this->register([
            'type'     => 'button',
            'label'    => 'Button',
            'icon'     => 'mouse-pointer',
            'category' => 'content',
            'defaults' => ['label' => 'Click Here', 'url' => '#', 'variant' => 'primary', 'size' => 'md', 'align' => 'left'],
        ]);

        $this->register([
            'type'     => 'form',
            'label'    => 'Form',
            'icon'     => 'clipboard-list',
            'category' => 'content',
            'defaults' => [
                'title'           => 'Contact Us',
                'fields'          => [
                    ['id' => 'f1', 'type' => 'text',     'label' => 'Name',    'required' => true,  'placeholder' => 'Your name'],
                    ['id' => 'f2', 'type' => 'email',    'label' => 'Email',   'required' => true,  'placeholder' => 'your@email.com'],
                    ['id' => 'f3', 'type' => 'textarea', 'label' => 'Message', 'required' => true,  'placeholder' => 'Your message'],
                ],
                'submit_label'    => 'Send Message',
                'success_message' => 'Thank you! We will get back to you soon.',
            ],
        ]);

        $this->register([
            'type'     => 'table',
            'label'    => 'Table',
            'icon'     => 'table',
            'category' => 'content',
            'defaults' => ['headers' => ['Column 1', 'Column 2'], 'rows' => [['', '']], 'striped' => true, 'bordered' => true],
        ]);

        $this->register([
            'type'     => 'card',
            'label'    => 'Card',
            'icon'     => 'credit-card',
            'category' => 'content',
            'defaults' => ['image' => null, 'title' => 'Card Title', 'body' => 'Card description goes here.', 'footer' => '', 'badge' => '', 'link' => ''],
        ]);

        // ── Media ─────────────────────────────────────────────────────────────
        $this->register([
            'type'     => 'gallery',
            'label'    => 'Gallery',
            'icon'     => 'layout-grid',
            'category' => 'media',
            'defaults' => ['images' => [], 'layout' => 'grid', 'columns' => 3, 'gap' => 8],
        ]);

        // ── Commerce ──────────────────────────────────────────────────────────
        $this->register([
            'type'     => 'hero',
            'label'    => 'Hero / Banner',
            'icon'     => 'monitor',
            'category' => 'commerce',
            'defaults' => [
                'image'            => null,
                'video_url'        => '',
                'overlay_color'    => '#000000',
                'overlay_opacity'  => 40,
                'headline'         => 'Welcome to Our Store',
                'subheading'       => 'Discover our latest collection',
                'cta_text'         => 'Shop Now',
                'cta_url'          => '/products',
                'height'           => 500,
                'text_align'       => 'center',
            ],
        ]);

        $this->register([
            'type'     => 'slider',
            'label'    => 'Hero Slider',
            'icon'     => 'image',
            'category' => 'commerce',
            'defaults' => [
                'height'   => 540,
                'autoplay' => true,
                'interval' => 5000,
                'slides'   => [
                    [
                        'id'         => 's1',
                        'image'      => '',
                        'grad_from'  => '#1e3a5f',
                        'grad_to'    => '#0ea5e9',
                        'badge'      => 'New Arrivals',
                        'headline'   => 'Summer Collection 2026',
                        'subheading' => 'Discover amazing products at unbeatable prices.',
                        'cta_text'   => 'Shop Now',
                        'cta_url'    => '/products',
                        'text_align' => 'center',
                    ],
                ],
            ],
        ]);

        $this->register([
            'type'     => 'newsletter',
            'label'    => 'Newsletter',
            'icon'     => 'mail',
            'category' => 'commerce',
            'defaults' => [
                'bg_color'    => '#0f172a',
                'layout'      => 'split',
                'title'       => 'Join Thousands of Happy Shoppers',
                'subtitle'    => 'Subscribe and get <strong>10% off</strong> your first order, plus exclusive deals and new arrivals straight to your inbox.',
                'cta_text'    => 'Subscribe Free',
                'success_msg' => "You're in! Check your inbox for your discount code.",
            ],
        ]);

        $this->register([
            'type'     => 'products_grid',
            'label'    => 'Products Grid',
            'icon'     => 'shopping-bag',
            'category' => 'commerce',
            'defaults' => ['product_ids' => [], 'columns' => 4, 'show_price' => true, 'show_cart' => true, 'limit' => 8],
        ]);

        $this->register([
            'type'     => 'testimonials',
            'label'    => 'Testimonials',
            'icon'     => 'message-square',
            'category' => 'commerce',
            'defaults' => [
                'items' => [
                    ['author' => 'Jane Doe', 'avatar' => null, 'text' => 'Great product!', 'rating' => 5],
                ],
            ],
        ]);

        // ── New Content Blocks ──────────────────────────────────────────────────────────
        $this->register([            'type'     => 'html',
            'label'    => 'HTML',
            'icon'     => 'code',
            'category' => 'content',
            'defaults' => [
                'content' => '<p style="color: #3b82f6; font-weight: bold;">Hello from HTML block!</p>',
            ],
        ]);

        $this->register([
            'type'     => 'code',
            'label'    => 'Code',
            'icon'     => 'file-code',
            'category' => 'content',
            'defaults' => [
                'language' => 'html',
                'filename' => '',
                'code'     => '<div class="example">\n  <p>Hello, world!</p>\n</div>',
                'show_line_numbers' => true,
            ],
        ]);

        $this->register([            'type'     => 'accordion',
            'label'    => 'Accordion',
            'icon'     => 'chevrons-down',
            'category' => 'content',
            'defaults' => [
                'items' => [
                    ['title' => 'Question 1', 'content' => 'Answer to question 1.', 'open' => true],
                    ['title' => 'Question 2', 'content' => 'Answer to question 2.', 'open' => false],
                ],
                'allow_multiple' => false,
            ],
        ]);

        $this->register([
            'type'     => 'tabs',
            'label'    => 'Tabs',
            'icon'     => 'book-open',
            'category' => 'content',
            'defaults' => [
                'tabs' => [
                    ['label' => 'Tab 1', 'content' => '<p>Content for tab 1.</p>'],
                    ['label' => 'Tab 2', 'content' => '<p>Content for tab 2.</p>'],
                ],
                'active_tab' => 0,
            ],
        ]);

        $this->register([
            'type'     => 'icon_box',
            'label'    => 'Icon Box',
            'icon'     => 'zap',
            'category' => 'content',
            'defaults' => [
                'icon'        => 'star',
                'icon_color'  => '#3b82f6',
                'icon_size'   => 48,
                'title'       => 'Feature Title',
                'description' => 'A short description of this feature or service.',
                'align'       => 'center',
                'link'        => '',
            ],
        ]);

        $this->register([
            'type'     => 'counter',
            'label'    => 'Counter',
            'icon'     => 'hash',
            'category' => 'content',
            'defaults' => [
                'number' => 1000,
                'prefix' => '',
                'suffix' => '+',
                'label'  => 'Happy Customers',
                'duration' => 2000,
                'align'  => 'center',
                'color'  => '#3b82f6',
            ],
        ]);

        $this->register([
            'type'     => 'pricing',
            'label'    => 'Pricing',
            'icon'     => 'tag',
            'category' => 'commerce',
            'defaults' => [
                'title'       => 'Pro Plan',
                'price'       => '29',
                'currency'    => '$',
                'period'      => '/month',
                'description' => 'Perfect for growing teams.',
                'features'    => ['Feature one', 'Feature two', 'Feature three'],
                'cta_text'    => 'Get Started',
                'cta_url'     => '#',
                'highlight'   => false,
                'badge'       => '',
            ],
        ]);

        $this->register([
            'type'     => 'social_links',
            'label'    => 'Social Links',
            'icon'     => 'share-2',
            'category' => 'content',
            'defaults' => [
                'links' => [
                    ['platform' => 'facebook',  'url' => '#', 'visible' => true],
                    ['platform' => 'twitter',   'url' => '#', 'visible' => true],
                    ['platform' => 'instagram', 'url' => '#', 'visible' => true],
                    ['platform' => 'linkedin',  'url' => '#', 'visible' => true],
                ],
                'size'   => 'md',
                'align'  => 'center',
                'style'  => 'circle',
                'color'  => '#6b7280',
            ],
        ]);

        // ── Global Region reference block ────────────────────────────────────
        $this->register([
            'type'     => 'global_region',
            'label'    => 'Reusable Section',
            'icon'     => 'layout-template',
            'category' => 'layout',
            'defaults' => [
                'region_id'     => null,
                'region_name'   => '',
                'region_type'   => 'section',
                'region_status' => 'draft',
            ],
        ]);
    }

    /**
     * Register a single block type definition.
     *
     * @param array{type: string, label: string, icon: string, category: string, defaults: array<string, mixed>} $definition
     */
    public function register(array $definition): void
    {
        $this->blocks[$definition['type']] = $definition;
    }

    /**
     * Get all registered block type definitions.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->blocks;
    }

    /**
     * Get all definitions as a grouped array keyed by category (for the block palette).
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public function grouped(): array
    {
        $grouped = [];
        foreach ($this->blocks as $block) {
            $grouped[$block['category']][] = $block;
        }
        return $grouped;
    }

    /**
     * Get the defaults for a given block type.
     *
     * @return array<string, mixed>
     */
    public function defaultsFor(string $type): array
    {
        return $this->blocks[$type]['defaults'] ?? [];
    }

    /**
     * Check whether a block type is registered.
     */
    public function has(string $type): bool
    {
        return isset($this->blocks[$type]);
    }
}
