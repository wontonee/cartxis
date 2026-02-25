<?php

namespace Cartxis\Sales\Services;

use Cartxis\Core\Services\SettingService;
use Cartxis\Sales\Models\Shipment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ShiprocketService
{
    private const TOKEN_CACHE_KEY = 'shiprocket:auth:token';

    public function __construct(
        protected SettingService $settingService
    ) {}

    public function isEnabled(): bool
    {
        return (bool) $this->settingService->get('shipping.shiprocket.enabled', false);
    }

    public function authenticateFromSettings(bool $force = false): string
    {
        $email = (string) $this->settingService->get('shipping.shiprocket.email', '');
        $password = (string) $this->settingService->get('shipping.shiprocket.password', '');

        if ($email === '' || $password === '') {
            throw new \RuntimeException('Shiprocket credentials are not configured.');
        }

        if (!$force) {
            $cached = Cache::get(self::TOKEN_CACHE_KEY);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        return $this->authenticate($email, $password);
    }

    public function authenticate(string $email, string $password): string
    {
        $response = Http::acceptJson()->post($this->apiBaseUrl() . '/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Shiprocket authentication failed: ' . $response->body());
        }

        $token = (string) (Arr::get($response->json(), 'token', ''));
        if ($token === '') {
            throw new \RuntimeException('Shiprocket authentication failed: token not received.');
        }

        Cache::put(self::TOKEN_CACHE_KEY, $token, now()->addMinutes(50));

        return $token;
    }

    public function createOrderForShipment(Shipment $shipment): array
    {
        if (!$this->isEnabled()) {
            throw new \RuntimeException('Shiprocket integration is disabled.');
        }

        $shipment->loadMissing([
            'order.items',
            'order.shippingAddress',
            'shipmentItems.orderItem',
        ]);

        $order = $shipment->order;
        if (!$order) {
            throw new \RuntimeException('Shipment order not found.');
        }

        $shippingAddress = $order->shippingAddress;
        if (!$shippingAddress) {
            throw new \RuntimeException('Order shipping address not found.');
        }

        $token = $this->authenticateFromSettings();
        $payload = $this->buildCreateOrderPayload($shipment);

        $response = Http::acceptJson()
            ->withToken($token)
            ->post($this->apiBaseUrl() . '/orders/create/adhoc', $payload);

        if ($response->status() === 401) {
            $token = $this->authenticateFromSettings(true);
            $response = Http::acceptJson()
                ->withToken($token)
                ->post($this->apiBaseUrl() . '/orders/create/adhoc', $payload);
        }

        if (!$response->successful()) {
            throw new \RuntimeException('Shiprocket create order failed: ' . $response->body());
        }

        $data = $response->json();

        $result = [
            'raw' => $data,
            'shiprocket_order_id' => $this->firstString([
                Arr::get($data, 'order_id'),
                Arr::get($data, 'data.order_id'),
                Arr::get($data, 'data.order.id'),
            ]),
            'shiprocket_shipment_id' => $this->firstString([
                Arr::get($data, 'shipment_id'),
                Arr::get($data, 'data.shipment_id'),
                Arr::get($data, 'data.shipment.id'),
            ]),
            'shiprocket_awb_code' => $this->firstString([
                Arr::get($data, 'awb_code'),
                Arr::get($data, 'data.awb_code'),
                Arr::get($data, 'data.shipment.awb_code'),
            ]),
            'shiprocket_courier_name' => $this->firstString([
                Arr::get($data, 'courier_name'),
                Arr::get($data, 'data.courier_name'),
                Arr::get($data, 'data.shipment.courier_name'),
            ]),
            'shiprocket_status' => $this->firstString([
                Arr::get($data, 'status'),
                Arr::get($data, 'data.status'),
                Arr::get($data, 'data.shipment.status'),
            ]),
        ];

        if (empty($result['shiprocket_order_id']) && empty($result['shiprocket_shipment_id'])) {
            $apiMessage = $this->firstString([
                Arr::get($data, 'message'),
                Arr::get($data, 'error.message'),
                Arr::get($data, 'errors.0.message'),
            ]) ?? 'Unknown Shiprocket response';

            throw new \RuntimeException('Shiprocket create order failed: ' . $apiMessage);
        }

        return $result;
    }

    public function fetchTrackingByAwb(string $awbCode): array
    {
        if ($awbCode === '') {
            throw new \RuntimeException('AWB code is required to fetch tracking.');
        }

        $token = $this->authenticateFromSettings();

        $response = Http::acceptJson()
            ->withToken($token)
            ->get($this->apiBaseUrl() . '/courier/track/awb/' . urlencode($awbCode));

        if ($response->status() === 401) {
            $token = $this->authenticateFromSettings(true);
            $response = Http::acceptJson()
                ->withToken($token)
                ->get($this->apiBaseUrl() . '/courier/track/awb/' . urlencode($awbCode));
        }

        if (!$response->successful()) {
            throw new \RuntimeException('Shiprocket tracking fetch failed: ' . $response->body());
        }

        $data = $response->json();

        return [
            'raw' => $data,
            'shiprocket_status' => $this->extractTrackingStatus($data),
            'shiprocket_courier_name' => $this->firstString([
                Arr::get($data, 'tracking_data.shipment_track.0.courier_name'),
                Arr::get($data, 'tracking_data.shipment_track.courier_name'),
                Arr::get($data, 'courier_name'),
            ]),
        ];
    }

    public function fetchPickupLocations(?string $token = null): array
    {
        $authToken = $token ?: $this->authenticateFromSettings();

        $data = $this->getWithToken($authToken, '/settings/company/pickup');

        $rows = Arr::get($data, 'data.shipping_address', Arr::get($data, 'data.data', Arr::get($data, 'data', [])));
        if (!is_array($rows)) {
            $rows = [];
        }

        $locations = collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row) {
                $code = $this->firstString([
                    Arr::get($row, 'pickup_location'),
                    Arr::get($row, 'pickup_code'),
                    Arr::get($row, 'warehouse_code'),
                    Arr::get($row, 'seller_name'),
                ]);

                $label = $this->firstString([
                    Arr::get($row, 'pickup_location'),
                    Arr::get($row, 'warehouse_name'),
                    Arr::get($row, 'seller_name'),
                ]);

                if (!$code || !$label) {
                    return null;
                }

                return [
                    'value' => $code,
                    'label' => $label,
                ];
            })
            ->filter()
            ->unique('value')
            ->values()
            ->all();

        return $locations;
    }

    public function fetchChannels(?string $token = null): array
    {
        $authToken = $token ?: $this->authenticateFromSettings();

        $data = $this->getWithToken($authToken, '/channels');

        $rows = Arr::get($data, 'data', []);
        if (!is_array($rows)) {
            $rows = Arr::get($data, 'data.data', []);
        }
        if (!is_array($rows)) {
            $rows = [];
        }

        $channels = collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row) {
                $id = $this->firstString([
                    Arr::get($row, 'id'),
                    Arr::get($row, 'channel_id'),
                ]);

                $name = $this->firstString([
                    Arr::get($row, 'name'),
                    Arr::get($row, 'channel_name'),
                ]);

                if (!$id || !$name) {
                    return null;
                }

                return [
                    'value' => $id,
                    'label' => $name,
                ];
            })
            ->filter()
            ->unique('value')
            ->values()
            ->all();

        return $channels;
    }

    protected function buildCreateOrderPayload(Shipment $shipment): array
    {
        $order = $shipment->order;
        $address = $order->shippingAddress;

        $itemsByOrderItemId = $shipment->shipmentItems
            ->keyBy('order_item_id');

        $orderItems = $order->items
            ->filter(fn ($orderItem) => $itemsByOrderItemId->has($orderItem->id))
            ->map(function ($orderItem) use ($itemsByOrderItemId) {
                $shipmentItem = $itemsByOrderItemId->get($orderItem->id);

                return [
                    'name' => (string) ($orderItem->product_name ?? 'Product'),
                    'sku' => (string) ($orderItem->product_sku ?? ('ITEM-' . $orderItem->id)),
                    'units' => (int) ($shipmentItem->quantity ?? 1),
                    'selling_price' => (float) ($orderItem->price ?? 0),
                    'discount' => (float) ($orderItem->discount_amount ?? 0),
                    'tax' => (float) ($orderItem->tax_amount ?? 0),
                ];
            })
            ->values()
            ->all();

        $fullName = trim((string) ($address->full_name ?? ''));
        $nameParts = preg_split('/\s+/', $fullName) ?: [];
        $firstName = (string) ($nameParts[0] ?? 'Customer');
        $lastName = (string) (count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '');

        $pickupLocation = (string) $this->settingService->get('shipping.shiprocket.pickup_location', 'Primary');

        $payload = [
            'order_id' => (string) $order->order_number,
            'order_date' => optional($order->created_at)->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
            'pickup_location' => $pickupLocation !== '' ? $pickupLocation : 'Primary',
            'billing_customer_name' => $firstName,
            'billing_last_name' => $lastName,
            'billing_address' => (string) ($address->address_line1 ?? ''),
            'billing_address_2' => (string) ($address->address_line2 ?? ''),
            'billing_city' => (string) ($address->city ?? ''),
            'billing_pincode' => (string) ($address->postal_code ?? ''),
            'billing_state' => (string) ($address->state ?? ''),
            'billing_country' => (string) ($address->country ?? 'India'),
            'billing_email' => (string) ($address->email ?: $order->customer_email),
            'billing_phone' => (string) ($address->phone ?: $order->customer_phone),
            'shipping_is_billing' => true,
            'order_items' => $orderItems,
            'payment_method' => $order->isPaid() ? 'Prepaid' : 'COD',
            'sub_total' => (float) ($order->subtotal ?? $order->total ?? 0),
            'length' => 10,
            'breadth' => 10,
            'height' => 10,
            'weight' => 0.5,
        ];

        $channelId = trim((string) $this->settingService->get('shipping.shiprocket.channel_id', ''));
        if ($channelId !== '') {
            $payload['channel_id'] = $channelId;
        }

        return $payload;
    }

    protected function extractTrackingStatus(array $trackingData): ?string
    {
        return $this->firstString([
            Arr::get($trackingData, 'tracking_data.shipment_status'),
            Arr::get($trackingData, 'tracking_data.shipment_track.0.current_status'),
            Arr::get($trackingData, 'tracking_data.shipment_track.current_status'),
            Arr::get($trackingData, 'status'),
        ]);
    }

    protected function firstString(array $values): ?string
    {
        foreach ($values as $value) {
            if (is_string($value)) {
                $trimmed = trim($value);
                if ($trimmed !== '') {
                    return $trimmed;
                }
            }

            if (is_numeric($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    protected function apiBaseUrl(): string
    {
        $configured = trim((string) $this->settingService->get('shipping.shiprocket.base_url', ''));
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        return 'https://apiv2.shiprocket.in/v1/external';
    }

    protected function getWithToken(string $token, string $path): array
    {
        $response = Http::acceptJson()
            ->withToken($token)
            ->get($this->apiBaseUrl() . $path);

        if ($response->status() === 401) {
            $newToken = $this->authenticateFromSettings(true);
            $response = Http::acceptJson()
                ->withToken($newToken)
                ->get($this->apiBaseUrl() . $path);
        }

        if (!$response->successful()) {
            throw new \RuntimeException('Shiprocket request failed for ' . $path . ': ' . $response->body());
        }

        $data = $response->json();
        return is_array($data) ? $data : [];
    }
}
