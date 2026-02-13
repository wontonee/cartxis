<?php

namespace Cartxis\Razorpay\Services;

use Cartxis\Core\Contracts\PaymentGatewayInterface;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Shop\Models\Order;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay Payment Gateway Implementation
 */
class RazorpayGateway implements PaymentGatewayInterface
{
    protected ?PaymentMethod $paymentMethod = null;
    protected ?Api $razorpay = null;

    /**
     * Get payment method configuration from database.
     */
    protected function getPaymentMethod(): ?PaymentMethod
    {
        if (!$this->paymentMethod) {
            $this->paymentMethod = PaymentMethod::where('code', 'razorpay')
                ->where('is_active', true)
                ->first();
        }

        return $this->paymentMethod;
    }

    /**
     * Get configuration value, resolving test/production mode for API keys.
     */
    protected function getConfig(string $key, mixed $default = null): mixed
    {
        $method = $this->getPaymentMethod();
        if (!$method) return $default;

        // Resolve key_id / key_secret based on mode (test vs production)
        if (in_array($key, ['key_id', 'key_secret'])) {
            $mode = $method->getConfigValue('mode', 'test');
            if ($mode === 'test') {
                $testVal = $method->getConfigValue('test_' . $key);
                if ($testVal) return $testVal;
            }
            // In production mode or no test key set, use the live key
            return $method->getConfigValue($key, $default);
        }

        return $method->getConfigValue($key, $default);
    }

    /**
     * Get Razorpay API instance.
     */
    protected function getRazorpayApi(): Api
    {
        if (!$this->razorpay) {
            $keyId = $this->getConfig('key_id');
            $keySecret = $this->getConfig('key_secret');
            $mode = $this->getPaymentMethod()?->getConfigValue('mode', 'test');
            
            if (!$keyId || !$keySecret) {
                throw new \Exception('Razorpay API credentials not configured');
            }
            
            Log::info('RazorpayGateway: Using credentials', [
                'mode' => $mode,
                'key_id' => substr($keyId, 0, 12) . '***',
                'key_secret_length' => strlen($keySecret),
            ]);
            
            $this->razorpay = new Api($keyId, $keySecret);
        }

        return $this->razorpay;
    }

    /**
     * Get the gateway code.
     */
    public function getCode(): string
    {
        return 'razorpay';
    }

    /**
     * Get the gateway display name.
     */
    public function getName(): string
    {
        return 'Razorpay';
    }

