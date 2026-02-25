<?php

namespace Cartxis\Core\Services;

class HookService
{
    /**
     * Registered action hooks.
     */
    protected array $actions = [];

    /**
     * Registered filter hooks.
     */
    protected array $filters = [];

    /**
     * Register an action hook.
     */
    public function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        if (!isset($this->actions[$hook])) {
            $this->actions[$hook] = [];
        }

        if (!isset($this->actions[$hook][$priority])) {
            $this->actions[$hook][$priority] = [];
        }

        $this->actions[$hook][$priority][] = $callback;
    }

    /**
     * Execute an action hook.
     */
    public function doAction(string $hook, ...$args): void
    {
        if (!isset($this->actions[$hook])) {
            return;
        }

        ksort($this->actions[$hook]);

        foreach ($this->actions[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }

    /**
     * Register a filter hook.
     */
    public function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        if (!isset($this->filters[$hook])) {
            $this->filters[$hook] = [];
        }

        if (!isset($this->filters[$hook][$priority])) {
            $this->filters[$hook][$priority] = [];
        }

        $this->filters[$hook][$priority][] = $callback;
    }

    /**
     * Apply a filter hook.
     */
    public function applyFilter(string $hook, $value, ...$args)
    {
        if (!isset($this->filters[$hook])) {
            return $value;
        }

        ksort($this->filters[$hook]);

        foreach ($this->filters[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $value = call_user_func_array($callback, array_merge([$value], $args));
            }
        }

        return $value;
    }

    /**
     * Remove an action hook.
     */
    public function removeAction(string $hook, callable $callback, int $priority = 10): bool
    {
        if (!isset($this->actions[$hook][$priority])) {
            return false;
        }

        $key = array_search($callback, $this->actions[$hook][$priority], true);
        
        if ($key === false) {
            return false;
        }

        unset($this->actions[$hook][$priority][$key]);

        return true;
    }

    /**
     * Remove a filter hook.
     */
    public function removeFilter(string $hook, callable $callback, int $priority = 10): bool
    {
        if (!isset($this->filters[$hook][$priority])) {
            return false;
        }

        $key = array_search($callback, $this->filters[$hook][$priority], true);
        
        if ($key === false) {
            return false;
        }

        unset($this->filters[$hook][$priority][$key]);

        return true;
    }

    /**
     * Check if an action hook has callbacks.
     */
    public function hasAction(string $hook): bool
    {
        return isset($this->actions[$hook]) && !empty($this->actions[$hook]);
    }

    /**
     * Check if a filter hook has callbacks.
     */
    public function hasFilter(string $hook): bool
    {
        return isset($this->filters[$hook]) && !empty($this->filters[$hook]);
    }

    /**
     * Get all registered actions.
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Get all registered filters.
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
