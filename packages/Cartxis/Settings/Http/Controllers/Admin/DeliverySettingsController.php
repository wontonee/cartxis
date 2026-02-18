<?php

namespace Cartxis\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Core\Services\SettingService;
use Cartxis\Sales\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliverySettingsController extends Controller
{
    public function __construct(
        protected SettingService $settingService,
        protected DeliveryService $deliveryService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Settings/ShippingMethods/ConfigureDelivery', [
            'settings' => [
                'enabled' => (bool) $this->settingService->get('shipping.delivery.enabled', false),
                'api_token' => (string) $this->settingService->get('shipping.delivery.api_token', ''),
                'pickup_location' => (string) $this->settingService->get('shipping.delivery.pickup_location', ''),
                'base_url' => (string) $this->settingService->get('shipping.delivery.base_url', 'https://track.delhivery.com'),
            ],
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'api_token' => 'nullable|string|max:500',
            'pickup_location' => 'nullable|string|max:255',
            'base_url' => 'nullable|url|max:255',
        ]);

        if ($validated['enabled'] ?? false) {
            $errors = [];

            if (empty($validated['api_token'])) {
                $errors['api_token'] = 'API token is required when Delivery extension is enabled.';
            }

            if (empty($validated['pickup_location'])) {
                $errors['pickup_location'] = 'Pickup location is required when Delivery extension is enabled.';
            }

            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }
        }

        $this->settingService->set('shipping.delivery.enabled', (bool) $validated['enabled'], 'boolean', 'shipping');
        $this->settingService->set('shipping.delivery.api_token', (string) ($validated['api_token'] ?? ''), 'string', 'shipping');
        $this->settingService->set('shipping.delivery.pickup_location', (string) ($validated['pickup_location'] ?? ''), 'string', 'shipping');
        $this->settingService->set('shipping.delivery.base_url', (string) ($validated['base_url'] ?? 'https://track.delhivery.com'), 'string', 'shipping');

        return back()->with('success', 'Delivery settings saved successfully.');
    }

    public function testConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_token' => 'required|string|max:500',
            'base_url' => 'nullable|url|max:255',
        ]);

        try {
            $pickupLocations = $this->deliveryService->fetchPickupLocations(
                (string) $validated['api_token'],
                (string) ($validated['base_url'] ?? '')
            );

            return response()->json([
                'success' => true,
                'message' => 'Delivery connection successful.',
                'pickup_locations' => $pickupLocations,
            ]);
        } catch (\Throwable $e) {
            $message = $e->getMessage();

            if (str_contains($message, 'HTTP 404')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connected, but pickup location fetch API is not available for this Delhivery account. Please enter pickup location manually.',
                    'pickup_locations' => [],
                    'manual_required' => true,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'pickup_locations' => [],
            ], 422);
        }
    }

    public function fetchMetadata(): JsonResponse
    {
        try {
            if (!(bool) $this->settingService->get('shipping.delivery.enabled', false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delivery is disabled.',
                    'pickup_locations' => [],
                ], 422);
            }

            if (trim((string) $this->settingService->get('shipping.delivery.api_token', '')) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Delivery API token is not configured.',
                    'pickup_locations' => [],
                ], 422);
            }

            $pickupLocations = $this->deliveryService->fetchPickupLocations();

            return response()->json([
                'success' => true,
                'message' => 'Delivery metadata fetched successfully.',
                'pickup_locations' => $pickupLocations,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'pickup_locations' => [],
            ], 422);
        }
    }
}
