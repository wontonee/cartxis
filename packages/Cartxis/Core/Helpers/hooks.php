<?php

/**
 * Global hook helper functions
 *
 * WordPress-style action/filter wrappers that delegate to the HookService singleton.
 * These are auto-loaded via composer.json so they're available everywhere,
 * including theme hooks.php files.
 *
 * @package Cartxis\Core\Helpers
 */

if (!function_exists('add_action')) {
    /**
     * Register an action hook.
     *
     * @param string   $hook     The hook name
     * @param callable $callback The callback to execute
     * @param int      $priority Execution priority (lower = earlier)
     */
    function add_action(string $hook, callable $callback, int $priority = 10): void
    {
        app('vortex.hook')->addAction($hook, $callback, $priority);
    }
}

if (!function_exists('do_action')) {
    /**
     * Execute all callbacks registered for an action hook.
     *
     * @param string $hook The hook name
     * @param mixed  ...$args Arguments passed to callbacks
     */
    function do_action(string $hook, ...$args): void
    {
        app('vortex.hook')->doAction($hook, ...$args);
    }
}

if (!function_exists('add_filter')) {
    /**
     * Register a filter hook.
     *
     * @param string   $hook     The hook name
     * @param callable $callback The callback to execute
     * @param int      $priority Execution priority (lower = earlier)
     */
    function add_filter(string $hook, callable $callback, int $priority = 10): void
    {
        app('vortex.hook')->addFilter($hook, $callback, $priority);
    }
}

if (!function_exists('apply_filters')) {
    /**
     * Apply all callbacks registered for a filter hook.
     *
     * @param string $hook  The hook name
     * @param mixed  $value The value to filter
     * @param mixed  ...$args Additional arguments passed to callbacks
     * @return mixed The filtered value
     */
    function apply_filters(string $hook, mixed $value, ...$args): mixed
    {
        return app('vortex.hook')->applyFilter($hook, $value, ...$args);
    }
}

if (!function_exists('has_action')) {
    /**
     * Check if an action hook has any registered callbacks.
     *
     * @param string $hook The hook name
     * @return bool
     */
    function has_action(string $hook): bool
    {
        return app('vortex.hook')->hasAction($hook);
    }
}

if (!function_exists('has_filter')) {
    /**
     * Check if a filter hook has any registered callbacks.
     *
     * @param string $hook The hook name
     * @return bool
     */
    function has_filter(string $hook): bool
    {
        return app('vortex.hook')->hasFilter($hook);
    }
}

if (!function_exists('theme_setting')) {
    /**
     * Get a setting value for the active theme.
     *
     * @param string $key     Dot-notation setting key (e.g., 'layout.products_per_row')
     * @param mixed  $default Default value if setting not found
     * @return mixed
     */
    function theme_setting(string $key, mixed $default = null): mixed
    {
        $theme = app('vortex.theme')->active();

        if (!$theme) {
            return $default;
        }

        $settings = $theme->settings ?? [];

        // Settings are stored with flat keys (e.g., 'layout.container_width')
        // Try flat key lookup first, then fall back to dot-notation traversal
        return $settings[$key] ?? data_get($settings, $key, $default);
    }
}
