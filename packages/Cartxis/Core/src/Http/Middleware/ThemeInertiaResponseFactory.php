<?php

namespace Cartxis\Core\Http\Middleware;

use Inertia\ResponseFactory;
use Illuminate\Support\Facades\File;

/**
 * Theme-aware Inertia Response Factory
 * 
 * Extends Inertia's ResponseFactory to resolve Theme:: prefixed paths
 */
class ThemeInertiaResponseFactory extends ResponseFactory
{
    /**
     * Resolve the component path
     * 
    * Converts Theme::cartxis-default/Products/Index
    * to themes/cartxis-default/resources/views/pages/Products/Index
     * 
     * @param string $component
     * @return string
     */
    protected function resolveComponent(string $component): string
    {
        // Check if component uses Theme:: prefix
        if (str_starts_with($component, 'Theme::')) {
            // Extract theme name and view path
            // Theme::cartxis-default/Products/Index
            $path = substr($component, 7); // Remove "Theme::"
            
            [$theme, $view] = explode('/', $path, 2);
            
            // Build the full path for Vite
            // themes/cartxis-default/resources/views/pages/Products/Index
            return "themes/{$theme}/resources/views/pages/{$view}";
        }
        
        // Default behavior for non-theme components
        return parent::resolveComponent($component);
    }
}
