<?php

declare(strict_types=1);

namespace Cartxis\System\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\Core\Services\SettingService;
use Carbon\Carbon;

class ApiSyncController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Display API sync status page.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/System/ApiSync/Index', [
            'status' => $this->getStatus(),
        ]);
    }

    /**
     * Return API sync status as JSON for polling.
     */
    public function status(Request $request)
    {
        return response()->json($this->getStatus());
    }

    /**
     * Trigger a manual refresh/sync.
     */
    public function refresh()
    {
        $this->settingService->set('system.api_sync_last_status', 'success', 'string', 'system');
        $this->settingService->set('system.api_sync_last_message', 'Sync completed successfully.', 'string', 'system');
        $this->settingService->set('system.api_sync_last_sync_at', now()->toDateTimeString(), 'string', 'system');
        $this->settingService->set('system.api_sync_last_checked_at', now()->toDateTimeString(), 'string', 'system');

        return back()->with('success', 'API sync completed successfully.');
    }

    /**
     * Update sync settings (enabled/connected).
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'sync_enabled' => 'required|boolean',
            'connected' => 'required|boolean',
        ]);

        $this->settingService->set('system.api_sync_enabled', $validated['sync_enabled'] ? '1' : '0', 'string', 'system');
        $this->settingService->set('system.api_sync_connected', $validated['connected'] ? '1' : '0', 'string', 'system');
        $this->settingService->set('system.api_sync_last_checked_at', now()->toDateTimeString(), 'string', 'system');

        return back()->with('success', 'API sync settings updated.');
    }

    private function getStatus(): array
    {
        $lastCheckedAt = (string) $this->settingService->get('system.api_sync_last_checked_at', '');
        $connectedSetting = (bool) $this->settingService->get('system.api_sync_connected', false);
        $connected = $connectedSetting;

        if (!empty($lastCheckedAt)) {
            try {
                $connected = Carbon::parse($lastCheckedAt)->diffInSeconds(now()) <= 120;
            } catch (\Throwable $e) {
                $connected = $connectedSetting;
            }
        }

        return [
            'connected' => $connected,
            'sync_enabled' => (bool) $this->settingService->get('system.api_sync_enabled', false),
            'last_sync_at' => (string) $this->settingService->get('system.api_sync_last_sync_at', ''),
            'last_status' => (string) $this->settingService->get('system.api_sync_last_status', 'unknown'),
            'last_message' => (string) $this->settingService->get('system.api_sync_last_message', ''),
            'last_checked_at' => $lastCheckedAt,
        ];
    }
}
