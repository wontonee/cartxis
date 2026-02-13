<?php

namespace Cartxis\PayPal\Services;

use Cartxis\Core\Contracts\PaymentGatewayInterface;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Shop\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * PayPal Payment Gateway Implementation
 * Uses PayPal REST API v2 directly
 */
class PayPalGateway implements PaymentGatewayInterface
{
    protected ?PaymentMethod $paymentMethod = null;
    protected ?Client $httpClient = null;
    protected ?string $accessToken = null;

    /**
     * Get payment method configuration from database.
     */
    protected function getPaymentMethod(): ?PaymentMethod
    {
        if (!$this->paymentMethod) {
            $this->paymentMethod = PaymentMethod::where('code', 'paypal')
                ->where('is_active', true)
                ->first();
        }

        return $this->paymentMethod;
    }

    /**
     * Get configuration value.
     */
    protected function getConfig(string $key, mixed $default = null): mixed
    {
        $method = $this->getPaymentMethod();
        return $method?->getConfigValue($key, $default);
    }

    /**
     * Get PayPal API base URL.
     */
    protected function getApiUrl(): string
    {
        $mode = $this->getConfig('mode', 'sandbox');
        return $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Get HTTP client instance.
     */
    protected function getHttpClient(): Client
    {
        if (!$this->httpClient) {
            $this->httpClient = new Client([
                'base_uri' => $this->getApiUrl(),
                'timeout' => 30,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);
        }

        return $this->httpClient;
    }

    /**
     * Get OAuth access token from PayPal.
     */
    protected function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $clientId = $this->getConfig('client_id');
        $clientSecret = $this->getConfig('client_secret');

        if (!$clientId || !$clientSecret) {
            throw new \Exception('PayPal API credentials not configured');
        }

        try {
            $client = $this->getHttpClient();
            $response = $client->post('/v1/oauth2/token', [
                'auth' => [$clientId, $clientSecret],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $this->accessToken = $data['access_token'];

            return $this->accessToken;
        } catch (\Exception $e) {
            Log::error('PayPal: Failed to get access token', [
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to authenticate with PayPal: ' . $e->getMessage());
        }
    }

    /**
     * Make authenticated API request to PayPal.
     */
    protected function apiRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $client = $this->getHttpClient();
            $token = $this->getAccessToken();

            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ];

            if (!empty($data)) {
                $options['json'] = $data;
            }

            $response = $client->request($method, $endpoint, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $responseBody = null;

            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $responseBody = (string) $e->getResponse()->getBody();
            }

            Log::error('PayPal API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'response' => $responseBody,
            ]);

            throw $e;
        }
    }

    /**
     * Get the gateway code.
     */
    public function getCode(): string
    {
        return 'paypal';
    }

    /**
     * Get the gateway display name.
     */
    public function getName(): string
    {
        return 'PayPal';
    }

    /**
     * Check if this gateway supports the given payment method.
     */
    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'paypal';
    }

