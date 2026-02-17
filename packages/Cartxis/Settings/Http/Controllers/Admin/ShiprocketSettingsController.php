<?php

namespace Cartxis\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Core\Services\SettingService;
use Cartxis\Sales\Services\ShiprocketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiprocketSettingsController extends Controller
{
    public function __construct(
        protected SettingService $settingService,
        protected ShiprocketService $shiprocketService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Settings/ShippingMethods/ConfigureShiprocket', [
            'settings' => [
                'enabled' => (bool) $this->settingService->get('shipping.shiprocket.enabled', false),
                'email' => (string) $this->settingService->get('shipping.shiprocket.email', ''),
                'password' => (string) $this->settingService->get('shipping.shiprocket.password', ''),
                'pickup_location' => (string) $this->settingService->get('shipping.shiprocket.pickup_location', ''),
                'channel_id' => (string) $this->settingService->get('shipping.shiprocket.channel_id', ''),
            ],
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|max:255',
            'pickup_location' => 'nullable|string|max:255',
            'channel_id' => 'nullable|string|max:100',
        ]);

        if (($validated['enabled'] ?? false) && (empty($validated['email']) || empty($validated['password']))) {
            return back()->withErrors([
                'email' => 'Email is required when Shiprocket is enabled.',
                'password' => 'Password is required when Shiprocket is enabled.',
            ])->withInput();
        }

        $this->settingService->set('shipping.shiprocket.enabled', (bool) $validated['enabled'], 'boolean', 'shipping');
        $this->settingService->set('shipping.shiprocket.email', (string) ($validated['email'] ?? ''), 'string', 'shipping');
        $this->settingService->set('shipping.shiprocket.password', (string) ($validated['password'] ?? ''), 'string', 'shipping');
        $this->settingService->set('shipping.shiprocket.pickup_location', (string) ($validated['pickup_location'] ?? ''), 'string', 'shipping');
        $this->settingService->set('shipping.shiprocket.channel_id', (string) ($validated['channel_id'] ?? ''), 'string', 'shipping');

        return back()->with('success', 'Shiprocket settings saved successfully.');
    }

    public function testConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:255',
        ]);

        try {
            $token = $this->shiprocketService->authenticate(
                $validated['email'],
                $validated['password']
            );

            $pickupLocations = $this->shiprocketService->fetchPickupLocations($token);
            $channels = $this->shiprocketService->fetchChannels($token);

            return response()->json([
                'success' => true,
                'message' => 'Shiprocket connection successful.',
                'token_preview' => substr($token, 0, 12) . '...',
                'pickup_locations' => $pickupLocations,
                'channels' => $channels,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function fetchMetadata(): JsonResponse
    {
        try {
            if (!(bool) $this->settingService->get('shipping.shiprocket.enabled', false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shiprocket is disabled.',
                    'pickup_locations' => [],
                    'channels' => [],
                ], 422);
            }

            $pickupLocations = $this->shiprocketService->fetchPickupLocations();
            $channels = $this->shiprocketService->fetchChannels();

            return response()->json([
                'success' => true,
                'message' => 'Shiprocket metadata fetched successfully.',
                'pickup_locations' => $pickupLocations,
                'channels' => $channels,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'pickup_locations' => [],
                'channels' => [],
            ], 422);
        }
    }
}
