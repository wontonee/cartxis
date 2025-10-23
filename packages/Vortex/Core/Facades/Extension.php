<?php

namespace Vortex\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Collection;
use Vortex\Core\Models\Extension as ExtensionModel;

/**
 * @method static Collection discover()
 * @method static ExtensionModel install(string $code)
 * @method static bool uninstall(string $code)
 * @method static ExtensionModel activate(string $code)
 * @method static ExtensionModel deactivate(string $code)
 * @method static Collection getInstalled()
 * @method static Collection getActive()
 * @method static ExtensionModel updateConfig(string $code, array $config)
 *
 * @see \Vortex\Core\Services\ExtensionService
 */
class Extension extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'vortex.extension';
    }
}
