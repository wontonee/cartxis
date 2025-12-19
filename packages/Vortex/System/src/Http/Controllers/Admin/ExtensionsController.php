<?php

declare(strict_types=1);

namespace Vortex\System\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Core\Models\Extension;
use Vortex\Core\Services\ExtensionService;

class ExtensionsController
{
    public function index(ExtensionService $extensions): Response
    {
        $discovered = $extensions->discover();
        $dbExtensions = Extension::query()->get()->keyBy('code');

        $items = $discovered
            ->map(function (array $item) use ($dbExtensions): array {
                $manifest = (array) ($item['manifest'] ?? []);
                $code = (string) ($manifest['code'] ?? '');

                /** @var Extension|null $db */
                $db = $code !== '' ? $dbExtensions->get($code) : null;

                return [
                    'code' => $code,
                    'name' => (string) ($manifest['name'] ?? ''),
                    'description' => $manifest['description'] ?? null,
                    'version' => (string) ($manifest['version'] ?? ''),
                    'author' => $manifest['author'] ?? null,
                    'author_url' => $manifest['author_url'] ?? null,
                    'source' => $item['source'] ?? null,
                    'path' => $item['path'] ?? null,

                    'installed' => (bool) ($db?->installed ?? false),
                    'active' => (bool) ($db?->active ?? false),
                    'installed_at' => $db?->installed_at,
                    'has_db_row' => (bool) $db,
                ];
            })
            ->sortBy(fn (array $item) => [$item['source'] ?? '', $item['name'] ?? '', $item['code'] ?? ''])
            ->values();

        return Inertia::render('Admin/System/Extensions/Index', [
            'extensions' => $items,
        ]);
    }

    public function sync(ExtensionService $extensions): RedirectResponse
    {
        $discovered = $extensions->discover();

        if ($discovered->isEmpty()) {
            return back()->with('info', 'No extensions discovered.');
        }

        $created = 0;
        $updated = 0;

        foreach ($discovered as $item) {
            $manifest = (array) ($item['manifest'] ?? []);
            $code = $manifest['code'] ?? null;
            if (!$code) {
                continue;
            }

            $extension = Extension::firstOrNew(['code' => $code]);
            $isNew = !$extension->exists;

            $extension->name = $manifest['name'] ?? $extension->name;
            $extension->description = $manifest['description'] ?? $extension->description;
            $extension->version = $manifest['version'] ?? $extension->version;
            $extension->author = $manifest['author'] ?? $extension->author;
            $extension->author_url = $manifest['author_url'] ?? $extension->author_url;
            $extension->icon = $manifest['icon'] ?? $extension->icon;
            $extension->requires = $manifest['requires'] ?? ($manifest['require'] ?? $extension->requires);

            if ($isNew) {
                $isBundled = ($item['source'] ?? null) === 'bundled';

                $extension->installed = $isBundled;
                $extension->installed_at = $isBundled ? now() : null;
                $extension->active = $isBundled;
            }

            $extension->save();

            if ($isNew) {
                $created++;
            } else {
                $updated++;
            }
        }

        return back()->with('success', "Extensions synced. Created: {$created}. Updated: {$updated}.");
    }

    public function install(string $code, ExtensionService $extensions): RedirectResponse
    {
        try {
            $extensions->install($code);
            return back()->with('success', "Extension {$code} installed.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function uninstall(string $code, ExtensionService $extensions): RedirectResponse
    {
        try {
            $extensions->uninstall($code);
            return back()->with('success', "Extension {$code} uninstalled.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function activate(string $code, ExtensionService $extensions): RedirectResponse
    {
        try {
            $extensions->activate($code);
            return back()->with('success', "Extension {$code} activated.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function deactivate(string $code, ExtensionService $extensions): RedirectResponse
    {
        try {
            $extensions->deactivate($code);
            return back()->with('success', "Extension {$code} deactivated.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
