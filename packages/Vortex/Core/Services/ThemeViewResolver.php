<?php

namespace Vortex\Core\Services;

use Illuminate\Support\Facades\File;

/**
 * Theme View Resolver
 * 
 * Resolves Inertia view paths with theme override support.
 * Allows themes to override core views without modifying core files.
 * 
 * @package Vortex\Core\Services
 */
class ThemeViewResolver
{
    /**
     * Active theme name
     */
    protected string $activeTheme;
    
    /**
     * Default fallback theme
     */
    protected string $defaultTheme = 'vortex-default';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activeTheme = config('theme.active', $this->defaultTheme);
    }
    
    /**
     * Resolve view path with theme override support
     * 
     * Priority:
     * 1. Active theme custom view
     * 2. Default theme view
     * 3. Core view (backward compatibility)
     * 
     * @param string $view Example: 'Products/Index', 'Cart/Index', 'Home'
     * @return string Resolved view path for Inertia
     * 
     * @example
     * // Returns: 'themes/vortex-default/pages/Products/Index'
     * $resolver->resolve('Products/Index');
     * 
     * // If custom theme overrides, returns: 'themes/vortex-christmas/pages/Products/Index'
     * $resolver->resolve('Products/Index'); // with active theme = vortex-christmas
     */
    public function resolve(string $view): string
    {
        // Check active theme first
        if ($this->hasThemeView($this->activeTheme, $view)) {
            return $this->formatThemePath($this->activeTheme, $view);
        }
        
        // Fallback to default theme
        if ($this->activeTheme !== $this->defaultTheme && $this->hasThemeView($this->defaultTheme, $view)) {
            return $this->formatThemePath($this->defaultTheme, $view);
        }
        
        // Final fallback to core (for backward compatibility during migration)
        return "Frontend/{$view}";
    }
    
    /**
     * Check if theme has custom view
     * 
     * @param string $theme Theme name
     * @param string $view View name
     * @return bool
     */
    protected function hasThemeView(string $theme, string $view): bool
    {
        $path = $this->getThemeViewPath($theme, $view);
        return File::exists($path);
    }
    
    /**
     * Get absolute path to theme view file
     * 
     * @param string $theme Theme name
     * @param string $view View name
     * @return string Absolute file path
     */
    protected function getThemeViewPath(string $theme, string $view): string
    {
        return base_path("themes/{$theme}/resources/views/pages/{$view}.vue");
    }
    
    /**
     * Format theme path for Inertia
     * 
     * Converts: themes/vortex-default/resources/views/pages/Products/Index.vue
     * To: themes/vortex-default/pages/Products/Index
     * 
     * @param string $theme Theme name
     * @param string $view View name
     * @return string Formatted path
     */
    protected function formatThemePath(string $theme, string $view): string
    {
        return "themes/{$theme}/pages/{$view}";
    }
    
    /**
     * Render Inertia view with theme resolution
     * 
     * @param string $view View name
     * @param array $props Props to pass to view
     * @return \Inertia\Response
     */
    public function inertia(string $view, array $props = [])
    {
        return \Inertia\Inertia::render($this->resolve($view), $props);
    }
    
    /**
     * Get active theme name
     * 
     * @return string
     */
    public function getActiveTheme(): string
    {
        return $this->activeTheme;
    }
    
    /**
     * Set active theme (for testing or runtime changes)
     * 
     * @param string $theme Theme name
     * @return self
     */
    public function setActiveTheme(string $theme): self
    {
        $this->activeTheme = $theme;
        return $this;
    }
    
    /**
     * Get all available themes
     * 
     * @return array Array of theme names
     */
    public function getAvailableThemes(): array
    {
        $themesPath = base_path('themes');
        
        if (!File::exists($themesPath)) {
            return [$this->defaultTheme];
        }
        
        $themes = [];
        $directories = File::directories($themesPath);
        
        foreach ($directories as $directory) {
            $themeName = basename($directory);
            
            // Verify it has the required structure
            if ($this->isValidTheme($themeName)) {
                $themes[] = $themeName;
            }
        }
        
        return $themes;
    }
    
    /**
     * Check if theme has valid structure
     * 
     * @param string $theme Theme name
     * @return bool
     */
    protected function isValidTheme(string $theme): bool
    {
        $requiredPaths = [
            base_path("themes/{$theme}/resources/views/pages"),
            base_path("themes/{$theme}/resources/views/components"),
            base_path("themes/{$theme}/resources/views/layouts"),
        ];
        
        foreach ($requiredPaths as $path) {
            if (!File::exists($path)) {
                return false;
            }
        }
        
        return true;
    }
}
