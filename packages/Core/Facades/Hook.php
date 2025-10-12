<?php

namespace Vortex\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void addAction(string $hook, callable $callback, int $priority = 10)
 * @method static void doAction(string $hook, ...$args)
 * @method static void addFilter(string $hook, callable $callback, int $priority = 10)
 * @method static mixed applyFilter(string $hook, $value, ...$args)
 * @method static bool removeAction(string $hook, callable $callback, int $priority = 10)
 * @method static bool removeFilter(string $hook, callable $callback, int $priority = 10)
 * @method static bool hasAction(string $hook)
 * @method static bool hasFilter(string $hook)
 * @method static array getActions()
 * @method static array getFilters()
 *
 * @see \Vortex\Core\Services\HookService
 */
class Hook extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'vortex.hook';
    }
}