    /**
     * Check if this gateway supports the given payment method.
     */
    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'razorpay';
    }

    /**
     * Process payment for an order.
     * Creates a Razorpay Order and returns payment data.
     */
    public function processPayment(Order $order, array $data = [])
    {
        Log::info('RazorpayGateway: processPayment called', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
        
        try {
            $api = $this->getRazorpayApi();
            
            Log::info('RazorpayGateway: API initialized', [
                'order_id' => $order->id,
            ]);

            // Get shipping address for receipt
            $shippingAddress = $order->shippingAddress;
            $customerName = $shippingAddress
                ? trim($shippingAddress->first_name . ' ' . $shippingAddress->last_name)
                : ($order->customer_name ?? 'Customer');

            // Create Razorpay Order
            $razorpayOrder = $api->order->create([
                'receipt' => $order->order_number,
                'amount' => (int) ($order->total * 100), // Amount in paise
                'currency' => $this->getConfig('currency', 'INR'),
                'notes' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $customerName,
                    'customer_email' => $order->customer_email,
                ]
            ]);

            Log::info('RazorpayGateway: Order created', [
                'razorpay_order_id' => $razorpayOrder['id'],
                'order_id' => $order->id,
            ]);

            // Update order with Razorpay order ID
            $order->update([
                'payment_gateway_order_id' => $razorpayOrder['id'],
            ]);

            // Return payment data for frontend
            return [
                'success' => true,
                'gateway_type' => 'frontend_integration',
                'gateway_code' => 'razorpay',
                'payment_data' => [
                    'gateway_code' => 'razorpay',
                    'script_url' => 'https://checkout.razorpay.com/v1/checkout.js',
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'razorpay_key_id' => $this->getConfig('key_id'),
                    'amount' => $razorpayOrder['amount'],
                    'currency' => $razorpayOrder['currency'],
                    'name' => config('app.name', 'Cartxis'),
                    'description' => "Order #{$order->order_number}",
                    'prefill' => [
                        'name' => $customerName,
                        'email' => $order->customer_email,
                        'contact' => $shippingAddress?->phone ?? '',
                    ],
                    'theme' => [
                        'color' => '#3399cc',
                    ],
                    'callback_url' => route('razorpay.callback', ['order' => $order->id]),
                    'cancel_url' => route('razorpay.cancel', ['order' => $order->id]),
                ]
            ];

        } catch (\Exception $e) {
            Log::error('RazorpayGateway: Payment processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle payment callback from Razorpay.
     */
    public function handleCallback(array $data): array
    {
        try {
            $razorpayOrderId = $data['razorpay_order_id'] ?? null;
            $razorpayPaymentId = $data['razorpay_payment_id'] ?? null;
            $razorpaySignature = $data['razorpay_signature'] ?? null;

            if (!$razorpayOrderId || !$razorpayPaymentId || !$razorpaySignature) {
                throw new \Exception('Missing payment verification data');
            }

            // Verify signature
            $api = $this->getRazorpayApi();
            $attributes = [
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
            ];
            
            $api->utility->verifyPaymentSignature($attributes);

            // Fetch payment details
            $payment = $api->payment->fetch($razorpayPaymentId);

            Log::info('RazorpayGateway: Payment verified', [
                'payment_id' => $razorpayPaymentId,
                'order_id' => $razorpayOrderId,
                'status' => $payment->status,
            ]);

            return [
                'success' => true,
                'payment_id' => $razorpayPaymentId,
                'payment' => $payment,
            ];

        } catch (SignatureVerificationError $e) {
            Log::error('RazorpayGateway: Signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment verification failed: Invalid signature',
            ];

        } catch (\Exception $e) {
            Log::error('RazorpayGateway: Callback handling failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook from Razorpay.
     */
    public function handleWebhook(array $payload, string $signature): bool
    {
        try {
            $webhookSecret = $this->getConfig('webhook_secret');
            
            if (!$webhookSecret) {
                Log::warning('RazorpayGateway: Webhook secret not configured');
                return false;
            }

            // Verify webhook signature
            $expectedSignature = hash_hmac('sha256', json_encode($payload), $webhookSecret);
            
            if ($signature !== $expectedSignature) {
                Log::error('RazorpayGateway: Webhook signature verification failed');
                return false;
            }

            $event = $payload['event'] ?? null;
            $paymentEntity = $payload['payload']['payment']['entity'] ?? null;

            if (!$event || !$paymentEntity) {
                Log::warning('RazorpayGateway: Invalid webhook payload');
                return false;
            }

            Log::info('RazorpayGateway: Webhook received', [
                'event' => $event,
                'payment_id' => $paymentEntity['id'] ?? null,
            ]);

            // Handle different webhook events
            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($paymentEntity);
                    break;

                case 'payment.failed':
                    $this->handlePaymentFailed($paymentEntity);
                    break;

                case 'refund.created':
                    $this->handleRefundCreated($payload['payload']['refund']['entity'] ?? []);
                    break;

                default:
                    Log::info('RazorpayGateway: Unhandled webhook event', ['event' => $event]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('RazorpayGateway: Webhook handling failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle payment captured event.
     */
    protected function handlePaymentCaptured(array $payment): void
    {
        $orderId = $payment['notes']['order_id'] ?? null;
        
        if (!$orderId) {
            Log::warning('RazorpayGateway: Order ID not found in payment notes');
            return;
        }

        $order = Order::find($orderId);
        
        if (!$order) {
            Log::warning('RazorpayGateway: Order not found', ['order_id' => $orderId]);
            return;
        }

        $order->update([
            'payment_status' => Order::PAYMENT_PAID,
            'status' => Order::STATUS_PROCESSING,
        ]);

        Log::info('RazorpayGateway: Payment captured, order updated', [
            'order_id' => $orderId,
        ]);
    }

    /**
     * Handle payment failed event.
     */
    protected function handlePaymentFailed(array $payment): void
    {
        $orderId = $payment['notes']['order_id'] ?? null;
        
        if (!$orderId) {
            Log::warning('RazorpayGateway: Order ID not found in payment notes');
            return;
        }

        $order = Order::find($orderId);
        
        if (!$order) {
            Log::warning('RazorpayGateway: Order not found', ['order_id' => $orderId]);
            return;
        }

        $order->update([
            'payment_status' => Order::PAYMENT_FAILED,
        ]);

        Log::info('RazorpayGateway: Payment failed, order updated', [
            'order_id' => $orderId,
        ]);
    }

    /**
     * Handle refund created event.
     */
    protected function handleRefundCreated(array $refund): void
    {
        Log::info('RazorpayGateway: Refund created', [
            'refund_id' => $refund['id'] ?? null,
            'payment_id' => $refund['payment_id'] ?? null,
            'amount' => $refund['amount'] ?? null,
        ]);
        
        // Additional refund handling logic can be added here
    }

    /**
     * Format line items for display.
     */
    protected function formatLineItems(Order $order): array
    {
        $items = [];
        
        foreach ($order->items as $item) {
            $items[] = [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'amount' => (int) ($item->price * 100), // Amount in paise
            ];
        }
        
        return $items;
    }

    /**
     * Refund a payment.
     */
    public function refund(Order $order, ?float $amount = null, ?string $reason = null): array
    {
        try {
            $api = $this->getRazorpayApi();
            $paymentId = $order->payment_gateway_transaction_id;

            if (!$paymentId) {
                throw new \Exception('Payment ID not found for order');
            }
            
            // If amount is null, refund full amount
            $refundAmount = $amount ?? $order->total;

            $refund = $api->refund->create([
                'payment_id' => $paymentId,
                'amount' => (int) ($refundAmount * 100), // Amount in paise
                'notes' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'reason' => $reason ?? 'Customer refund request',
                ]
            ]);

            Log::info('RazorpayGateway: Refund created', [
                'refund_id' => $refund['id'],
                'payment_id' => $paymentId,
                'amount' => $refundAmount,
            ]);

            return [
                'success' => true,
                'transaction_id' => $refund['id'],
                'message' => 'Refund processed successfully',
            ];

        } catch (\Exception $e) {
            Log::error('RazorpayGateway: Refund failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'transaction_id' => '',
                'message' => 'Refund failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment status for an order.
     */
    public function verifyPayment(Order $order): bool
    {
        try {
            $api = $this->getRazorpayApi();
            $paymentId = $order->payment_gateway_transaction_id;

            if (!$paymentId) {
                return false;
            }

            $payment = $api->payment->fetch($paymentId);
            
            return $payment->status === 'captured' || $payment->status === 'authorized';

        } catch (\Exception $e) {
            Log::error('RazorpayGateway: Payment verification failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get configuration fields required by this gateway.
     */
    public function getConfigFields(): array
    {
        return [
            [
                'name' => 'key_id',
                'label' => 'Razorpay Key ID',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'rzp_test_xxxxxxxxxxxxxxxx',
                'description' => 'Your Razorpay Key ID from Dashboard > Settings > API Keys',
            ],
            [
                'name' => 'key_secret',
                'label' => 'Razorpay Key Secret',
                'type' => 'password',
                'required' => true,
                'placeholder' => 'xxxxxxxxxxxxxxxxxxxxxxxx',
                'description' => 'Your Razorpay Key Secret from Dashboard > Settings > API Keys',
            ],
            [
                'name' => 'currency',
                'label' => 'Currency',
                'type' => 'select',
                'required' => true,
                'default' => 'INR',
                'options' => [
                    'INR' => 'Indian Rupee (INR)',
                    'USD' => 'US Dollar (USD)',
                    'EUR' => 'Euro (EUR)',
                    'GBP' => 'British Pound (GBP)',
                ],
                'description' => 'Currency for payments (Razorpay primarily supports INR)',
            ],
            [
                'name' => 'webhook_secret',
                'label' => 'Webhook Secret',
                'type' => 'password',
                'required' => false,
                'placeholder' => 'whsec_xxxxxxxxxxxxxxxx',
                'description' => 'Webhook secret from Dashboard > Settings > Webhooks (optional but recommended)',
            ],
            [
                'name' => 'auto_capture',
                'label' => 'Auto Capture Payments',
                'type' => 'checkbox',
                'required' => false,
                'default' => true,
                'description' => 'Automatically capture payments after authorization',
            ],
        ];
    }

    /**
     * Check if gateway is properly configured and ready to use.
     */
    public function isConfigured(): bool
    {
        $keyId = $this->getConfig('key_id');
        $keySecret = $this->getConfig('key_secret');

        return !empty($keyId) && !empty($keySecret);
    }
}
