<?php

declare(strict_types=1);

namespace Cartxis\Core\Services;

use Cartxis\Core\Models\Theme;
use Cartxis\Setup\Services\DemoDataService;
use Cartxis\UIEditor\Models\PageLayout;
use Cartxis\UIEditor\Services\LayoutService;
use Exception;
use Illuminate\Support\Facades\File;
use ZipArchive;

class TemplateInstallService
{
    public function __construct(
        protected TemplateCatalogService $catalog,
        protected ThemeService $themes,
        protected LayoutService $layouts,
        protected ThemePathResolver $paths,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function install(string $slug, array $options = []): array
    {
        $entry = $this->catalog->find($slug);

        if ($entry === null) {
            throw new Exception("Template \"{$slug}\" was not found in the catalog.");
        }

        if (($entry['type'] ?? '') !== 'storefront') {
            throw new Exception('Only storefront templates can be installed in v1.');
        }

        $defaultSlug = (string) config('theme.default', 'cartxis-default');
        if ($slug === $defaultSlug && $this->catalog->isInstalled($slug)) {
            throw new Exception('The default theme is already installed and cannot be reinstalled from the catalog.');
        }

        $sourcePath = (string) ($entry['path'] ?? '');
        $category = (string) ($entry['category'] ?? 'general');
        $targetPath = $this->paths->installPath($category, $slug);

        if ($sourcePath === '' || ! is_dir($sourcePath)) {
            throw new Exception("Catalog files for \"{$slug}\" are missing.");
        }

        if (realpath($sourcePath) !== realpath($targetPath)) {
            if (is_dir($targetPath)) {
                File::deleteDirectory($targetPath);
            }

            File::ensureDirectoryExists(dirname($targetPath));
            File::copyDirectory($sourcePath, $targetPath);
        }

        $this->themes->discover();

        $theme = Theme::where('slug', $slug)->first();

        if ($theme) {
            $theme->update([
                'catalog_slug' => $slug,
                'source' => 'catalog',
                'category' => $category,
                'installed_from_catalog_at' => now(),
            ]);
        }

        $result = [
            'slug' => $slug,
            'installed' => true,
            'activated' => false,
            'demo_products_imported' => false,
            'layout_imported' => false,
        ];

        if (! empty($options['activate'])) {
            $result['activated'] = $this->themes->activate($slug);
        }

        if (! empty($options['import_layout']) && ! $this->usesNativeHomepage($entry)) {
            $result['layout_imported'] = $this->importHomepageLayout($slug, $category);
        }

        $includes = (array) ($entry['includes'] ?? []);
        $demoBusinessType = $entry['demo_business_type'] ?? null;

        if (! empty($options['import_demo_products']) && $demoBusinessType && in_array('demo_products', $includes, true)) {
            /** @var DemoDataService $demoData */
            $demoData = app(DemoDataService::class);
            $demoResult = $demoData->importDemoData((string) $demoBusinessType, true);
            $result['demo_products_imported'] = (bool) ($demoResult['success'] ?? false);

            if ($result['demo_products_imported']) {
                app(ThemeDataImportService::class)->import(
                    $slug,
                    blocks: true,
                    menus: true,
                    settings: true,
                );
            }
        }

        return $result;
    }

    /**
     * @return array{path: string, filename: string}
     */
    public function export(string $slug, string $source = 'catalog'): array
    {
        $directory = $this->resolveExportDirectory($slug, $source);

        if ($directory === null || ! is_dir($directory)) {
            throw new Exception("Unable to export template \"{$slug}\" from source \"{$source}\".");
        }

        $zipPath = storage_path('app/template-exports/' . $slug . '-' . time() . '.zip');
        File::ensureDirectoryExists(dirname($zipPath));

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Unable to create export archive.');
        }

        $rootFolder = $slug;
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            $relativePath = $rootFolder . '/' . $file->getRelativePathname();
            $zip->addFile($file->getPathname(), $relativePath);
        }

        $zip->close();

        return [
            'path' => $zipPath,
            'filename' => $slug . '.zip',
        ];
    }

    protected function resolveExportDirectory(string $slug, string $source): ?string
    {
        if ($source === 'installed') {
            return $this->paths->resolve($slug);
        }

        $entry = $this->catalog->find($slug);

        return $entry['path'] ?? null;
    }

    protected function importHomepageLayout(string $slug, ?string $category = null): bool
    {
        $packagePath = $this->paths->resolve($slug, $category);
        $dataPath = $packagePath ? $packagePath . '/data/theme-data.json' : null;

        if ($dataPath === null || ! file_exists($dataPath)) {
            return false;
        }

        $themeData = json_decode((string) file_get_contents($dataPath), true);
        $layoutData = $themeData['homepage'] ?? null;

        if (! is_array($layoutData) || empty($layoutData['sections'])) {
            return false;
        }

        $layout = $this->layouts->saveDraft($layoutData, PageLayout::TYPE_HOMEPAGE, null);
        $this->layouts->publish($layout);

        return true;
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    protected function usesNativeHomepage(array $entry): bool
    {
        if (! empty($entry['native_homepage'])) {
            return true;
        }

        $themeJsonPath = rtrim((string) ($entry['path'] ?? ''), '/\\') . '/theme.json';

        if (! file_exists($themeJsonPath)) {
            return false;
        }

        $config = json_decode((string) file_get_contents($themeJsonPath), true);

        return is_array($config) && ! empty($config['native_homepage']);
    }
}
