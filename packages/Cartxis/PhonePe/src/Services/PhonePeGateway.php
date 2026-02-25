<?php

namespace Cartxis\PhonePe\Services;

use Cartxis\Core\Contracts\PaymentGatewayInterface;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Shop\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * PhonePe Payment Gateway Implementation
 * 
 * Uses PhonePe PHP SDK v2 for Standard Checkout integration.
 */
class PhonePeGateway implements PaymentGatewayInterface
{
    protected ?PaymentMethod $paymentMethod = null;
    
    /**
     * PhonePe SDK client instance
     */
    protected $client = null;

    /**
     * Get payment method configuration from database.
     */
    protected function getPaymentMethod(): ?PaymentMethod
    {
        if (!$this->paymentMethod) {
            $this->paymentMethod = PaymentMethod::where('code', 'phonepe')
                ->where('is_active', true)
                ->first();
        }

        return $this->paymentMethod;
    }

    /**
     * Get configuration value, resolving test/live credentials based on mode.
     */
    protected function getConfig(string $key, mixed $default = null): mixed
    {
        $method = $this->getPaymentMethod();
        if (!$method) return $default;

        // Resolve credential keys based on mode (test vs production)
        if (in_array($key, ['merchant_id', 'client_id', 'client_secret', 'client_version'])) {
            $mode = $method->getConfigValue('mode', 'production');
            if ($mode === 'test') {
                $testVal = $method->getConfigValue('test_' . $key);
                if ($testVal) return $testVal;
            }
            // Production mode or no test value set — use live key
            return $method->getConfigValue($key, $default);
        }

        return $method->getConfigValue($key, $default);
    }

    /**
     * Get the gateway code.
     */
    public function getCode(): string
    {
        return 'phonepe';
    }

    /**
     * Get the gateway display name.
     */
    public function getName(): string
    {
        return 'PhonePe';
    }

