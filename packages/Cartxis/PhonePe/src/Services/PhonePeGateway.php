<?php

namespace Cartxis\PhonePe\Services;

use Cartxis\Core\Contracts\PaymentGatewayInterface;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Shop\Models\Order;
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
     * Get configuration value.
     */
    protected function getConfig(string $key, mixed $default = null): mixed
    {
        $method = $this->getPaymentMethod();
        return $method?->getConfigValue($key, $default);
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
            $envClass = '\PhonePe\Env';
            $clientClass = '\PhonePe\payments\v2\standardCheckout\StandardCheckoutClient';
            
            // PhonePe SDK only supports PRODUCTION environment
            $env = $envClass::PRODUCTION;
            
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
                'key' => 'client_id',
                'label' => 'Client ID',
                'type' => 'text',
                'required' => true,
                'help' => 'Your PhonePe Client ID from the Business Dashboard',
            ],
            [
                'key' => 'client_secret',
                'label' => 'Client Secret',
                'type' => 'password',
                'required' => true,
                'help' => 'Your PhonePe Client Secret (keep this secure)',
            ],
            [
                'key' => 'client_version',
                'label' => 'Client Version',
                'type' => 'number',
                'required' => true,
                'help' => 'Your PhonePe Client Version (typically an integer)',
            ],
            [
                'key' => 'callback_username',
                'label' => 'Callback Username',
                'type' => 'text',
                'required' => true,
                'help' => 'Username for webhook authentication (must match PhonePe Dashboard)',
            ],
            [
                'key' => 'callback_password',
                'label' => 'Callback Password',
                'type' => 'password',
                'required' => true,
                'help' => 'Password for webhook authentication (must match PhonePe Dashboard)',
            ],
        ];
    }

    /**
     * Check if gateway is properly configured.
     */
    public function isConfigured(): bool
    {
        $clientId = $this->getConfig('client_id');
        $clientSecret = $this->getConfig('client_secret');
        $clientVersion = $this->getConfig('client_version');

        return !empty($clientId) && !empty($clientSecret) && !empty($clientVersion);
    }
}
