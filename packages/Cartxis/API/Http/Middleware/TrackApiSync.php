<?php

declare(strict_types=1);

namespace Cartxis\API\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Cartxis\Core\Services\SettingService;
use Throwable;

class TrackApiSync
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // Connectivity tracking must never break authenticated API calls
        // (e.g. settings write / cache failures on demo).
        try {
            $this->settingService->set('system.api_sync_connected', '1', 'string', 'system');
            $this->settingService->set('system.api_sync_last_checked_at', now()->toDateTimeString(), 'string', 'system');
        } catch (Throwable $e) {
            Log::warning('TrackApiSync settings update failed', [
                'message' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
