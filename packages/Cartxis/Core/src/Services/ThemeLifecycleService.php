<?php

declare(strict_types=1);

namespace Cartxis\Core\Services;

use Cartxis\Core\Models\Theme;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class ThemeLifecycleService
{
    public function __construct(
        protected ThemeService $themes,
        protected ThemeAssetBuildService $assetBuild,
        protected ThemeViewResolver $viewResolver,
    ) {}

    /**
     * Republish assets, clear caches, and rebuild Vite bundles after install/activate.
     *
     * @return array{cache_cleared: bool, assets_published: bool, assets_rebuilt: bool}
     */
    public function finalize(string $slug, bool $rebuildAssets = true): array
    {
        $this->themes->discover();

        $theme = Theme::where('slug', $slug)->first();
        if ($theme) {
            config(['theme.active' => $theme->slug]);
            $this->viewResolver->setActiveTheme($theme->slug);
        }

        cache()->forget('active_theme');

        $cacheCleared = $this->clearCaches();
        $assetsRebuilt = false;

        if ($rebuildAssets && $this->assetBuild->shouldRebuild()) {
            $assetsRebuilt = $this->assetBuild->rebuild(background: false);
        }

        return [
            'cache_cleared' => $cacheCleared,
            'assets_published' => true,
            'assets_rebuilt' => $assetsRebuilt,
        ];
    }

    protected function clearCaches(): bool
    {
        try {
            Artisan::call('optimize:clear');

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
