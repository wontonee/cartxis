<?php

namespace Vortex\Core\Services;

use Vortex\Core\Models\Extension;
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
     * Hook service instance.
     */
    protected HookService $hookService;

    /**
     * Menu service instance.
     */
    protected MenuService $menuService;

    public function __construct(HookService $hookService, MenuService $menuService)
    {
        $this->extensionsPath = base_path('extensions');
        $this->hookService = $hookService;
        $this->menuService = $menuService;
    }

    /**
     * Discover all available extensions from filesystem.
     */
    public function discover(): Collection
    {
        $extensions = collect();

        if (!File::isDirectory($this->extensionsPath)) {
            return $extensions;
        }

        $directories = File::directories($this->extensionsPath);

        foreach ($directories as $directory) {
            $manifestPath = $directory . '/extension.json';

            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true);
                
                if ($this->isValidManifest($manifest)) {
                    $extensions->push([
                        'path' => $directory,
                        'manifest' => $manifest,
                        'installed' => Extension::where('code', $manifest['code'])->exists(),
                    ]);
                }
            }
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

        $providerPath = $discovered['path'] . '/src/' . str_replace('\\', '/', $providerClass) . '.php';

        if (!File::exists($providerPath)) {
            return;
        }

        require_once $providerPath;

        if (class_exists($providerClass) && method_exists($providerClass, $method)) {
            $provider = new $providerClass(app());
            $provider->{$method}();
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
