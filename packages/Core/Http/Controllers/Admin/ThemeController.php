<?php

namespace Packages\Core\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Packages\Core\Services\ThemeService;
use Packages\Core\Models\Theme;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

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
                'screenshot' => $theme->screenshot ? $theme->asset($theme->screenshot) : null,
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
     * Show theme settings page
     */
    public function settings(string $slug)
    {
        $theme = Theme::where('slug', $slug)->firstOrFail();

        if (!$theme->exists()) {
            return redirect()->route('admin.themes.index')
                ->with('error', 'Theme files not found.');
        }

        $schema = $this->themeService->getSettingsSchema($theme);
        $currentSettings = $theme->settings ?? [];

        return Inertia::render('Admin/Themes/Settings', [
            'theme' => [
                'id' => $theme->id,
                'name' => $theme->name,
                'slug' => $theme->slug,
                'description' => $theme->description,
                'version' => $theme->version,
                'is_active' => $theme->is_active,
            ],
            'schema' => $schema,
            'settings' => $currentSettings,
        ]);
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

            $this->themeService->install($fullPath);

            // Clean up uploaded zip
            unlink($fullPath);

            return back()->with('success', 'Theme installed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to install theme: ' . $e->getMessage());
        }
    }
}
