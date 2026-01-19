<?php

namespace Cartxis\Core\Services;

use Cartxis\Core\Models\Extension;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Exception;

class ExtensionService
{
    /**
     * Directory where extensions are located.
     */
    protected string $extensionsPath;

    /**
     * Directory where bundled (first-party) extensions are located.
     */
    protected string $bundledExtensionsPath;

    /**
     * Hook service instance.
     */
    protected HookService $hookService;

    /**
     * Menu service instance.
     */
    protected MenuService $menuService;

    public function __construct(HookService $hookService, MenuService $menuService)
    {
        $this->extensionsPath = config('core.extensions_path', base_path('extensions'));
        $this->bundledExtensionsPath = config('core.bundled_extensions_path', base_path('packages/Cartxis'));
        $this->hookService = $hookService;
        $this->menuService = $menuService;
    }

    /**
     * Discover all available extensions from filesystem.
     */
    public function discover(): Collection
    {
        $extensions = collect();

        // 1) User-installed extensions (filesystem: /extensions)
        $extensions = $extensions->merge($this->discoverFromPath($this->extensionsPath, 'filesystem'));

        // 2) Bundled first-party extensions (packages: /packages/Cartxis/*)
        $extensions = $extensions->merge($this->discoverFromPath($this->bundledExtensionsPath, 'bundled'));

        return $extensions;
    }

    /**
     * Discover extensions from a directory.
     *
     * - filesystem extensions: expected at <dir>/<extension>/extension.json
     * - bundled extensions: expected at <dir>/<package>/extension.json
     */
    protected function discoverFromPath(string $path, string $source): Collection
    {
        $extensions = collect();

        if (!File::isDirectory($path)) {
            return $extensions;
        }

        foreach (File::directories($path) as $directory) {
            $manifestPath = $directory . '/extension.json';

            if (!File::exists($manifestPath)) {
                continue;
            }

            $manifest = json_decode(File::get($manifestPath), true);

            if (!$this->isValidManifest($manifest)) {
                continue;
            }

            $extensions->push([
                'path' => $directory,
                'source' => $source,
                'manifest' => $manifest,
                'installed' => Extension::where('code', $manifest['code'])->exists(),
            ]);
        }

        return $extensions;
    }

    /**
     * Validate extension manifest.
     */
    protected function isValidManifest(array $manifest): bool
    {
        return isset($manifest['code']) 
            && isset($manifest['name']) 
            && isset($manifest['version']);
    }

    /**
     * Install an extension.
     */
    public function install(string $code): Extension
    {
        $extension = Extension::where('code', $code)->first();

        if ($extension && $extension->installed) {
            throw new Exception("Extension {$code} is already installed.");
        }

        $discovered = $this->discover()->firstWhere('manifest.code', $code);

        if (!$discovered) {
            throw new Exception("Extension {$code} not found.");
        }

        $manifest = $discovered['manifest'];

        if ($extension) {
            $extension->update([
                'installed' => true,
                'installed_at' => now(),
            ]);
        } else {
            $extension = Extension::create([
                'code' => $manifest['code'],
                'name' => $manifest['name'],
                'description' => $manifest['description'] ?? null,
                'version' => $manifest['version'],
                'author' => $manifest['author'] ?? null,
                'author_url' => $manifest['author_url'] ?? null,
                'icon' => $manifest['icon'] ?? null,
                'requires' => $manifest['requires'] ?? [],
                'config' => $manifest['config'] ?? [],
                'installed' => true,
                'active' => false,
                'installed_at' => now(),
            ]);
        }

        // Run install hook
        $this->runExtensionMethod($code, 'install');

        $this->hookService->doAction('extension.installed', $extension);

        return $extension;
    }

    /**
     * Uninstall an extension.
     */
    public function uninstall(string $code): bool
    {
        $extension = Extension::where('code', $code)->first();

        if (!$extension || !$extension->installed) {
            throw new Exception("Extension {$code} is not installed.");
        }

        if ($extension->active) {
            $this->deactivate($code);
        }

        // Run uninstall hook
        $this->runExtensionMethod($code, 'uninstall');

        // Remove extension menus
        $this->menuService->removeByExtension($code);

        $this->hookService->doAction('extension.uninstalled', $extension);

        return $extension->delete();
    }

    /**
     * Activate an extension.
     */
    public function activate(string $code): Extension
    {
        $extension = Extension::where('code', $code)->firstOrFail();

        if (!$extension->canActivate()) {
            throw new Exception("Extension {$code} cannot be activated.");
        }

        $extension->update(['active' => true]);

        // Run activate hook
        $this->runExtensionMethod($code, 'activate');

        $this->hookService->doAction('extension.activated', $extension);

        return $extension;
    }

    /**
     * Deactivate an extension.
     */
    public function deactivate(string $code): Extension
    {
        $extension = Extension::where('code', $code)->firstOrFail();

        if (!$extension->canDeactivate()) {
            throw new Exception("Extension {$code} cannot be deactivated.");
        }

        $extension->update(['active' => false]);

        // Run deactivate hook
        $this->runExtensionMethod($code, 'deactivate');

        $this->hookService->doAction('extension.deactivated', $extension);

        return $extension;
    }

    /**
     * Run extension lifecycle method.
     */
    protected function runExtensionMethod(string $code, string $method): void
    {
        $discovered = $this->discover()->firstWhere('manifest.code', $code);

        if (!$discovered) {
            return;
        }

        $providerClass = $discovered['manifest']['provider'] ?? null;

        if (!$providerClass) {
            return;
        }

        $this->requireProviderIfNeeded($discovered);

        if (class_exists($providerClass) && method_exists($providerClass, $method)) {
            $provider = new $providerClass(app());
            $provider->{$method}();
        }
    }

    /**
     * Require provider file if the provider class is not already autoloadable.
     *
     * Supports:
     * - manifest.provider_file: explicit relative file path
     * - legacy behavior: derive file path from class name under <path>/src/
     */
    protected function requireProviderIfNeeded(array $discovered): void
    {
        $providerClass = $discovered['manifest']['provider'] ?? null;
        if (!$providerClass || class_exists($providerClass)) {
            return;
        }

        $providerFile = $discovered['manifest']['provider_file'] ?? null;
        if ($providerFile) {
            $providerPath = rtrim($discovered['path'], '/\\') . '/' . ltrim($providerFile, '/\\');
            if (File::exists($providerPath)) {
                require_once $providerPath;
            }

            return;
        }

        // Legacy fallback
        $legacyProviderPath = $discovered['path'] . '/src/' . str_replace('\\', '/', $providerClass) . '.php';
        if (File::exists($legacyProviderPath)) {
            require_once $legacyProviderPath;
        }
    }

    /**
     * Get all installed extensions.
     */
    public function getInstalled(): Collection
    {
        return Extension::installed()->get();
    }

    /**
     * Get all active extensions.
     */
    public function getActive(): Collection
    {
        return Extension::active()->get();
    }

    /**
     * Update extension configuration.
     */
    public function updateConfig(string $code, array $config): Extension
    {
        $extension = Extension::where('code', $code)->firstOrFail();
        $extension->config = array_merge($extension->config ?? [], $config);
        $extension->save();

        return $extension;
    }
}
