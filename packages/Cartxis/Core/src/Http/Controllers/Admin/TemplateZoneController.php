<?php

declare(strict_types=1);

namespace Cartxis\Core\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Core\Services\TemplateCatalogService;
use Cartxis\Core\Services\TemplateInstallService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TemplateZoneController extends Controller
{
    public function __construct(
        protected TemplateCatalogService $catalog,
        protected TemplateInstallService $installer,
    ) {}

    public function index(Request $request): Response
    {
        $type = $request->query('type');
        $category = $request->query('category');
        $search = trim((string) $request->query('search', ''));

        $templates = collect($this->catalog
            ->discover(
                is_string($type) && $type !== '' ? $type : null,
                is_string($category) && $category !== '' ? $category : null,
                $search !== '' ? $search : null,
            ))
            ->map(function (array $item) {
                $item['screenshot_url'] = $this->catalog->resolveScreenshotUrl($item);

                return $item;
            })
            ->values()
            ->all();

        $remoteProbe = $this->catalog->probeRemoteDirectory();
        $remoteThemeCount = collect($templates)->where('source', 'remote')->count();

        return Inertia::render('Admin/TemplateZone/Index', [
            'templates' => $templates,
            'categories' => $this->catalog->getCategories(),
            'types' => $this->catalog->getTypes(),
            'filters' => [
                'type' => $type,
                'category' => $category,
                'search' => $search,
            ],
            'remoteBrowseEnabled' => $this->catalog->isRemoteBrowseAvailable(),
            'remoteInstallEnabled' => $this->catalog->isRemoteInstallAvailable(),
            'directoryUrl' => $this->catalog->remoteDirectoryUrl(),
            'remoteProbe' => $remoteProbe,
            'remoteThemeCount' => $remoteThemeCount,
        ]);
    }

    public function show(string $slug): Response|RedirectResponse
    {
        $template = $this->catalog->find($slug);

        if ($template === null) {
            return redirect()
                ->route('admin.template-zone.index')
                ->with('error', 'Template not found in the catalog.');
        }

        $template['screenshot_url'] = $this->catalog->resolveScreenshotUrl($template);

        return Inertia::render('Admin/TemplateZone/Show', [
            'template' => $template,
            'categories' => $this->catalog->getCategories(),
            'remoteBrowseEnabled' => $this->catalog->isRemoteBrowseAvailable(),
            'remoteInstallEnabled' => $this->catalog->isRemoteInstallAvailable(),
        ]);
    }

    public function sync(): RedirectResponse
    {
        $this->catalog->clearRemoteCache();

        $errors = $this->catalog->validateCatalog();

        if (! empty($errors)) {
            return back()->with('error', 'Catalog validation failed: '.implode('; ', $errors));
        }

        $probe = $this->catalog->probeRemoteDirectory();
        $count = $this->catalog->discover()->count();

        $message = "Theme catalog synced. {$count} theme(s) available.";

        if ($this->catalog->isRemoteBrowseAvailable()) {
            if ($probe['ok']) {
                $message .= " Remote directory ({$this->catalog->remoteDirectoryUrl()}) returned {$probe['theme_count']} theme(s).";
            } else {
                return back()->with('error', 'Local catalog synced, but remote theme directory failed: '.($probe['error'] ?? 'Unknown error'));
            }
        }

        return back()->with('success', $message);
    }

    public function install(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'activate' => ['sometimes', 'boolean'],
            'import_layout' => ['sometimes', 'boolean'],
            'import_demo_products' => ['sometimes', 'boolean'],
        ]);

        try {
            $defaults = $this->catalog->isRemoteEntry($this->catalog->find($slug) ?? [])
                ? ['activate' => true, 'import_layout' => false, 'import_demo_products' => false]
                : [];

            $result = $this->installer->install($slug, [
                'activate' => (bool) ($validated['activate'] ?? $defaults['activate'] ?? false),
                'import_layout' => (bool) ($validated['import_layout'] ?? $defaults['import_layout'] ?? false),
                'import_demo_products' => (bool) ($validated['import_demo_products'] ?? $defaults['import_demo_products'] ?? false),
            ]);

            $message = "Template \"{$slug}\" installed successfully.";

            if ($result['activated']) {
                $message .= ' Theme activated.';
            }

            if ($result['layout_imported']) {
                $message .= ' Demo layout imported.';
            }

            if ($result['demo_products_imported']) {
                $message .= ' Demo products imported.';
            }

            if ($result['assets_rebuilt'] ?? false) {
                $message .= ' Storefront assets updated.';
            } elseif ($result['cache_cleared'] ?? false) {
                $message .= ' Caches cleared.';
            } else {
                $message .= ' Run npm run build on the server, then hard-refresh the storefront.';
            }

            return redirect()
                ->route('admin.themes.index')
                ->with('success', $message);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function download(Request $request, string $slug): BinaryFileResponse|RedirectResponse
    {
        $source = (string) $request->query('source', 'catalog');

        if (! in_array($source, ['catalog', 'installed'], true)) {
            return back()->with('error', 'Invalid download source.');
        }

        try {
            $export = $this->installer->export($slug, $source);

            return response()->download($export['path'], $export['filename'])->deleteFileAfterSend(true);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
