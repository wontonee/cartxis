<?php

declare(strict_types=1);

namespace Cartxis\API\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Cartxis\Core\Services\SettingService;

class TrackApiSync
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // Any authenticated API request indicates app connectivity.
        $this->settingService->set('system.api_sync_connected', '1', 'string', 'system');
        $this->settingService->set('system.api_sync_last_checked_at', now()->toDateTimeString(), 'string', 'system');

        return $next($request);
    }
}
