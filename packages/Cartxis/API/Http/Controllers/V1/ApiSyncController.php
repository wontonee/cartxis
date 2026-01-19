<?php

declare(strict_types=1);

namespace Cartxis\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cartxis\Core\Services\SettingService;
use Carbon\Carbon;

class ApiSyncController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Heartbeat endpoint called by mobile app to report connectivity/sync status.
     */
    public function heartbeat(Request $request)
    {
        $validated = $request->validate([
            'connected' => 'required|boolean',
            'sync_enabled' => 'sometimes|boolean',
            'last_status' => 'sometimes|string',
            'last_message' => 'sometimes|string',
            'last_sync_at' => 'sometimes|date',
        ]);

        $this->settingService->set('system.api_sync_connected', $validated['connected'] ? '1' : '0', 'string', 'system');
        $this->settingService->set('system.api_sync_last_checked_at', now()->toDateTimeString(), 'string', 'system');

        if (array_key_exists('sync_enabled', $validated)) {
            $this->settingService->set('system.api_sync_enabled', $validated['sync_enabled'] ? '1' : '0', 'string', 'system');
        }

        if (array_key_exists('last_status', $validated)) {
            $this->settingService->set('system.api_sync_last_status', $validated['last_status'], 'string', 'system');
        }

        if (array_key_exists('last_message', $validated)) {
            $this->settingService->set('system.api_sync_last_message', $validated['last_message'], 'string', 'system');
        }

        if (array_key_exists('last_sync_at', $validated)) {
            $this->settingService->set('system.api_sync_last_sync_at', $validated['last_sync_at'], 'string', 'system');
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Return current sync status to the mobile app.
     */
    public function status()
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

        return response()->json([
            'connected' => $connected,
            'sync_enabled' => (bool) $this->settingService->get('system.api_sync_enabled', false),
            'last_sync_at' => (string) $this->settingService->get('system.api_sync_last_sync_at', ''),
            'last_status' => (string) $this->settingService->get('system.api_sync_last_status', 'unknown'),
            'last_message' => (string) $this->settingService->get('system.api_sync_last_message', ''),
            'last_checked_at' => $lastCheckedAt,
        ]);
    }
}
