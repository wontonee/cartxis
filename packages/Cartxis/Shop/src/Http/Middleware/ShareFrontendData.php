<?php

namespace Cartxis\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Cartxis\Core\Models\Theme;
use Cartxis\Shop\Repositories\CategoryRepository;
use Cartxis\Core\Services\SettingService;

class ShareFrontendData
{
    protected $categoryRepository;
    protected $settingService;

    public function __construct(CategoryRepository $categoryRepository, SettingService $settingService)
    {
        $this->categoryRepository = $categoryRepository;
        $this->settingService = $settingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to non-admin routes
        if ($request->is('admin/*') || $request->is('admin')) {
            return $next($request);
        }

        // Core frontend data (shared across ALL themes)
        $sharedData = [
            'siteConfig' => [
                'name' => $this->settingService->get('site_name') ?? config('app.name'),
                'url' => config('app.url'),
                'description' => $this->settingService->get('site_tagline') ?? 'Cartxis E-commerce Platform',
                'logo' => $this->settingService->get('site_logo') ?? null,
                'favicon' => $this->settingService->get('site_favicon') ?? null,
            ],
            
            // Share categories for header navigation
            'categories' => function () {
                try {
                    return $this->categoryRepository->getRootCategories()
                        ->map(function ($category) {
                            return [
                                'id' => $category->id,
                                'name' => $category->name,
                                'slug' => $category->slug,
                                'description' => $category->description,
                                'image' => $category->image,
                            ];
                        });
                } catch (\Exception $e) {
                    // Silent fail during migrations
                }
                return [];
            },

            // Theme basics (name, slug, settings) — needed by all themes
            'theme' => function () {
                try {
                    $theme = Theme::where('is_active', true)->first();
                    if ($theme) {
                        $configSettings = $theme->getConfig()['settings'] ?? [];
                        $defaultSettings = is_array($configSettings)
                            ? $this->flattenSettings($configSettings)
                            : [];

                        $databaseSettings = is_array($theme->settings)
                            ? $this->flattenSettings($theme->settings)
                            : [];

                        $settings = $this->normalizeThemeSettings(array_merge($defaultSettings, $databaseSettings));

                        return [
                            'name' => $theme->name,
                            'slug' => $theme->slug,
                            'settings' => $settings,
                        ];
                    }
                } catch (\Exception $e) {
                    // Silent fail during migrations
                }
                return null;
            },
        ];

        // Let the active theme register additional shared props via hooks.
        // Each theme's hooks.php can add its own data (contactInfo, socialLinks, etc.)
        // without polluting the global middleware or affecting other themes.
        //
        // Usage in theme hooks.php:
        //   add_filter('theme.shared_data', function ($data) {
        //       $data['contactInfo'] = fn () => [...];
        //       $data['socialLinks'] = fn () => [...];
        //       return $data;
        //   });
        try {
            $sharedData = apply_filters('theme.shared_data', $sharedData);
        } catch (\Exception $e) {
            // Silent fail if hooks not loaded yet
        }

        Inertia::share($sharedData);

        return $next($request);
    }

    /**
     * Flatten nested settings array to dot-notation keys.
     * DB may store {"colors":{"primary":"#fff"}} — frontend expects flat keys like "colors.primary".
     */
    protected function flattenSettings(array $settings, string $prefix = ''): array
    {
        $flat = [];
        foreach ($settings as $key => $value) {
            $fullKey = $prefix === '' ? $key : $prefix . '.' . $key;
            if (is_array($value) && !array_is_list($value)) {
                $flat = array_merge($flat, $this->flattenSettings($value, $fullKey));
            } else {
                $flat[$fullKey] = $value;
            }
        }
        return $flat;
    }

    protected function normalizeThemeSettings(array $settings): array
    {
        $aliases = [
            'colors.primary' => 'colors.primary_color',
            'colors.secondary' => 'colors.secondary_color',
            'colors.accent' => 'colors.accent_color',
            'colors.success' => 'colors.success_color',
            'colors.danger' => 'colors.danger_color',
            'colors.warning' => 'colors.warning_color',
            'colors.info' => 'colors.info_color',
            'typography.font_family' => 'typography.body_font',
        ];

        foreach ($aliases as $canonical => $alias) {
            if (!array_key_exists($canonical, $settings) && array_key_exists($alias, $settings)) {
                $settings[$canonical] = $settings[$alias];
            }

            if (!array_key_exists($alias, $settings) && array_key_exists($canonical, $settings)) {
                $settings[$alias] = $settings[$canonical];
            }
        }

        return $settings;
    }
}