    /**
     * Check if this gateway supports the given payment method.
     */
    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'phonepe';
    }

    /**
     * Initialize PhonePe SDK client.
     * 
     * @throws Exception
     */
    protected function initializeClient(): void
    {
        if ($this->client !== null) {
            return;
        }

        $clientId = $this->getConfig('client_id');
        $clientSecret = $this->getConfig('client_secret');
        $clientVersion = (int) $this->getConfig('client_version', 1);

        if (!$clientId || !$clientSecret) {
            throw new Exception('PhonePe credentials not configured');
        }

        // Check if PhonePe SDK classes are available
        if (!class_exists('\PhonePe\payments\v2\standardCheckout\StandardCheckoutClient')) {
            throw new Exception('PhonePe SDK not installed. Please run: composer require phonepe/pg-php-sdk-v2');
        }

        try {
            // Import PhonePe SDK classes
            $envClass = '\\PhonePe\\Env';
            $clientClass = '\\PhonePe\\payments\\v2\\standardCheckout\\StandardCheckoutClient';

            $envKey = $this->resolveEnvironment();
            $env = match ($envKey) {
                'UAT' => $envClass::UAT,
                'STAGE' => $envClass::STAGE,
                default => $envClass::PRODUCTION,
            };

            $this->client = $clientClass::getInstance(
                $clientId,
                $clientVersion,
                $clientSecret,
                $env
            );
        } catch (\Throwable $e) {
            Log::error('PhonePe SDK initialization failed', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to initialize PhonePe SDK: ' . $e->getMessage());
        }
    }

    /**
     * Resolve the PhonePe environment from mode setting.
     * test → UAT, production → PRODUCTION
     */
    protected function resolveEnvironment(): string
    {
        $mode = strtolower((string) $this->getPaymentMethod()?->getConfigValue('mode', 'production'));
        return $mode === 'test' ? 'UAT' : 'PRODUCTION';
    }

    /**
     * Step 1 of the PhonePe mobile SDK flow — generate an OAuth access token.
     *
     * Calls POST /v1/oauth/token with form-urlencoded credentials.
     * Returns the full JSON response, which includes:
     *   - access_token  (use as "O-Bearer <token>" header)
     *   - expires_at
     *   - token_type    ("O-Bearer")
     *
     * @throws Exception on HTTP error or missing access_token
     */
    public function generateAccessToken(): array
    {
        $env = $this->resolveEnvironment();
        $url = $env === 'UAT'
            ? 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token'
            : 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';

        $response = Http::asForm()->post($url, [
            'client_id'      => $this->getConfig('client_id'),
            'client_version' => (int) $this->getConfig('client_version', 1),
            'client_secret'  => $this->getConfig('client_secret'),
            'grant_type'     => 'client_credentials',
        ]);

        if (! $response->successful()) {
            throw new Exception('PhonePe authorization failed (' . $response->status() . '): ' . $response->body());
        }

        $data = $response->json();

        if (empty($data['access_token'])) {
            throw new Exception('PhonePe authorization response is missing access_token');
        }

        return $data;
    }

    /**
     * Step 2 of the PhonePe mobile SDK flow — create an SDK Order Token.
     *
     * Calls POST /checkout/v2/sdk/order with JSON body and O-Bearer auth header.
     * Returns the full JSON response, which includes:
     *   - orderId   (PhonePe's order reference — pass to Flutter SDK)
     *   - token     (order token — pass to Flutter SDK)
     *   - expireAt  (unix ms timestamp)
     *   - state
     *
     * @param  string $accessToken     Token from generateAccessToken()
     * @param  string $merchantOrderId Your unique reference (≤ 63 chars, [A-Za-z0-9_-])
     * @param  int    $amountInPaisa   Amount in lowest denomination (₹10 = 1000 paisa)
     * @throws Exception on HTTP error or missing token
     */
    public function createSdkOrderToken(string $accessToken, string $merchantOrderId, int $amountInPaisa): array
    {
        $env = $this->resolveEnvironment();
        $url = $env === 'UAT'
            ? 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/sdk/order'
            : 'https://api.phonepe.com/apis/pg/checkout/v2/sdk/order';

        $response = Http::withHeaders([
            'Authorization' => 'O-Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ])->post($url, [
            'merchantOrderId' => $merchantOrderId,
            'amount'          => $amountInPaisa,
            'expireAfter'     => 1200,
            'paymentFlow'     => ['type' => 'PG_CHECKOUT'],
        ]);

        if (! $response->successful()) {
            throw new Exception('PhonePe create order token failed (' . $response->status() . '): ' . $response->body());
        }

        $data = $response->json();

        if (empty($data['token'])) {
            throw new Exception('PhonePe create order token response is missing token');
        }

        return $data;
    }

    /**
     * Process payment for an order.
     * Redirects to PhonePe payment page.
     */
    public function processPayment(Order $order, array $data = [])
    {
        Log::info('PhonePeGateway: processPayment called', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        try {
            $this->initializeClient();

            // Amount in paisa (1 INR = 100 paisa)
            $amountInPaisa = (int) round($order->total * 100);

            // Build payment request
            $requestBuilderClass = '\PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder';
            
            $payRequest = $requestBuilderClass::builder()
                ->merchantOrderId($order->order_number)
                ->amount($amountInPaisa)
                ->redirectUrl(route('phonepe.success', ['order' => $order->id]))
                ->message("Order #{$order->order_number}")
                ->build();

            Log::info('PhonePeGateway: Initiating payment', [
                'order_id' => $order->id,
                'amount_paisa' => $amountInPaisa,
            ]);

            // Initiate payment
            $payResponse = $this->client->pay($payRequest);
            
            $state = $payResponse->getState();
            $redirectUrl = $payResponse->getRedirectUrl();
            $phonePeOrderId = $payResponse->getOrderId();

            Log::info('PhonePeGateway: Payment initiated', [
                'order_id' => $order->id,
                'phonepe_order_id' => $phonePeOrderId,
                'state' => $state,
                'redirect_url' => $redirectUrl,
            ]);

            // Store PhonePe order ID in order metadata
            $order->update([
                'payment_data' => json_encode([
                    'phonepe_order_id' => $phonePeOrderId,
                    'phonepe_state' => $state,
                    'merchant_order_id' => $order->order_number,
                ]),
            ]);

            if ($state === 'PENDING' && $redirectUrl) {
                // Redirect to PhonePe payment page
                return redirect($redirectUrl);
            }

            throw new Exception("Payment initiation failed. State: {$state}");

        } catch (\Throwable $e) {
            Log::error('PhonePe payment creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Unable to process payment. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Initiate PhonePe payment for the mobile API.
     *
     * Does NOT redirect. Calls PhonePe, stores the order ID, and returns
     * the redirectUrl (used as the "token" by the Flutter PhonePe SDK) plus
     * the PhonePe order ID and expiry timestamp.
     *
     * @return array{phonepe_token: string, phonepe_order_id: string, expires_at: int, checksum: string, merchant_id: string}
     * @throws Exception
     */
    public function initiateForApi(Order $order): array
    {
        try {
            $amountInPaisa = (int) round($order->total * 100);

            // Step 1: OAuth access token
            $authData = $this->generateAccessToken();

            // Step 2: SDK order token (JWT) — linked to this specific order
            $orderData = $this->createSdkOrderToken(
                $authData['access_token'],
                $order->order_number,
                $amountInPaisa
            );

            $phonePeOrderId = $orderData['orderId'];
            $token          = $orderData['token'];     // JWT — what Flutter SDK v3 needs
            $expiresAt      = $orderData['expireAt'] ?? null;

            // Derive short merchant ID from client_id (e.g. M22TUU3OAID7Z_xxx → M22TUU3OAID7Z)
            $clientId   = $this->getConfig('client_id');
            $merchantId = explode('_', $clientId)[0];

            $order->update([
                'payment_data' => json_encode([
                    'phonepe_order_id'  => $phonePeOrderId,
                    'merchant_order_id' => $order->order_number,
                    'initiated_via'     => 'flutter_sdk',
                ]),
            ]);

            Log::info('PhonePeGateway: initiateForApi success', [
                'order_id'         => $order->id,
                'phonepe_order_id' => $phonePeOrderId,
                'merchant_id'      => $merchantId,
            ]);

            return [
                'phonepe_token'    => $token,           // JWT for Flutter SDK v3
                'phonepe_order_id' => $phonePeOrderId,
                'expires_at'       => $expiresAt,
                'checksum'         => '',               // Not used by SDK v3
                'merchant_id'      => $merchantId,
            ];

        } catch (\Throwable $e) {
            Log::error('PhonePeGateway: initiateForApi failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            throw new Exception('Unable to initiate PhonePe payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment callback from PhonePe.
     */
    public function handleCallback(array $data): array
    {
        try {
            $this->initializeClient();

            $merchantOrderId = $data['merchant_order_id'] ?? null;

            if (!$merchantOrderId) {
                return [
                    'success' => false,
                    'message' => 'Invalid callback data: missing merchant_order_id',
                ];
            }

            // Check order status with PhonePe
            $statusResponse = $this->client->getOrderStatus($merchantOrderId, true);
            
            $state = $statusResponse->getState();
            $transactionId = $statusResponse->getTransactionId();

            Log::info('PhonePeGateway: Callback processed', [
                'merchant_order_id' => $merchantOrderId,
                'state' => $state,
                'transaction_id' => $transactionId,
            ]);

            if ($state === 'COMPLETED') {
                // Find order by order_number
                $order = Order::where('order_number', $merchantOrderId)->first();
                
                return [
                    'success' => true,
                    'order_id' => $order?->id,
                    'transaction_id' => $transactionId,
                    'message' => 'Payment successful',
                ];
            }

            return [
                'success' => false,
                'message' => "Payment not completed. Status: {$state}",
            ];

        } catch (\Throwable $e) {
            Log::error('PhonePe callback handling failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Unable to verify payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment status for an order.
     */
    public function verifyPayment(Order $order): bool
    {
        try {
            $this->initializeClient();

            $paymentData = json_decode($order->payment_data, true);
            $merchantOrderId = $paymentData['merchant_order_id'] ?? $order->order_number;

            $statusResponse = $this->client->getOrderStatus($merchantOrderId, true);
            $state = $statusResponse->getState();

            Log::info('PhonePeGateway: Payment verification', [
                'order_id' => $order->id,
                'merchant_order_id' => $merchantOrderId,
                'state' => $state,
            ]);

            return $state === 'COMPLETED';

        } catch (\Throwable $e) {
            Log::error('PhonePe payment verification failed', [
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
        try {
            $this->initializeClient();

            $paymentData = json_decode($order->payment_data, true);
            $merchantOrderId = $paymentData['merchant_order_id'] ?? $order->order_number;

            // Amount in paisa
            $refundAmount = $amount 
                ? (int) round($amount * 100)
                : (int) round($order->total * 100);

            // Generate unique refund ID
            $merchantRefundId = 'REFUND_' . $order->order_number . '_' . time();

            $refundBuilderClass = '\PhonePe\payments\v2\models\request\builders\StandardCheckoutRefundRequestBuilder';

            $refundRequest = $refundBuilderClass::builder()
                ->merchantRefundId($merchantRefundId)
                ->originalMerchantOrderId($merchantOrderId)
                ->amount($refundAmount)
                ->build();

            Log::info('PhonePeGateway: Initiating refund', [
                'order_id' => $order->id,
                'merchant_refund_id' => $merchantRefundId,
                'amount_paisa' => $refundAmount,
            ]);

            $refundResponse = $this->client->refund($refundRequest);

            $refundId = $refundResponse->getRefundId();
            $refundState = $refundResponse->getState();

            Log::info('PhonePeGateway: Refund processed', [
                'order_id' => $order->id,
                'refund_id' => $refundId,
                'state' => $refundState,
            ]);

            // Update order payment data with refund info
            $paymentData['refunds'] = $paymentData['refunds'] ?? [];
            $paymentData['refunds'][] = [
                'refund_id' => $refundId,
                'merchant_refund_id' => $merchantRefundId,
                'amount' => $amount ?? $order->total,
                'state' => $refundState,
                'reason' => $reason,
                'created_at' => now()->toIso8601String(),
            ];
            $order->update(['payment_data' => json_encode($paymentData)]);

            return [
                'success' => in_array($refundState, ['PENDING', 'COMPLETED']),
                'transaction_id' => $refundId,
                'message' => "Refund initiated. Status: {$refundState}",
            ];

        } catch (\Throwable $e) {
            Log::error('PhonePe refund failed', [
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
     * Get configuration fields for this gateway.
     */
    public function getConfigFields(): array
    {
        return [
            [
                'key'      => 'mode',
                'label'    => 'Mode',
                'type'     => 'select',
                'required' => true,
                'default'  => 'production',
                'options'  => [
                    ['value' => 'production', 'label' => 'Live (Production)'],
                    ['value' => 'test',       'label' => 'Test (UAT)'],
                ],
                'help'     => 'Use Test mode for development; switch to Live for real payments',
            ],
            // ── Live credentials ──────────────────────────────────────────
            [
                'key'      => 'merchant_id',
                'label'    => 'Live Merchant ID',
                'type'     => 'text',
                'required' => true,
                'help'     => 'PhonePe Merchant ID for production (e.g. M22TUU3OAID7Z)',
            ],
            [
                'key'      => 'client_id',
                'label'    => 'Live Client ID',
                'type'     => 'text',
                'required' => true,
                'help'     => 'PhonePe Client ID for production',
            ],
            [
                'key'      => 'client_secret',
                'label'    => 'Live Client Secret',
                'type'     => 'password',
                'required' => true,
                'help'     => 'PhonePe Client Secret for production (keep secure)',
            ],
            [
                'key'      => 'client_version',
                'label'    => 'Live Client Version',
                'type'     => 'number',
                'required' => true,
                'default'  => 1,
                'help'     => 'PhonePe Client Version for production (typically 1)',
            ],
            // ── Test (UAT) credentials ────────────────────────────────────
            [
                'key'      => 'test_merchant_id',
                'label'    => 'Test Merchant ID',
                'type'     => 'text',
                'required' => false,
                'help'     => 'PhonePe Merchant ID for UAT / test environment',
            ],
            [
                'key'      => 'test_client_id',
                'label'    => 'Test Client ID',
                'type'     => 'text',
                'required' => false,
                'help'     => 'PhonePe Client ID for UAT / test environment',
            ],
            [
                'key'      => 'test_client_secret',
                'label'    => 'Test Client Secret',
                'type'     => 'password',
                'required' => false,
                'help'     => 'PhonePe Client Secret for UAT / test environment',
            ],
            [
                'key'      => 'test_client_version',
                'label'    => 'Test Client Version',
                'type'     => 'number',
                'required' => false,
                'default'  => 1,
                'help'     => 'PhonePe Client Version for UAT / test environment',
            ],
            // ── Webhook ───────────────────────────────────────────────────
            [
                'key'      => 'callback_username',
                'label'    => 'Callback Username',
                'type'     => 'text',
                'required' => true,
                'help'     => 'Username for webhook authentication (must match PhonePe Dashboard)',
            ],
            [
                'key'      => 'callback_password',
                'label'    => 'Callback Password',
                'type'     => 'password',
                'required' => true,
                'help'     => 'Password for webhook authentication (must match PhonePe Dashboard)',
            ],
        ];
    }

    /**
     * Check if gateway is properly configured.
     */
    public function isConfigured(): bool
    {
        $merchantId    = $this->getConfig('merchant_id');
        $clientId      = $this->getConfig('client_id');
        $clientSecret  = $this->getConfig('client_secret');
        $clientVersion = $this->getConfig('client_version');

        return !empty($merchantId) && !empty($clientId) && !empty($clientSecret) && !empty($clientVersion);
    }
}