    /**
     * Process payment for an order.
     * Creates a PayPal Order and returns payment data.
     */
    public function processPayment(Order $order, array $data = [])
    {
        Log::info('PayPalGateway: processPayment called', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        try {
            // Get shipping address
            $shippingAddress = $order->shippingAddress;

            $currency = strtoupper($order->currency_code ?? 'USD');
            $parseAmount = static function ($value): float {
                if (is_null($value)) {
                    return 0.0;
                }

                if (is_numeric($value)) {
                    return (float) $value;
                }

                $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);
                return is_numeric($normalized) ? (float) $normalized : 0.0;
            };

            $subTotal = $parseAmount($order->subtotal ?? ($order->sub_total ?? 0));
            $shippingAmount = $parseAmount($order->shipping_cost ?? ($order->shipping_amount ?? 0));
            $taxAmount = $parseAmount($order->tax ?? ($order->tax_amount ?? 0));
            $discountRaw = $parseAmount($order->discount ?? ($order->discount_amount ?? 0));
            $discountAmount = max(0, abs($discountRaw));

            $calculatedTotal = $subTotal + $shippingAmount + $taxAmount - $discountAmount;
            $calculatedTotal = max(0, $calculatedTotal);

            $grandTotal = $parseAmount($order->total ?? ($order->grand_total ?? $calculatedTotal));
            $grandTotal = max(0, $grandTotal);
            if ($grandTotal <= 0 && $calculatedTotal > 0) {
                $grandTotal = $calculatedTotal;
            }

            if ($grandTotal <= 0) {
                throw new \Exception('PayPal amount must be greater than zero.');
            }

            $formatAmount = static fn ($value) => number_format($value, 2, '.', '');

            // Create PayPal Order
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $order->order_number,
                        'description' => 'Order #' . $order->order_number,
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => $formatAmount($grandTotal),
                        ],
                    ],
                ],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => route('paypal.callback', ['order' => $order->id]),
                    'cancel_url' => route('shop.checkout.index'),
                ],
            ];

            if ($shippingAddress) {
                $countryCode = $this->normalizeCountryCode($shippingAddress->country_code ?? $shippingAddress->country ?? '');

                if ($countryCode) {
                    $orderData['purchase_units'][0]['shipping'] = [
                        'name' => [
                            'full_name' => trim($shippingAddress->first_name . ' ' . $shippingAddress->last_name),
                        ],
                        'address' => [
                            'address_line_1' => $shippingAddress->address_line_1,
                            'address_line_2' => $shippingAddress->address_line_2 ?? '',
                            'admin_area_2' => $shippingAddress->city,
                            'admin_area_1' => $shippingAddress->state,
                            'postal_code' => $shippingAddress->postcode,
                            'country_code' => $countryCode,
                        ],
                    ];
                }
            }

            Log::info('PayPalGateway: Creating PayPal order', [
                'order_id' => $order->id,
                'amount' => $order->grand_total,
            ]);

            // Execute API request
            $response = $this->apiRequest('POST', '/v2/checkout/orders', $orderData);

            Log::info('PayPalGateway: PayPal order created', [
                'order_id' => $order->id,
                'paypal_order_id' => $response['id'],
                'status' => $response['status'],
            ]);

            // Get approval URL
            $approveUrl = null;
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approveUrl = $link['href'];
                    break;
                }
            }

            if (!$approveUrl) {
                throw new \Exception('PayPal approval URL not found');
            }

            // Store PayPal order ID in order metadata
            $order->update([
                'payment_gateway_data' => [
                    'paypal_order_id' => $response['id'],
                    'status' => $response['status'],
                ],
            ]);

            // Return payment data for frontend redirect
            return [
                'success' => true,
                'gateway_type' => 'redirect',
                'redirect_url' => $approveUrl,
                'payment_data' => [
                    'gateway_code' => $this->getCode(),
                    'paypal_order_id' => $response['id'],
                    'approve_url' => $approveUrl,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('PayPalGateway: Payment processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('PayPal payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment callback from PayPal.
     */
    public function handleCallback(array $data): array
    {
        Log::info('PayPalGateway: handleCallback called', $data);

        try {
            $paypalOrderId = $data['token'] ?? null;
            $orderId = $data['order_id'] ?? null;

            if (!$paypalOrderId || !$orderId) {
                throw new \Exception('Missing PayPal order ID or order ID');
            }

            $order = Order::find($orderId);
            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Capture the payment
            $response = $this->apiRequest('POST', "/v2/checkout/orders/{$paypalOrderId}/capture", []);

            Log::info('PayPalGateway: Payment captured', [
                'order_id' => $order->id,
                'paypal_order_id' => $paypalOrderId,
                'status' => $response['status'],
            ]);

            if ($response['status'] === 'COMPLETED') {
                // Extract capture details
                $capture = $response['purchase_units'][0]['payments']['captures'][0] ?? null;

                return [
                    'success' => true,
                    'order_id' => $order->id,
                    'transaction_id' => $capture['id'] ?? $paypalOrderId,
                    'message' => 'Payment completed successfully',
                    'response_data' => [
                        'paypal_order_id' => $paypalOrderId,
                        'capture_id' => $capture['id'] ?? null,
                        'status' => $response['status'],
                        'payer_email' => $response['payer']['email_address'] ?? null,
                    ],
                ];
            }

            throw new \Exception('Payment not completed. Status: ' . $response['status']);
        } catch (\Exception $e) {
            Log::error('PayPalGateway: Callback handling failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ];
        }
    }

    private function normalizeCountryCode(string $country): ?string
    {
        $value = strtoupper(trim($country));
        if ($value === '') {
            return null;
        }

        if (strlen($value) === 2 && ctype_alpha($value)) {
            return $value;
        }

        $map = [
            'UNITED STATES' => 'US',
            'USA' => 'US',
            'UNITED STATES OF AMERICA' => 'US',
            'CANADA' => 'CA',
            'INDIA' => 'IN',
            'UNITED KINGDOM' => 'GB',
            'GREAT BRITAIN' => 'GB',
        ];

        return $map[$value] ?? null;
    }

    /**
     * Verify payment status for an order.
     */
    public function verifyPayment(Order $order): bool
    {
        try {
            $paypalOrderId = $order->payment_gateway_data['paypal_order_id'] ?? null;

            if (!$paypalOrderId) {
                return false;
            }

            $response = $this->apiRequest('GET', "/v2/checkout/orders/{$paypalOrderId}", []);

            return $response['status'] === 'COMPLETED';
        } catch (\Exception $e) {
            Log::error('PayPalGateway: Payment verification failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Process refund for an order.
     */
    public function refund(Order $order, ?float $amount = null, ?string $reason = null): array
    {
        Log::info('PayPalGateway: refund called', [
            'order_id' => $order->id,
            'amount' => $amount,
        ]);

        try {
            // Get capture ID from order metadata
            $captureId = $order->payment_gateway_data['capture_id'] ?? null;

            if (!$captureId) {
                throw new \Exception('PayPal capture ID not found');
            }

            $refundAmount = $amount ?? $order->grand_total;

            $refundData = [
                'amount' => [
                    'currency_code' => $order->currency_code ?? 'USD',
                    'value' => number_format($refundAmount, 2, '.', ''),
                ],
                'note_to_payer' => $reason ?? 'Refund for order #' . $order->order_number,
            ];

            $response = $this->apiRequest('POST', "/v2/payments/captures/{$captureId}/refund", $refundData);

            Log::info('PayPalGateway: Refund processed', [
                'order_id' => $order->id,
                'refund_id' => $response['id'],
                'status' => $response['status'],
            ]);

            return [
                'success' => true,
                'transaction_id' => $response['id'],
                'message' => 'Refund processed successfully',
                'amount' => $refundAmount,
            ];
        } catch (\Exception $e) {
            Log::error('PayPalGateway: Refund failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Refund failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get configuration fields required by this gateway.
     */
    public function getConfigFields(): array
    {
        return [
            'client_id' => [
                'type' => 'text',
                'label' => 'Client ID',
                'required' => true,
                'help' => 'Your PayPal REST API Client ID',
            ],
            'client_secret' => [
                'type' => 'password',
                'label' => 'Client Secret',
                'required' => true,
                'help' => 'Your PayPal REST API Client Secret',
            ],
            'mode' => [
                'type' => 'select',
                'label' => 'Mode',
                'required' => true,
                'options' => [
                    'sandbox' => 'Sandbox (Test)',
                    'live' => 'Live (Production)',
                ],
                'default' => 'sandbox',
                'help' => 'Use sandbox for testing, live for production',
            ],
            'webhook_id' => [
                'type' => 'text',
                'label' => 'Webhook ID',
                'required' => false,
                'help' => 'Optional: Webhook ID for signature verification',
            ],
        ];
    }

    /**
     * Check if gateway is properly configured and ready to use.
     */
    public function isConfigured(): bool
    {
        $method = $this->getPaymentMethod();

        if (!$method) {
            return false;
        }

        $clientId = $method->getConfigValue('client_id');
        $clientSecret = $method->getConfigValue('client_secret');

        return !empty($clientId) && !empty($clientSecret);
    }
}
