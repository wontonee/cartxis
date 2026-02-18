<?php

namespace Cartxis\Sales\Services;

use Cartxis\Core\Services\SettingService;
use Cartxis\Sales\Models\Shipment;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliveryService
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function isEnabled(): bool
    {
        return (bool) $this->settingService->get('shipping.delivery.enabled', false);
    }

    public function isConfigured(): bool
    {
        return $this->isEnabled()
            && trim((string) $this->settingService->get('shipping.delivery.api_token', '')) !== '';
    }

    public function createOrderForShipment(Shipment $shipment): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Delivery extension is not configured.');
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

        $address = $order->shippingAddress;
        if (!$address) {
            throw new \RuntimeException('Order shipping address not found.');
        }

        $payload = $this->buildCreatePayload($shipment);

        /** @var \Illuminate\Http\Client\Response $response */
        $data = $this->postCreateOrder($payload);

        $result = [
            'raw' => $data,
            'delivery_order_id' => $this->firstString([
                Arr::get($data, 'packages.0.order'),
                Arr::get($data, 'order_id'),
                Arr::get($data, 'order'),
                (string) ($order->order_number ?? ''),
            ]),
            'delivery_shipment_id' => $this->firstString([
                Arr::get($data, 'packages.0.refnum'),
                Arr::get($data, 'packages.0.waybill'),
                Arr::get($data, 'shipment_id'),
            ]),
            'delivery_awb_code' => $this->firstString([
                Arr::get($data, 'packages.0.waybill'),
                Arr::get($data, 'waybill'),
                Arr::get($data, 'awb'),
            ]),
            'delivery_courier_name' => 'Delhivery',
            'delivery_status' => $this->firstString([
                Arr::get($data, 'packages.0.status'),
                Arr::get($data, 'status'),
                Arr::get($data, 'rmk'),
                Arr::get($data, 'message'),
            ]),
        ];

        if (empty($result['delivery_awb_code'])) {
            $apiMessage = $this->extractCreateErrorMessage($data);

            if ($this->isGenericInternalError($apiMessage) && Arr::has($payload, 'shipments.0.channel_id')) {
                $retryPayload = $payload;
                unset($retryPayload['shipments'][0]['channel_id']);

                $retryData = $this->postCreateOrder($retryPayload);
                $retryResult = [
                    'raw' => $retryData,
                    'delivery_order_id' => $this->firstString([
                        Arr::get($retryData, 'packages.0.order'),
                        Arr::get($retryData, 'order_id'),
                        Arr::get($retryData, 'order'),
                        (string) ($order->order_number ?? ''),
                    ]),
                    'delivery_shipment_id' => $this->firstString([
                        Arr::get($retryData, 'packages.0.refnum'),
                        Arr::get($retryData, 'packages.0.waybill'),
                        Arr::get($retryData, 'shipment_id'),
                    ]),
                    'delivery_awb_code' => $this->firstString([
                        Arr::get($retryData, 'packages.0.waybill'),
                        Arr::get($retryData, 'waybill'),
                        Arr::get($retryData, 'awb'),
                    ]),
                    'delivery_courier_name' => 'Delhivery',
                    'delivery_status' => $this->firstString([
                        Arr::get($retryData, 'packages.0.status'),
                        Arr::get($retryData, 'status'),
                        Arr::get($retryData, 'rmk'),
                        Arr::get($retryData, 'message'),
                    ]),
                ];

                if (!empty($retryResult['delivery_awb_code'])) {
                    return $retryResult;
                }

                $data = $retryData;
                $result = $retryResult;
                $apiMessage = $this->extractCreateErrorMessage($retryData);
            }

            if ($this->isGenericInternalError($apiMessage)) {
                $detailedRemark = $this->firstString([
                    Arr::get($data, 'packages.0.remarks.0'),
                    Arr::get($data, 'packages.0.remarks'),
                ]);

                Log::warning('Delhivery create shipment generic internal error', [
                    'order_number' => $order->order_number,
                    'pickup_location' => (string) $this->settingService->get('shipping.delivery.pickup_location', ''),
                    'channel_id' => (string) $this->settingService->get('shipping.delivery.channel_id', ''),
                    'base_url' => $this->apiBaseUrl(),
                    'response' => $data,
                ]);

                $apiMessage = $detailedRemark
                    ?: 'Delhivery returned an internal error. Verify pickup location, channel id (if your account uses channels), and destination serviceability. If issue persists, contact client.support@delhivery.com with order number ' . ((string) ($order->order_number ?? 'N/A')) . '.';
            }

            if (stripos($apiMessage, 'ClientWarehouse matching query does not exist') !== false) {
                $configuredPickup = trim((string) $this->settingService->get('shipping.delivery.pickup_location', ''));
                $apiMessage = sprintf(
                    'Pickup location "%s" was not found in Delhivery. Update Delivery Settings with the exact warehouse name from Delhivery panel.',
                    $configuredPickup !== '' ? $configuredPickup : '(empty)'
                );
            }

            if ($apiMessage === null || trim($apiMessage) === '') {
                $apiMessage = 'Waybill/AWB not returned by Delivery API.';
            }

            throw new \RuntimeException('Delivery create shipment failed: ' . $apiMessage);
        }

        return $result;
    }

    protected function extractCreateErrorMessage(array $data): ?string
    {
        return $this->firstString([
                Arr::get($data, 'packages.0.remarks.0'),
                Arr::get($data, 'packages.0.remarks'),
                Arr::get($data, 'packages.0.status'),
                Arr::get($data, 'rmk'),
                Arr::get($data, 'message'),
                Arr::get($data, 'error'),
            ]);
    }

    protected function isGenericInternalError(?string $message): bool
    {
        if (!is_string($message) || trim($message) === '') {
            return false;
        }

        return str_contains(strtolower($message), 'internal error');
    }

    protected function postCreateOrder(array $payload): array
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::acceptJson()
            ->withHeaders([
                'Authorization' => 'Token ' . trim((string) $this->settingService->get('shipping.delivery.api_token', '')),
            ])
            ->asForm()
            ->post($this->apiBaseUrl() . '/api/cmu/create.json', [
                'format' => 'json',
                'data' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Delivery create shipment failed: ' . $response->body());
        }

        $json = $response->json();
        return is_array($json) ? $json : [];
    }

    public function fetchTrackingByAwb(string $awbCode): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Delivery extension is not configured.');
        }

        $awbCode = trim($awbCode);
        if ($awbCode === '') {
            throw new \RuntimeException('AWB code is required to fetch Delivery tracking.');
        }

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::acceptJson()
            ->withHeaders([
                'Authorization' => 'Token ' . trim((string) $this->settingService->get('shipping.delivery.api_token', '')),
                'Content-Type' => 'application/json',
            ])
            ->get($this->apiBaseUrl() . '/api/v1/packages/json/', [
                'waybill' => $awbCode,
                'ref_ids' => '',
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Delivery tracking fetch failed: ' . $response->body());
        }

        $data = $response->json();

        $status = $this->extractTrackingStatus($data);
        if ($status === null) {
            $apiMessage = $this->firstString([
                Arr::get($data, 'message'),
                Arr::get($data, 'error'),
                Arr::get($data, 'rmk'),
            ]);

            $status = $apiMessage ?: 'Tracking data received';
        }

        return [
            'raw' => $data,
            'delivery_status' => $status,
            'delivery_courier_name' => 'Delhivery',
        ];
    }

    public function fetchPickupLocations(?string $token = null, ?string $baseUrl = null): array
    {
        $apiToken = trim((string) ($token ?? $this->settingService->get('shipping.delivery.api_token', '')));
        if ($apiToken === '') {
            throw new \RuntimeException('Delivery API token is required to fetch pickup locations.');
        }

        $baseCandidates = $this->metadataBaseUrls($baseUrl);
        $paths = [
            '/api/backend/clientwarehouse/list/',
            '/api/backend/clientwarehouse/list',
            '/api/backend/clientwarehouse/get/',
            '/api/backend/clientwarehouse/get',
            '/api/backend/clientwarehouse/all/',
            '/api/backend/clientwarehouse/all',
            '/api/backend/clientwarehouse/',
            '/api/backend/clientwarehouse',
        ];

        $lastError = null;
        $attempts = [];

        foreach ($baseCandidates as $candidateBase) {
            foreach ($paths as $path) {
                foreach (['get', 'post'] as $method) {
                    /** @var \Illuminate\Http\Client\Response $response */
                    $response = Http::acceptJson()
                        ->withHeaders([
                            'Authorization' => 'Token ' . $apiToken,
                            'Content-Type' => 'application/json',
                        ])
                        ->$method($candidateBase . $path, $method === 'post' ? [] : []);

                    $attempts[] = strtoupper($method) . ' ' . $candidateBase . $path . ' -> ' . $response->status();

                    if (!$response->successful()) {
                        $lastError = sprintf(
                            'HTTP %s from %s%s',
                            $response->status(),
                            $candidateBase,
                            $path
                        );
                        continue;
                    }

                    $rows = $this->extractWarehouseRows($response->json());
                    if (count($rows) === 0) {
                        $lastError = 'Connected, but no pickup locations were returned.';
                        continue;
                    }

                    return $rows;
                }
            }
        }

        throw new \RuntimeException(
            'Unable to fetch Delivery pickup locations. ' . ($lastError ?: 'Please verify API token and warehouse setup in Delhivery panel.')
            . ' You can still enter pickup location manually.'
        );
    }

    protected function buildCreatePayload(Shipment $shipment): array
    {
        $order = $shipment->order;
        $address = $order->shippingAddress;

        $fullName = trim((string) ($address->full_name ?? ($order->customer_name ?? 'Customer')));
        $phone = (string) ($address->phone ?: ($order->customer_phone ?? '9999999999'));
        $pickupName = trim((string) $this->settingService->get('shipping.delivery.pickup_location', ''));
        if ($pickupName === '') {
            throw new \RuntimeException('Delivery pickup location is not configured. Please set it in Delivery Settings.');
        }

        $orderItems = $shipment->shipmentItems
            ->map(function ($shipmentItem) {
                $orderItem = $shipmentItem->orderItem;
                return [
                    'name' => (string) ($orderItem->product_name ?? 'Product'),
                    'qty' => (int) ($shipmentItem->quantity ?? 1),
                ];
            })
            ->values()
            ->all();

        $productsDescription = collect($orderItems)
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->implode(', ');

        $shipmentPayload = [
            'name' => $fullName !== '' ? $fullName : 'Customer',
            'add' => (string) ($address->address_line1 ?? ''),
            'add2' => (string) ($address->address_line2 ?? ''),
            'pin' => (string) ($address->postal_code ?? ''),
            'city' => (string) ($address->city ?? ''),
            'state' => (string) ($address->state ?? ''),
            'country' => (string) ($address->country ?? 'India'),
            'phone' => $phone,
            'order' => (string) ($order->order_number ?? $shipment->shipment_number),
            'payment_mode' => $order->isPaid() ? 'Prepaid' : 'COD',
            'total_amount' => (float) ($order->total ?? 0),
            'cod_amount' => $order->isPaid() ? 0 : (float) ($order->total ?? 0),
            'quantity' => (int) max(1, $shipment->shipmentItems->sum('quantity')),
            'products_desc' => $productsDescription,
            'weight' => (float) max(0.5, (float) $shipment->shipmentItems->sum('quantity') * 0.5),
            'shipment_height' => 10,
            'shipment_width' => 10,
            'shipment_length' => 10,
            'shipping_mode' => 'Surface',
            'address_type' => 'home',
            'pickup_location' => [
                'name' => $pickupName,
            ],
        ];

        return [
            'shipments' => [$shipmentPayload],
            'pickup_location' => [
                'name' => $pickupName,
            ],
        ];
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

    protected function extractTrackingStatus(array $data): ?string
    {
        return $this->firstString([
            Arr::get($data, 'packages.0.status'),
            Arr::get($data, 'packages.0.current_status'),
            Arr::get($data, 'shipment.status'),
            Arr::get($data, 'ShipmentData.0.Shipment.Status.Status'),
            Arr::get($data, 'ShipmentData.0.Shipment.Status.StatusType'),
            Arr::get($data, 'ShipmentData.0.Shipment.Scans.0.ScanDetail.Scan'),
            Arr::get($data, 'ShipmentData.0.Shipment.Scans.0.ScanDetail.Status'),
            Arr::get($data, 'status'),
        ]);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    protected function extractWarehouseRows(array $data): array
    {
        $rowCandidates = [
            Arr::get($data, 'data'),
            Arr::get($data, 'warehouses'),
            Arr::get($data, 'warehouse'),
            Arr::get($data, 'results'),
            Arr::get($data, 'pickup_locations'),
        ];

        $rows = collect($rowCandidates)
            ->first(fn ($candidate) => is_array($candidate));

        if (!is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row) {
                $value = $this->firstString([
                    Arr::get($row, 'name'),
                    Arr::get($row, 'warehouse_name'),
                    Arr::get($row, 'pickup_location'),
                    Arr::get($row, 'location_name'),
                ]);

                $label = $this->firstString([
                    Arr::get($row, 'name'),
                    Arr::get($row, 'warehouse_name'),
                    Arr::get($row, 'pickup_location'),
                    Arr::get($row, 'location_name'),
                ]);

                if (!$value || !$label) {
                    return null;
                }

                return [
                    'value' => $value,
                    'label' => $label,
                ];
            })
            ->filter()
            ->unique('value')
            ->values()
            ->all();
    }

    protected function apiBaseUrl(): string
    {
        $configured = trim((string) $this->settingService->get('shipping.delivery.base_url', ''));
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        return 'https://track.delhivery.com';
    }

    /**
     * @return array<int, string>
     */
    protected function metadataBaseUrls(?string $override = null): array
    {
        $configured = trim((string) ($override ?: $this->settingService->get('shipping.delivery.base_url', '')));
        $normalizedConfigured = $configured !== '' ? rtrim($configured, '/') : null;

        $candidates = collect([
            $normalizedConfigured,
            'https://track.delhivery.com',
        ])->filter(fn ($url) => is_string($url) && $url !== '');

        if ($normalizedConfigured && str_contains($normalizedConfigured, 'staging-express.delhivery.com')) {
            $candidates->push('https://staging-express.delhivery.com');
        }

        return $candidates->unique()->values()->all();
    }
}
