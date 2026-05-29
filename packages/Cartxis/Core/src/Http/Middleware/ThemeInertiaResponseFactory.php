<?php

namespace Cartxis\Core\Http\Middleware;

use Inertia\ResponseFactory;

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
     * to templates/cartxis-default/pages/Products/Index
     */
    protected function resolveComponent(string $component): string
    {
        if (str_starts_with($component, 'Theme::')) {
            $path = substr($component, 7);

            [$theme, $view] = explode('/', $path, 2);

            return "templates/{$theme}/pages/{$view}";
        }

        return parent::resolveComponent($component);
    }
}
