<?php

namespace Cartxis\Core\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Core\Services\ThemeLifecycleService;
use Cartxis\Core\Services\ThemeService;
use Cartxis\Core\Services\ThemeDataImportService;
use Cartxis\Core\Services\ThemeAssetBuildService;
use Cartxis\Core\Models\Theme;
use Cartxis\UIEditor\Services\LayoutService;
use Cartxis\UIEditor\Models\PageLayout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    protected ThemeService $themeService;
    protected LayoutService $layoutService;

    public function __construct(ThemeService $themeService, LayoutService $layoutService)
    {
        $this->themeService = $themeService;
        $this->layoutService = $layoutService;
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
                'id'             => $theme->id,
                'name'           => $theme->name,
                'slug'           => $theme->slug,
                'description'    => $theme->description,
                'version'        => $theme->version,
                'author'         => $theme->author,
                'screenshot'     => $this->resolveThemeScreenshotUrl($theme),
                'is_active'      => $theme->is_active,
                'is_default'     => $theme->is_default,
                'exists'         => $theme->exists(),
                'supports'       => $config['supports'] ?? [],
                'homepageEditor' => $this->homepageEditorMeta($theme),
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
        $dataPath = $theme->getPath() . '/data/theme-data.json';
        $hasThemeData = file_exists($dataPath);
        $hasProductData = false;

        if ($hasThemeData) {
            $themeData = json_decode((string) file_get_contents($dataPath), true);
            $hasProductData = ! empty($themeData['categories']) || ! empty($themeData['products']);
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
            'homepageEditor' => $this->homepageEditorMeta($theme),
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

            $lifecycle = app(ThemeLifecycleService::class)->finalize($slug);

            $message = "Theme '{$theme->name}' activated successfully!";

            if ($lifecycle['assets_rebuilt']) {
                $message .= ' Storefront assets updated.';
            } elseif ($lifecycle['cache_cleared']) {
                $message .= ' Caches cleared — hard-refresh the storefront if styles look stale.';
            }

            return back()->with('success', $message);
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

            Theme::where('slug', $installedSlug)->update([
                'source' => 'upload',
            ]);

            $lifecycle = app(ThemeLifecycleService::class)->finalize($installedSlug);

            $message = "Theme installed successfully ({$installedSlug})!";

            if ($lifecycle['assets_rebuilt']) {
                $message .= ' Storefront assets updated.';
            } else {
                $message .= ' Run npm run build on the server, then hard-refresh the storefront.';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to install theme: ' . $e->getMessage());
        }
    }

    /**
     * Import theme data (CMS blocks, menus, settings) from the theme's data/theme-data.json file.
     */
    public function importData(Request $request, string $slug, ThemeDataImportService $importService)
    {
        Theme::where('slug', $slug)->firstOrFail();

        $fresh = $request->boolean('fresh', false);

        try {
            $results = $importService->import($slug, fresh: $fresh);

            $productResults = [
                'categories' => 0,
                'products' => 0,
                'reviews' => 0,
                'images' => 0,
                'attributes' => 0,
            ];

            if ($request->boolean('include_products', false)) {
                $productResults = $importService->importProducts($slug, $fresh);
            }

            cache()->forget('active_theme');

            return back()->with(
                'success',
                $importService->buildImportSuccessMessage($results, $productResults)
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Failed to import theme data: ' . $e->getMessage());
        }
    }

    /**
     * Import a pre-built UIEditor homepage layout from the theme's data/theme-data.json.
     * The JSON must have a "homepage" key containing a valid UIEditor layout object.
     */
    public function importLayout(Request $request, string $slug)
    {
        $theme = Theme::where('slug', $slug)->firstOrFail();

        $dataPath = $theme->getPath() . '/data/theme-data.json';

        if (!file_exists($dataPath)) {
            return back()->with('error', 'No demo data file found for this theme.');
        }

        $themeData = json_decode(file_get_contents($dataPath), true);

        if (empty($themeData['homepage'])) {
            return back()->with('error', 'No homepage layout found in theme demo data.');
        }

        $layoutData = $themeData['homepage'];

        // Validate basic structure
        if (empty($layoutData['version']) || !isset($layoutData['sections'])) {
            return back()->with('error', 'Invalid layout format in theme demo data.');
        }

        try {
            // Homepage layout uses TYPE_HOMEPAGE with null page_id — save and immediately publish
            $layout = $this->layoutService->saveDraft($layoutData, PageLayout::TYPE_HOMEPAGE, null);
            $this->layoutService->publish($layout);

            return back()->with('success', 'Homepage demo layout imported and published successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to import layout: ' . $e->getMessage());
        }
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
            $themePath = public_path("templates/{$slug}");
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
     * Homepage editor affordances differ between UI Editor layouts and native theme homepages.
     *
     * @return array{
     *     mode: string,
     *     showPublishedBanner: bool,
     *     showImportLayout: bool,
     *     editorUrl: string,
     *     editorLabel: string,
     *     bannerTitle: string,
     *     bannerDescription: string
     * }
     */
    protected function homepageEditorMeta(Theme $theme): array
    {
        $config = $theme->getConfig();
        $nativeHomepage = ! empty($config['native_homepage']);
        $dataPath = $theme->getPath() . '/data/theme-data.json';
        $hasUiEditorLayout = false;

        if (file_exists($dataPath)) {
            $themeData = json_decode((string) file_get_contents($dataPath), true);
            $hasUiEditorLayout = ! empty($themeData['homepage']);
        }

        $hasPublishedLayout = PageLayout::homepage()->published()->exists();

        if ($nativeHomepage) {
            return [
                'mode' => 'native',
                'showPublishedBanner' => true,
                'showImportLayout' => false,
                'editorUrl' => route('admin.appearance.index', ['tab' => 'features']),
                'editorLabel' => 'Open Page Builder',
                'bannerTitle' => 'Native Homepage is Live',
                'bannerDescription' => 'This theme renders a built-in homepage. Use the Page Builder to customize homepage sections, hero content, and visibility.',
            ];
        }

        return [
            'mode' => 'layout',
            'showPublishedBanner' => $hasUiEditorLayout && $hasPublishedLayout,
            'showImportLayout' => $hasUiEditorLayout && ! $hasPublishedLayout,
            'editorUrl' => url('/admin/content/pages'),
            'editorLabel' => 'Open Page Builder',
            'bannerTitle' => 'Homepage Layout is Live',
            'bannerDescription' => 'Your homepage is rendering a published layout. Open the Page Builder to edit it.',
        ];
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

        $publicRelativePath = "templates/{$theme->slug}/{$configuredScreenshot}";
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
}
