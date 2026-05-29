<?php

declare(strict_types=1);

namespace Cartxis\Core\Services;

use Cartxis\CMS\Models\Block;
use Cartxis\Core\Models\Currency;
use Cartxis\Core\Models\Theme;
use Cartxis\Product\Models\Attribute;
use Cartxis\Product\Models\AttributeOption;
use Cartxis\Product\Models\Category;
use Cartxis\Product\Models\Product;
use Cartxis\Product\Models\ProductAttributeValue;
use Cartxis\Product\Models\ProductImage;
use Cartxis\Product\Models\ProductReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ThemeDataImportService
{
    private ?Currency $defaultCurrency = null;

    /**
     * Import theme demo data from templates/storefront/{category}/{slug}/data/theme-data.json.
     *
     * @return array{blocks: int, menus: int, settings: bool}
     */
    public function import(
        string $slug,
        bool $blocks = true,
        bool $menus = true,
        bool $settings = true,
        bool $fresh = false,
    ): array {
        ['theme' => $theme, 'data' => $data] = $this->loadThemeData($slug);

        $importAll = ! $blocks && ! $menus && ! $settings;

        if ($fresh) {
            $this->cleanExistingData($slug, $data, $importAll, $blocks, $menus, $settings, $theme);
        }

        $results = ['blocks' => 0, 'menus' => 0, 'settings' => false];

        if ($importAll || $blocks) {
            $results['blocks'] = $this->importBlocks($data['blocks'] ?? [], $slug);
        }

        if ($importAll || $menus) {
            $results['menus'] = $this->importMenus($data['menus'] ?? [], $slug);
        }

        if ($importAll || $settings) {
            $results['settings'] = $this->importSettings($data['settings'] ?? [], $theme);
        }

        return $results;
    }

    /**
     * Import demo catalog data (categories, products, attributes) from theme-data.json.
     *
     * @return array{categories: int, products: int, reviews: int, images: int, attributes: int}
     */
    public function importProducts(string $slug, bool $fresh = false): array
    {
        ['data' => $data] = $this->loadThemeData($slug);

        return $this->importProductsFromData($data, $fresh);
    }

    /**
     * @param  array{blocks: int, menus: int, settings: bool}  $results
     * @param  array{categories: int, products: int, reviews: int, images: int, attributes: int}  $productResults
     */
    public function buildImportSuccessMessage(array $results, array $productResults): string
    {
        $parts = ["{$results['blocks']} blocks", "{$results['menus']} menu items", 'settings'];

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

        return 'Theme data imported successfully! ' . implode(', ', $parts) . ' have been updated.';
    }

    /**
     * @return array{theme: Theme, data: array<string, mixed>}
     */
    private function loadThemeData(string $slug): array
    {
        $theme = Theme::where('slug', $slug)->first();

        if (! $theme) {
            throw new RuntimeException("Theme '{$slug}' not found.");
        }

        $dataPath = $theme->getPath() . '/data/theme-data.json';

        if (! file_exists($dataPath)) {
            throw new RuntimeException('No theme-data.json found for this theme.');
        }

        $data = json_decode((string) file_get_contents($dataPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON in theme-data.json: ' . json_last_error_msg());
        }

        return ['theme' => $theme, 'data' => $data];
    }

    public function importBlocks(array $blocks, string $themeSlug): int
    {
        $count = 0;

        foreach ($blocks as $blockData) {
            $identifier = $blockData['identifier'];

            Block::withTrashed()->updateOrCreate(
                ['identifier' => $identifier],
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

            $count++;
        }

        return $count;
    }

    public function importMenus(array $menus, string $themeSlug): int
    {
        $count = 0;

        foreach ($menus as $menuType => $items) {
            foreach ($items as $item) {
                $count += $this->createMenuItem($item, $menuType, $themeSlug, null);
            }
        }

        return $count;
    }

    private function createMenuItem(array $item, string $menuType, string $themeSlug, ?int $parentId): int
    {
        $count = 0;
        $key = "{$themeSlug}-{$menuType}-" . Str::slug($item['title']);

        $existing = DB::table('menu_items')->where('key', $key)->first();

        $data = [
            'title'      => $item['title'],
            'key'        => $key,
            'icon'       => $item['icon'] ?? null,
            'route'      => $item['route'] ?? null,
            'url'        => $item['url'] ?? null,
            'location'   => 'storefront',
            'menu_type'  => $menuType,
            'parent_id'  => $parentId,
            'order'      => $item['order'] ?? 0,
            'active'     => $item['active'] ?? true,
            'meta'       => isset($item['meta']) ? json_encode($item['meta']) : null,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('menu_items')->where('id', $existing->id)->update($data);
            $itemId = $existing->id;
        } else {
            $itemId = DB::table('menu_items')->insertGetId(array_merge($data, [
                'created_at' => now(),
            ]));
        }

        $count++;

        if (! empty($item['children'])) {
            foreach ($item['children'] as $child) {
                $count += $this->createMenuItem($child, $menuType, $themeSlug, $itemId);
            }
        }

        return $count;
    }

    public function importSettings(array $settings, Theme $theme): bool
    {
        if ($settings === []) {
            return false;
        }

        $existingSettings = $theme->settings ?? [];
        $mergedSettings = array_replace_recursive($existingSettings, $settings);

        $theme->update(['settings' => $mergedSettings]);

        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{categories: int, products: int, reviews: int, images: int, attributes: int}
     */
    private function importProductsFromData(array $data, bool $fresh): array
    {
        $categoryCount = 0;
        $productCount = 0;
        $reviewCount = 0;
        $imageCount = 0;
        $attributeCount = 0;

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

        $attributeMap = [];
        $optionMap = [];

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

        $categoryIdMap = [];

        foreach ($data['categories'] ?? [] as $catData) {
            $oldId = $catData['id'] ?? null;
            unset($catData['id'], $catData['image_url']);

            if (! empty($catData['parent_id']) && isset($categoryIdMap[$catData['parent_id']])) {
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

        foreach ($data['products'] ?? [] as $prodData) {
            $categorySlug = $prodData['category_slug'] ?? null;
            $imagesData = $prodData['images'] ?? [];
            $reviewsData = $prodData['reviews'] ?? [];
            $attributeValues = $prodData['attribute_values'] ?? [];

            if (array_key_exists('price', $prodData) && is_numeric($prodData['price'])) {
                $prodData['price'] = $this->toStorePrice((float) $prodData['price']);
            }

            if (array_key_exists('special_price', $prodData) && is_numeric($prodData['special_price'])) {
                $prodData['special_price'] = $this->toStorePrice((float) $prodData['special_price']);
            }

            unset(
                $prodData['id'], $prodData['category_slug'], $prodData['in_stock'],
                $prodData['main_image'], $prodData['images'], $prodData['reviews'],
                $prodData['attribute_values']
            );

            $product = Product::withTrashed()->updateOrCreate(
                ['sku' => $prodData['sku']],
                array_merge($prodData, ['deleted_at' => null])
            );

            if ($categorySlug) {
                $category = Category::where('slug', $categorySlug)->first();
                if ($category && ! $product->categories()->where('categories.id', $category->id)->exists()) {
                    $product->categories()->attach($category->id);
                }
            }

            if ($fresh) {
                $product->images()->delete();
                $product->update(['main_image_id' => null]);
            }

            $firstImageId = null;

            if (! empty($imagesData)) {
                foreach ($imagesData as $imgData) {
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

                    if (! $firstImageId) {
                        $firstImageId = $img->id;
                    }

                    $imageCount++;
                }
            } else {
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

            if ($firstImageId) {
                $product->update(['main_image_id' => $firstImageId]);
            }

            if (! empty($reviewsData)) {
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

            if (! empty($attributeValues)) {
                if ($fresh) {
                    $product->attributeValues()->delete();
                }

                foreach ($attributeValues as $avData) {
                    $attrCode = $avData['attribute_code'] ?? null;
                    $optValue = $avData['option_value'] ?? null;

                    if (! $attrCode) {
                        continue;
                    }

                    $attribute = $attributeMap[$attrCode] ?? Attribute::where('code', $attrCode)->first();
                    if (! $attribute) {
                        continue;
                    }

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

    private function cleanExistingData(
        string $themeSlug,
        array $data,
        bool $importAll,
        bool $blocks,
        bool $menus,
        bool $settings,
        Theme $theme,
    ): void {
        if ($importAll || $blocks) {
            $identifiers = collect($data['blocks'] ?? [])->pluck('identifier')->toArray();
            if ($identifiers !== []) {
                Block::withTrashed()->whereIn('identifier', $identifiers)->forceDelete();
            }
        }

        if ($importAll || $menus) {
            DB::table('menu_items')
                ->where('key', 'like', "{$themeSlug}-%")
                ->delete();
        }

        if ($importAll || $settings) {
            $theme->update(['settings' => []]);
        }
    }

    private function toStorePrice(float $basePrice): float
    {
        $currency = $this->getDefaultCurrency();
        if (! $currency) {
            return round($basePrice, 2);
        }

        $exchangeRate = $this->resolveExchangeRate($currency);
        $decimalPlaces = max(0, (int) $currency->decimal_places);

        if ($exchangeRate <= 0) {
            return round($basePrice, $decimalPlaces);
        }

        return round($basePrice * $exchangeRate, $decimalPlaces);
    }

    private function getDefaultCurrency(): ?Currency
    {
        if ($this->defaultCurrency instanceof Currency) {
            return $this->defaultCurrency;
        }

        $this->defaultCurrency = Currency::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if (! $this->defaultCurrency) {
            $this->defaultCurrency = Currency::query()
                ->where('is_default', true)
                ->first();
        }

        return $this->defaultCurrency;
    }

    private function resolveExchangeRate(Currency $currency): float
    {
        $rate = (float) $currency->exchange_rate;
        $code = strtoupper((string) $currency->code);

        if ($rate > 0 && ! ($code !== 'USD' && $rate == 1.0)) {
            return $rate;
        }

        $fallbackRates = [
            'USD' => 1.0,
            'EUR' => 0.92,
            'GBP' => 0.79,
            'INR' => 83.0,
            'AUD' => 1.52,
            'CAD' => 1.36,
        ];

        return $fallbackRates[$code] ?? max($rate, 1.0);
    }

    private function generateDemoImage(string $productName, string $slug, int $position, ?string $bgHex = null): string
    {
        $dir = storage_path('app/public/products/demo');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $palette = [
            ['bg' => '#F0F4F8', 'accent' => '#4A90D9', 'shape' => '#D6E4F0'],
            ['bg' => '#F5F0FF', 'accent' => '#7C5CFC', 'shape' => '#E0D4FC'],
            ['bg' => '#FFF5F5', 'accent' => '#E53E3E', 'shape' => '#FED7D7'],
            ['bg' => '#F0FFF4', 'accent' => '#38A169', 'shape' => '#C6F6D5'],
            ['bg' => '#FFFAF0', 'accent' => '#DD6B20', 'shape' => '#FEEBC8'],
            ['bg' => '#FFF5F7', 'accent' => '#D53F8C', 'shape' => '#FED7E2'],
            ['bg' => '#F0FCFF', 'accent' => '#0891B2', 'shape' => '#CFFAFE'],
            ['bg' => '#FEFCE8', 'accent' => '#CA8A04', 'shape' => '#FEF08A'],
        ];
        $hash = crc32($slug . $position);
        $colors = $palette[abs($hash) % count($palette)];

        $width = 800;
        $height = 800;
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        $hex2rgb = function ($hex) {
            $hex = ltrim($hex, '#');

            return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        };

        [$br, $bg, $bb] = $hex2rgb($colors['bg']);
        $bgColor = imagecolorallocate($img, $br, $bg, $bb);
        imagefilledrectangle($img, 0, 0, $width, $height, $bgColor);

        [$sr, $sg, $sb] = $hex2rgb($colors['shape']);
        $shapeColor = imagecolorallocate($img, $sr, $sg, $sb);
        imagefilledellipse($img, (int) ($width / 2), (int) ($height / 2 - 30), 420, 420, $shapeColor);

        $innerR = (int) (($sr + $br) / 2);
        $innerG = (int) (($sg + $bg) / 2);
        $innerB = (int) (($sb + $bb) / 2);
        $innerColor = imagecolorallocate($img, $innerR, $innerG, $innerB);
        imagefilledellipse($img, (int) ($width / 2), (int) ($height / 2 - 30), 280, 280, $innerColor);

        [$ar, $ag, $ab] = $hex2rgb($colors['accent']);
        $accentColor = imagecolorallocate($img, $ar, $ag, $ab);
        imagefilledellipse($img, (int) ($width / 2 + 60), (int) ($height / 2 - 90), 24, 24, $accentColor);

        $labelBg = imagecolorallocatealpha($img, $ar, $ag, $ab, 100);
        imagefilledrectangle($img, 0, $height - 120, $width, $height, $labelBg);

        $accentLine = imagecolorallocatealpha($img, $ar, $ag, $ab, 60);
        imagefilledrectangle($img, 100, $height - 122, $width - 100, $height - 120, $accentLine);

        $textColor = imagecolorallocate($img, 60, 60, 70);
        $fontSize = 5;

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
            imagestring($img, $fontSize, (int) $x, (int) ($startY + $i * $lineHeight), $line, $textColor);
        }

        if ($position > 1) {
            $badge = imagecolorallocate($img, $ar, $ag, $ab);
            imagefilledellipse($img, $width - 50, 50, 50, 50, $badge);
            $whiteText = imagecolorallocate($img, 255, 255, 255);
            $posStr = (string) $position;
            $pw = imagefontwidth(5) * strlen($posStr);
            imagestring($img, 5, $width - 50 - (int) ($pw / 2), 42, $posStr, $whiteText);
        }

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
