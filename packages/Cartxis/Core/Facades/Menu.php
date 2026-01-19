<?php

namespace Cartxis\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Collection;
use Cartxis\Core\Models\MenuItem;

/**
 * @method static Collection buildTree(string $location = 'admin')
 * @method static MenuItem register(array $data)
 * @method static bool unregister(string $key)
 * @method static bool update(string $key, array $data)
 * @method static MenuItem|null getByKey(string $key)
 * @method static Collection getByExtension(string $extensionCode)
 * @method static bool removeByExtension(string $extensionCode)
 * @method static void reorder(array $items)
 * @method static Collection getFlatList(string $location = 'admin')
 *
 * @see \Cartxis\Core\Services\MenuService
 */
class Menu extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'vortex.menu';
    }
}
