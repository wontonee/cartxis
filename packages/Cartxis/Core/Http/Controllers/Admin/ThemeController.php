<?php

namespace Cartxis\Core\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Core\Services\ThemeService;
use Cartxis\Core\Models\Theme;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Cartxis\CMS\Models\Block;
use Cartxis\Product\Models\Product;
use Cartxis\Product\Models\Category;
use Cartxis\Product\Models\ProductImage;
use Cartxis\Product\Models\ProductReview;
use Cartxis\Product\Models\Attribute;
use Cartxis\Product\Models\AttributeOption;
use Cartxis\Product\Models\ProductAttributeValue;

class ThemeController extends Controller
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * Display list of all themes
     */
    public function index()
    {
        // Discover new themes from filesystem
        $this->themeService->discover();

        $themes = Theme::all()->map(function ($theme) {
            $config = $theme->getConfig();
            return [
                'id' => $theme->id,
                'name' => $theme->name,
                'slug' => $theme->slug,
                'description' => $theme->description,
                'version' => $theme->version,
                'author' => $theme->author,
                'screenshot' => $this->resolveThemeScreenshotUrl($theme),
                'is_active' => $theme->is_active,
                'is_default' => $theme->is_default,
                'exists' => $theme->exists(),
                'supports' => $config['supports'] ?? [],
            ];
        });

        return Inertia::render('Admin/Themes/Index', [
            'themes' => $themes,
        ]);
    }

    /**
     * Unified Appearance page — shows active theme settings in a tabbed layout.
     */
    public function appearance()
    {
        $theme = Theme::where('is_active', true)->first();

        if (!$theme) {
            return redirect()->route('admin.themes.index')
                ->with('error', 'No active theme found. Please activate a theme first.');
        }

        if (!$theme->exists()) {
            return redirect()->route('admin.themes.index')
                ->with('error', 'Active theme files are missing.');
        }

        $schema = $this->themeService->getSettingsSchema($theme);
        $currentSettings = $this->flattenSettings($theme->settings ?? []);
        $hasThemeData = file_exists($theme->getPath() . '/data/theme-data.json');
        $hasProductData = false;
        if ($hasThemeData) {
            $themeData = json_decode(file_get_contents($theme->getPath() . '/data/theme-data.json'), true);
            $hasProductData = !empty($themeData['categories']) || !empty($themeData['products']);
        }

        return Inertia::render('Admin/Appearance/Index', [
            'theme' => [
                'id' => $theme->id,
                'name' => $theme->name,
                'slug' => $theme->slug,
                'description' => $theme->description,
                'version' => $theme->version,
                'author' => $theme->author ?? 'Unknown',
                'screenshot' => $this->resolveThemeScreenshotUrl($theme),
                'is_active' => $theme->is_active,
            ],
            'schema' => $schema,
            'settings' => $currentSettings,
            'hasThemeData' => $hasThemeData,
            'hasProductData' => $hasProductData,
        ]);
    }

    /**
     * Activate a theme
     */
    public function activate(Request $request, string $slug)
    {
        try {
            $theme = Theme::where('slug', $slug)->firstOrFail();

            if (!$theme->exists()) {
                return back()->with('error', 'Theme files not found. Please reinstall the theme.');
            }

            $this->themeService->activate($slug);

            return back()->with('success', "Theme '{$theme->name}' activated successfully!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to activate theme: ' . $e->getMessage());
        }
    }

    /**
     * Redirect to the active theme's settings page.
     * Used by the admin sidebar menu so it always opens the correct theme.
     */
    public function activeSettings()
    {
        return redirect()->route('admin.appearance.index');
    }

    /**
     * Show theme settings page
     */
    public function settings(string $slug)
    {
        return redirect()->route('admin.appearance.index');
    }

    /**
     * Update theme settings
     */
    public function updateSettings(Request $request, string $slug)
    {
        $theme = Theme::where('slug', $slug)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $theme->settings = $request->settings;
            $theme->save();

            // Clear theme cache
            cache()->forget('active_theme');

            return back()->with('success', 'Theme settings updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Delete a theme
     */
    public function destroy(string $slug)
    {
        try {
            $theme = Theme::where('slug', $slug)->firstOrFail();

            // Prevent deleting active or default theme
            if ($theme->is_active) {
                return back()->with('error', 'Cannot delete the active theme. Please activate another theme first.');
            }

            if ($theme->is_default) {
                return back()->with('error', 'Cannot delete the default theme.');
            }

            $this->themeService->delete($slug);

            return back()->with('success', "Theme '{$theme->name}' deleted successfully!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete theme: ' . $e->getMessage());
        }
    }

    /**
     * Upload and install a new theme
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme' => 'required|file|mimes:zip|max:51200', // 50MB max
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $file = $request->file('theme');
            $path = $file->storeAs('themes', $file->getClientOriginalName(), 'local');
            $fullPath = storage_path('app/' . $path);

            $installedSlug = $this->themeService->install($fullPath);

            // Clean up uploaded zip
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            if (! $installedSlug) {
                return back()->with('error', 'Theme upload failed: invalid package structure. Ensure the zip contains a valid theme with theme.json.');
            }

            return back()->with('success', "Theme installed successfully ({$installedSlug})!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to install theme: ' . $e->getMessage());
        }
    }

    /**
     * Import theme data (CMS blocks, menus, settings) from the theme's data/theme-data.json file.
     */
    public function importData(Request $request, string $slug)
    {
        $theme = Theme::where('slug', $slug)->firstOrFail();

        $dataPath = $theme->getPath() . '/data/theme-data.json';
        if (!file_exists($dataPath)) {
            return back()->with('error', 'No theme-data.json found for this theme.');
        }

        $data = json_decode(file_get_contents($dataPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'Invalid JSON in theme-data.json: ' . json_last_error_msg());
        }

        $fresh = $request->boolean('fresh', false);

        try {
            // Fresh import — clean existing data first
            if ($fresh) {
                $identifiers = collect($data['blocks'] ?? [])->pluck('identifier')->toArray();
                if (!empty($identifiers)) {
                    Block::withTrashed()->whereIn('identifier', $identifiers)->forceDelete();
                }
                DB::table('menu_items')->where('key', 'like', "{$slug}-%")->delete();
                $theme->update(['settings' => []]);
            }

            // Import CMS Blocks
            $blockCount = 0;
            foreach ($data['blocks'] ?? [] as $blockData) {
                Block::withTrashed()->updateOrCreate(
                    ['identifier' => $blockData['identifier']],
                    [
                        'title'      => $blockData['title'],
                        'type'       => $blockData['type'] ?? 'html',
                        'content'    => is_array($blockData['content'])
                            ? json_encode($blockData['content'])
                            : $blockData['content'],
                        'status'     => $blockData['status'] ?? 'active',
                        'deleted_at' => null,
                    ]
                );
                $blockCount++;
            }

            // Import Menus
            $menuCount = 0;
            foreach ($data['menus'] ?? [] as $menuType => $items) {
                foreach ($items as $item) {
                    $menuCount += $this->createMenuItem($item, $menuType, $slug, null);
                }
            }

            // Import Theme Settings
            if (!empty($data['settings'])) {
                $existingSettings = $theme->settings ?? [];
                $mergedSettings = array_replace_recursive($existingSettings, $data['settings']);
                $theme->update(['settings' => $mergedSettings]);
            }

            // Import Products & Categories (only if requested)
            $productResults = ['categories' => 0, 'products' => 0, 'reviews' => 0, 'images' => 0, 'attributes' => 0];
            if ($request->boolean('include_products', false)) {
                $productResults = $this->importProductsAndCategories($data, $fresh);
            }

            // Clear theme cache so frontend picks up new data
            cache()->forget('active_theme');

            $parts = ["{$blockCount} blocks", "{$menuCount} menu items", "settings"];
            if ($productResults['categories'] > 0 || $productResults['products'] > 0) {
                $parts[] = "{$productResults['categories']} categories";
                $parts[] = "{$productResults['products']} products";
            }
            if ($productResults['images'] > 0) {
                $parts[] = "{$productResults['images']} images";
            }
            if ($productResults['reviews'] > 0) {
                $parts[] = "{$productResults['reviews']} reviews";
            }
            if ($productResults['attributes'] > 0) {
                $parts[] = "{$productResults['attributes']} attributes";
            }

            return back()->with('success', 'Theme data imported successfully! ' . implode(', ', $parts) . ' have been updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to import theme data: ' . $e->getMessage());
        }
    }

    /**
     * Recursively create a menu item and its children for theme import.
     */
    protected function createMenuItem(array $item, string $menuType, string $themeSlug, ?int $parentId): int
    {
        $count = 0;
        $key = "{$themeSlug}-{$menuType}-" . Str::slug($item['title']);

        $menuData = [
            'title'     => $item['title'],
            'key'       => $key,
            'icon'      => $item['icon'] ?? null,
            'route'     => $item['route'] ?? null,
            'url'       => $item['url'] ?? null,
            'location'  => 'storefront',
            'menu_type' => $menuType,
            'parent_id' => $parentId,
            'order'     => $item['order'] ?? 0,
            'active'    => $item['active'] ?? true,
            'meta'      => isset($item['meta']) ? json_encode($item['meta']) : null,
        ];

        $existing = DB::table('menu_items')->where('key', $key)->first();

        if ($existing) {
            DB::table('menu_items')->where('id', $existing->id)->update(
                array_merge($menuData, ['updated_at' => now()])
            );
            $itemId = $existing->id;
        } else {
            $itemId = DB::table('menu_items')->insertGetId(
                array_merge($menuData, ['created_at' => now(), 'updated_at' => now()])
            );
        }
        $count++;

        if (!empty($item['children'])) {
            foreach ($item['children'] as $child) {
                $count += $this->createMenuItem($child, $menuType, $themeSlug, $itemId);
            }
        }

        return $count;
    }

    /**
     * Upload a screenshot/preview image for the theme.
     */
    public function uploadScreenshot(Request $request, string $slug)
    {
        $theme = Theme::where('slug', $slug)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'screenshot' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $file = $request->file('screenshot');
            $filename = 'screenshot.' . $file->getClientOriginalExtension();

            // Save to the theme's public directory
            $themePath = public_path("themes/{$slug}");
            if (!is_dir($themePath)) {
                mkdir($themePath, 0755, true);
            }

            $file->move($themePath, $filename);

            // Update the DB record
            $theme->update(['screenshot' => $filename]);

            return back()->with('success', 'Theme screenshot updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload screenshot: ' . $e->getMessage());
        }
    }

    /**
     * Import products and categories from theme data.
     */
    protected function importProductsAndCategories(array $data, bool $fresh): array
    {
        $categoryCount = 0;
        $productCount = 0;
        $reviewCount = 0;
        $imageCount = 0;
        $attributeCount = 0;

        // Fresh import — wipe existing products, categories, and related data
        if ($fresh) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('product_attribute_values')->truncate();
            DB::table('product_images')->truncate();
            DB::table('product_reviews')->truncate();
            DB::table('category_product')->truncate();
            Product::withTrashed()->forceDelete();
            Category::withTrashed()->forceDelete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // ── Import Attributes ──
        $attributeMap = []; // code => Attribute model
        $optionMap = [];    // "code:value" => AttributeOption model
        foreach ($data['attributes'] ?? [] as $attrData) {
            $options = $attrData['options'] ?? [];
            unset($attrData['options']);

            $attribute = Attribute::updateOrCreate(
                ['code' => $attrData['code']],
                collect($attrData)->except('code')->toArray()
            );
            $attributeMap[$attribute->code] = $attribute;
            $attributeCount++;

            foreach ($options as $optData) {
                // Auto-generate swatch_value for color attributes if not provided
                if ($attrData['code'] === 'color' && empty($optData['swatch_value'])) {
                    $colorMap = [
                        'black' => '#000000', 'white' => '#FFFFFF', 'silver' => '#C0C0C0',
                        'blue' => '#2563EB', 'gold' => '#D4AF37', 'space-gray' => '#52565A',
                        'midnight' => '#1C1C2E', 'starlight' => '#F5E6D3', 'red' => '#EF4444',
                        'green' => '#22C55E', 'pink' => '#EC4899', 'purple' => '#8B5CF6',
                        'orange' => '#F97316', 'yellow' => '#EAB308', 'navy' => '#1E3A5F',
                        'gray' => '#6B7280', 'grey' => '#6B7280', 'natural-titanium' => '#8A8578',
                        'blue-titanium' => '#394E6A', 'white-titanium' => '#D4D2CA',
                        'black-titanium' => '#3A3A3C', 'cream' => '#FFFDD0',
                    ];
                    $optData['swatch_value'] = $colorMap[$optData['value']] ?? null;
                }

                $option = AttributeOption::updateOrCreate(
                    ['attribute_id' => $attribute->id, 'value' => $optData['value']],
                    collect($optData)->except('value')->merge(['attribute_id' => $attribute->id])->toArray()
                );
                $optionMap[$attribute->code . ':' . $optData['value']] = $option;
            }
        }

        // ── Import Categories ──
        $categoryIdMap = []; // old_id => new_id mapping
        foreach ($data['categories'] ?? [] as $catData) {
            $oldId = $catData['id'] ?? null;
            unset($catData['id'], $catData['image_url']);

            // Resolve parent_id from mapping
            if (!empty($catData['parent_id']) && isset($categoryIdMap[$catData['parent_id']])) {
                $catData['parent_id'] = $categoryIdMap[$catData['parent_id']];
            } else {
                $catData['parent_id'] = null;
            }

            $category = Category::withTrashed()->updateOrCreate(
                ['slug' => $catData['slug']],
                array_merge($catData, ['deleted_at' => null])
            );

            if ($oldId) {
                $categoryIdMap[$oldId] = $category->id;
            }
            $categoryCount++;
        }

        // ── Import Products ──
        foreach ($data['products'] ?? [] as $prodData) {
            $categorySlug = $prodData['category_slug'] ?? null;
            $imagesData = $prodData['images'] ?? [];
            $reviewsData = $prodData['reviews'] ?? [];
            $attributeValues = $prodData['attribute_values'] ?? [];

            // Strip non-product fields
            unset(
                $prodData['id'], $prodData['category_slug'], $prodData['in_stock'],
                $prodData['main_image'], $prodData['images'], $prodData['reviews'],
                $prodData['attribute_values']
            );

            $product = Product::withTrashed()->updateOrCreate(
                ['sku' => $prodData['sku']],
                array_merge($prodData, ['deleted_at' => null])
            );

            // Assign to category via slug
            if ($categorySlug) {
                $category = Category::where('slug', $categorySlug)->first();
                if ($category && !$product->categories()->where('categories.id', $category->id)->exists()) {
                    $product->categories()->attach($category->id);
                }
            }

            // ── Import Product Images ──
            if ($fresh) {
                $product->images()->delete();
                $product->update(['main_image_id' => null]);
            }

            $firstImageId = null;
            if (!empty($imagesData)) {
                foreach ($imagesData as $imgData) {
                    // Generate a local demo image instead of relying on external URLs
                    $localPath = $this->generateDemoImage(
                        $product->name,
                        $product->slug,
                        $imgData['position'] ?? 1,
                        $imgData['bg_color'] ?? null
                    );

                    $img = ProductImage::updateOrCreate(
                        ['product_id' => $product->id, 'position' => $imgData['position'] ?? 1],
                        [
                            'product_id' => $product->id,
                            'path' => $localPath,
                            'thumbnail_path' => null,
                            'alt_text' => $imgData['alt_text'] ?? $product->name,
                            'position' => $imgData['position'] ?? 1,
                        ]
                    );

                    if (!$firstImageId) {
                        $firstImageId = $img->id;
                    }
                    $imageCount++;
                }
            } else {
                // Auto-generate a single demo image even if no images defined in data
                $localPath = $this->generateDemoImage($product->name, $product->slug, 1);
                $img = ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'position' => 1],
                    [
                        'product_id' => $product->id,
                        'path' => $localPath,
                        'alt_text' => $product->name,
                        'position' => 1,
                    ]
                );
                $firstImageId = $img->id;
                $imageCount++;
            }

            // Set the main image for the product
            if ($firstImageId) {
                $product->update(['main_image_id' => $firstImageId]);
            }

            // ── Import Product Reviews ──
            if (!empty($reviewsData)) {
                if ($fresh) {
                    $product->reviews()->delete();
                }
                foreach ($reviewsData as $revData) {
                    ProductReview::create([
                        'product_id' => $product->id,
                        'reviewer_name' => $revData['reviewer_name'] ?? 'Customer',
                        'reviewer_email' => $revData['reviewer_email'] ?? null,
                        'rating' => $revData['rating'] ?? 5,
                        'title' => $revData['title'] ?? '',
                        'comment' => $revData['comment'] ?? '',
                        'status' => $revData['status'] ?? 'approved',
                        'verified_purchase' => $revData['verified_purchase'] ?? false,
                        'helpful_count' => $revData['helpful_count'] ?? 0,
                    ]);
                    $reviewCount++;
                }
            }

            // ── Import Product Attribute Values ──
            if (!empty($attributeValues)) {
                if ($fresh) {
                    $product->attributeValues()->delete();
                }
                foreach ($attributeValues as $avData) {
                    $attrCode = $avData['attribute_code'] ?? null;
                    $optValue = $avData['option_value'] ?? null;

                    if (!$attrCode) continue;

                    $attribute = $attributeMap[$attrCode] ?? Attribute::where('code', $attrCode)->first();
                    if (!$attribute) continue;

                    $optionKey = $attrCode . ':' . $optValue;
                    $option = $optionMap[$optionKey] ?? AttributeOption::where('attribute_id', $attribute->id)
                        ->where('value', $optValue)->first();

                    if ($option) {
                        ProductAttributeValue::updateOrCreate(
                            ['product_id' => $product->id, 'attribute_id' => $attribute->id],
                            [
                                'product_id' => $product->id,
                                'attribute_id' => $attribute->id,
                                'attribute_option_id' => $option->id,
                            ]
                        );
                    } elseif (isset($avData['text_value'])) {
                        // Fallback for text-type attributes
                        ProductAttributeValue::updateOrCreate(
                            ['product_id' => $product->id, 'attribute_id' => $attribute->id],
                            [
                                'product_id' => $product->id,
                                'attribute_id' => $attribute->id,
                                'text_value' => $avData['text_value'],
                            ]
                        );
                    }
                }
            }

            $productCount++;
        }

        return [
            'categories' => $categoryCount,
            'products' => $productCount,
            'reviews' => $reviewCount,
            'images' => $imageCount,
            'attributes' => $attributeCount,
        ];
    }

    /**
     * Flatten nested settings into dot-notation keys for the schema-driven form.
     *
     * The theme-data importer writes nested objects (e.g. {"contact": {"phone": "..."}}).
     * The admin form expects flat keys like "contact.phone". This method converts
     * between the two formats automatically so both sources are supported.
     */
    protected function flattenSettings(array $settings, string $prefix = ''): array
    {
        $flat = [];

        foreach ($settings as $key => $value) {
            $fullKey = $prefix === '' ? $key : $prefix . '.' . $key;

            if (is_array($value) && !array_is_list($value)) {
                // Recursively flatten associative arrays
                $flat = array_merge($flat, $this->flattenSettings($value, $fullKey));
            } else {
                $flat[$fullKey] = $value;
            }
        }

        return $flat;
    }

    /**
     * Resolve a theme screenshot URL from public assets, auto-publishing from
     * the theme folder when available.
     */
    protected function resolveThemeScreenshotUrl(Theme $theme): ?string
    {
        $configuredScreenshot = $theme->screenshot ?: ($theme->getConfig()['screenshot'] ?? null);

        if (!$configuredScreenshot) {
            return null;
        }

        $publicRelativePath = "themes/{$theme->slug}/{$configuredScreenshot}";
        $publicPath = public_path($publicRelativePath);

        if (!file_exists($publicPath)) {
            $sourcePath = $theme->getPath() . '/' . ltrim($configuredScreenshot, '/');

            if (file_exists($sourcePath)) {
                $targetDirectory = dirname($publicPath);
                if (!is_dir($targetDirectory)) {
                    mkdir($targetDirectory, 0755, true);
                }

                @copy($sourcePath, $publicPath);
            }
        }

        return file_exists($publicPath) ? asset($publicRelativePath) : null;
    }

    /**
     * Generate a local demo product image with gradient background and product name.
     * Stored in storage/app/public/products/demo/.
     */
    protected function generateDemoImage(string $productName, string $slug, int $position, ?string $bgHex = null): string
    {
        $dir = storage_path('app/public/products/demo');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Fresh, modern accent colors (soft pastels)
        $palette = [
            ['bg' => '#F0F4F8', 'accent' => '#4A90D9', 'shape' => '#D6E4F0'],  // soft blue
            ['bg' => '#F5F0FF', 'accent' => '#7C5CFC', 'shape' => '#E0D4FC'],  // soft purple
            ['bg' => '#FFF5F5', 'accent' => '#E53E3E', 'shape' => '#FED7D7'],  // soft red
            ['bg' => '#F0FFF4', 'accent' => '#38A169', 'shape' => '#C6F6D5'],  // soft green
            ['bg' => '#FFFAF0', 'accent' => '#DD6B20', 'shape' => '#FEEBC8'],  // soft orange
            ['bg' => '#FFF5F7', 'accent' => '#D53F8C', 'shape' => '#FED7E2'],  // soft pink
            ['bg' => '#F0FCFF', 'accent' => '#0891B2', 'shape' => '#CFFAFE'],  // soft teal
            ['bg' => '#FEFCE8', 'accent' => '#CA8A04', 'shape' => '#FEF08A'],  // soft gold
        ];
        $hash = crc32($slug . $position);
        $colors = $palette[abs($hash) % count($palette)];

        $width = 800;
        $height = 800;
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        // Helper to parse hex
        $hex2rgb = function ($hex) {
            $hex = ltrim($hex, '#');
            return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        };

        // Fill background
        [$br, $bg, $bb] = $hex2rgb($colors['bg']);
        $bgColor = imagecolorallocate($img, $br, $bg, $bb);
        imagefilledrectangle($img, 0, 0, $width, $height, $bgColor);

        // Draw large subtle shape circle (product silhouette area)
        [$sr, $sg, $sb] = $hex2rgb($colors['shape']);
        $shapeColor = imagecolorallocate($img, $sr, $sg, $sb);
        imagefilledellipse($img, (int)($width / 2), (int)($height / 2 - 30), 420, 420, $shapeColor);

        // Draw a slightly darker inner circle to simulate product body
        $innerR = (int)(($sr + $br) / 2);
        $innerG = (int)(($sg + $bg) / 2);
        $innerB = (int)(($sb + $bb) / 2);
        $innerColor = imagecolorallocate($img, $innerR, $innerG, $innerB);
        imagefilledellipse($img, (int)($width / 2), (int)($height / 2 - 30), 280, 280, $innerColor);

        // Draw accent dot (like a power indicator or detail)
        [$ar, $ag, $ab] = $hex2rgb($colors['accent']);
        $accentColor = imagecolorallocate($img, $ar, $ag, $ab);
        imagefilledellipse($img, (int)($width / 2 + 60), (int)($height / 2 - 90), 24, 24, $accentColor);

        // Draw a subtle rounded rectangle at bottom for label area
        $labelBg = imagecolorallocatealpha($img, $ar, $ag, $ab, 100);
        imagefilledrectangle($img, 0, $height - 120, $width, $height, $labelBg);

        // Draw thin accent line separator
        $accentLine = imagecolorallocatealpha($img, $ar, $ag, $ab, 60);
        imagefilledrectangle($img, 100, $height - 122, $width - 100, $height - 120, $accentLine);

        // Product name text - use GD built-in fonts
        $textColor = imagecolorallocate($img, 60, 60, 70);
        $fontSize = 5; // largest built-in

        // Split product name into lines
        $words = explode(' ', $productName);
        $lines = [];
        $currentLine = '';
        foreach ($words as $word) {
            $testLine = $currentLine ? "$currentLine $word" : $word;
            if (strlen($testLine) > 24 && $currentLine) {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }
        $lines[] = $currentLine;

        $lineHeight = imagefontheight($fontSize) + 8;
        $totalTextHeight = count($lines) * $lineHeight;
        $startY = $height - 110 + (90 - $totalTextHeight) / 2;

        foreach ($lines as $i => $line) {
            $textWidth = imagefontwidth($fontSize) * strlen($line);
            $x = ($width - $textWidth) / 2;
            imagestring($img, $fontSize, (int)$x, (int)($startY + $i * $lineHeight), $line, $textColor);
        }

        // Add position indicator for variant images
        if ($position > 1) {
            $badge = imagecolorallocate($img, $ar, $ag, $ab);
            imagefilledellipse($img, $width - 50, 50, 50, 50, $badge);
            $whiteText = imagecolorallocate($img, 255, 255, 255);
            $posStr = (string) $position;
            $pw = imagefontwidth(5) * strlen($posStr);
            imagestring($img, 5, $width - 50 - (int)($pw / 2), 42, $posStr, $whiteText);
        }

        // Small decorative elements (dots pattern in corner)
        $dotColor = imagecolorallocatealpha($img, $ar, $ag, $ab, 100);
        for ($dx = 0; $dx < 4; $dx++) {
            for ($dy = 0; $dy < 4; $dy++) {
                imagefilledellipse($img, 60 + $dx * 18, 60 + $dy * 18, 6, 6, $dotColor);
            }
        }

        $filename = "{$slug}-{$position}.png";
        $path = "products/demo/{$filename}";
        imagepng($img, storage_path("app/public/{$path}"), 6);
        imagedestroy($img);

        return $path;
    }
}
