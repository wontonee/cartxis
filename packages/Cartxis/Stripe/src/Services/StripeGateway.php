<?php

namespace Cartxis\Stripe\Services;

use Cartxis\Core\Contracts\PaymentGatewayInterface;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Shop\Models\Order;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

/**
 * Stripe Payment Gateway Implementation
 */
class StripeGateway implements PaymentGatewayInterface
{
    protected ?PaymentMethod $paymentMethod = null;

    /**
     * Get payment method configuration from database.
     */
    protected function getPaymentMethod(): ?PaymentMethod
    {
        if (!$this->paymentMethod) {
            $this->paymentMethod = PaymentMethod::where('code', 'stripe')
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

        // Resolve credential keys based on mode (test vs live)
        if (in_array($key, ['publishable_key', 'secret_key', 'webhook_secret'])) {
            $mode = $method->getConfigValue('mode', 'live');
            if ($mode === 'test') {
                $testVal = $method->getConfigValue('test_' . $key);
                if ($testVal) return $testVal;
            }
            // Live mode or no test value set — use live key
            // Also fall back to legacy 'public_key' field name for publishable_key
            $val = $method->getConfigValue($key);
            if ($val === null && $key === 'publishable_key') {
                $val = $method->getConfigValue('public_key', $default);
            }
            return $val ?? $default;
        }

        return $method->getConfigValue($key, $default);
    }
    /**
     * Get the gateway code.
     */
    public function getCode(): string
    {
        return 'stripe';
    }

    /**
     * Get the gateway display name.
     */
    public function getName(): string
    {
        return 'Stripe';
    }

    /**
     * Check if this gateway supports the given payment method.
     */
    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'stripe';
    }

    /**
     * Process payment for an order.
     * Redirects to Stripe Checkout.
     */
    public function processPayment(Order $order, array $data = [])
    {
        Log::info('StripeGateway: processPayment called', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
        
        try {
            // Set Stripe API key from database configuration
            $secretKey = $this->getConfig('secret_key');
            
            Log::info('StripeGateway: Retrieved secret key', [
                'has_key' => !empty($secretKey),
                'key_length' => $secretKey ? strlen($secretKey) : 0,
            ]);
            
            if (!$secretKey) {
                throw new \Exception('Stripe secret key not configured');
            }
            
            Stripe::setApiKey($secretKey);
            
            Log::info('StripeGateway: Creating Checkout Session', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email,
            ]);

            // Create Stripe Checkout Session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $this->formatLineItems($order),
                'mode' => 'payment',
                'success_url' => route('stripe.success', ['order' => $order->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('stripe.cancel', ['order' => $order->id]),
                'customer_email' => $order->customer_email,
                'client_reference_id' => $order->order_number,
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                ],
                // Pre-fill customer shipping information
                'shipping_address_collection' => [
                    'allowed_countries' => ['US', 'CA', 'GB', 'IN'], // Add countries as needed
                ],
                'shipping_options' => [
                    [
                        'shipping_rate_data' => [
                            'type' => 'fixed_amount',
                            'fixed_amount' => [
                                'amount' => 0, // Already included in line items
                                'currency' => 'inr',
                            ],
                            'display_name' => $order->shipping_method ?? 'Standard Shipping',
                        ],
                    ],
                ],
                // Pre-fill billing details if available
                'billing_address_collection' => 'required',
                'phone_number_collection' => [
                    'enabled' => true,
                ],
            ]);
            
            Log::info('StripeGateway: Checkout Session created', [
                'session_id' => $session->id,
                'url' => $session->url,
            ]);

            // Store session ID in order metadata
            $order->update([
                'payment_data' => json_encode([
                    'stripe_session_id' => $session->id,
                    'stripe_payment_intent' => $session->payment_intent,
                ]),
            ]);
            
            Log::info('StripeGateway: Redirecting to Stripe', [
                'url' => $session->url,
            ]);

            // Redirect to Stripe Checkout
            return redirect($session->url);

        } catch (ApiErrorException $e) {
            Log::error('Stripe payment creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception('Unable to process payment. Please try again.');
        }
    }

    /**
     * Format order items for Stripe Checkout.
     */
    protected function formatLineItems(Order $order): array
    {
        $items = [];

        // Add order items
        foreach ($order->items as $item) {
            $items[] = [
                'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                        'name' => $item->product_name,
                        'images' => $item->product_image ? [url($item->product_image)] : [],
                    ],
                    'unit_amount' => (int) ($item->price * 100), // Convert to cents
                ],
                'quantity' => $item->quantity,
            ];
        }

        // Add shipping as a line item if > 0
        if ($order->shipping_cost > 0) {
            $items[] = [
                'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                        'name' => 'Shipping - ' . $order->shipping_method,
                    ],
                    'unit_amount' => (int) ($order->shipping_cost * 100),
                ],
                'quantity' => 1,
            ];
        }

        // Add tax as a line item if > 0
        if ($order->tax > 0) {
            $items[] = [
                'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                        'name' => 'Tax',
                    ],
                    'unit_amount' => (int) ($order->tax * 100),
                ],
                'quantity' => 1,
            ];
        }

        return $items;
    }

    /**
     * Handle payment callback from Stripe.
     */
    public function handleCallback(array $data): array
    {
        try {
            $sessionId = $data['session_id'] ?? null;

            if (!$sessionId) {
                return [
                    'success' => false,
                    'message' => 'Invalid callback data',
                ];
            }

            // Set Stripe API key
            $secretKey = $this->getConfig('secret_key');
            Stripe::setApiKey($secretKey);

            // Retrieve the session from Stripe
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return [
                    'success' => false,
                    'message' => 'Payment not completed',
                ];
            }

            $orderId = $session->metadata->order_id ?? null;

            return [
                'success' => true,
                'order_id' => $orderId,
                'transaction_id' => $session->payment_intent,
                'message' => 'Payment successful',
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe callback handling failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Unable to verify payment',
            ];
        }
    }

    /**
     * Verify payment status for an order.
     */
    public function verifyPayment(Order $order, array $data = []): bool
    {
        try {
            $secretKey = $this->getConfig('secret_key');
            Stripe::setApiKey($secretKey);

            // Flutter path: verify via PaymentIntent ID passed from the SDK
            $paymentIntentId = $data['payment_intent_id'] ?? null;

            if (!$paymentIntentId) {
                // Fallback: try order payment_data
                $paymentData     = is_array($order->payment_data)
                    ? $order->payment_data
                    : json_decode($order->payment_data ?? '{}', true);
                $paymentIntentId = $paymentData['payment_intent_id'] ?? null;
            }

            if ($paymentIntentId) {
                $intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
                $succeeded = $intent->status === 'succeeded';
                if ($succeeded) {
                    $order->update(['payment_gateway_transaction_id' => $paymentIntentId]);
                }
                Log::info('StripeGateway: PaymentIntent verify', [
                    'order_id' => $order->id,
                    'intent'   => $paymentIntentId,
                    'status'   => $intent->status,
                ]);
                return $succeeded;
            }

            // Legacy path: Stripe Checkout Session
            $paymentData = is_array($order->payment_data)
                ? $order->payment_data
                : json_decode($order->payment_data ?? '{}', true);
            $sessionId = $paymentData['stripe_session_id'] ?? null;

            if (!$sessionId) {
                Log::warning('StripeGateway: no payment_intent_id or session_id', ['order_id' => $order->id]);
                return false;
            }

            $session = Session::retrieve($sessionId);
            return $session->payment_status === 'paid';

        } catch (ApiErrorException $e) {
            Log::error('Stripe payment verification failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
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
            // Set Stripe API key
            $secretKey = $this->getConfig('secret_key');
            Stripe::setApiKey($secretKey);

            $paymentData = json_decode($order->payment_data, true);
            $paymentIntentId = $paymentData['stripe_payment_intent'] ?? null;

            if (!$paymentIntentId) {
                return [
                    'success' => false,
                    'message' => 'Payment intent not found',
                ];
            }

            $refundData = [
                'payment_intent' => $paymentIntentId,
            ];

            if ($amount) {
                $refundData['amount'] = (int) ($amount * 100); // Convert to cents
            }

            if ($reason) {
                $refundData['reason'] = $reason;
            }

            $refund = \Stripe\Refund::create($refundData);

            return [
                'success' => true,
                'transaction_id' => $refund->id,
                'message' => 'Refund processed successfully',
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe refund failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
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
                'default'  => 'live',
                'options'  => [
                    ['value' => 'live', 'label' => 'Live'],
                    ['value' => 'test', 'label' => 'Test'],
                ],
                'help'     => 'Use Test mode for development; switch to Live for real payments',
            ],
            // ── Live credentials ──────────────────────────────────────────
            [
                'key'      => 'publishable_key',
                'label'    => 'Live Publishable Key',
                'type'     => 'text',
                'required' => true,
                'help'     => 'Stripe live publishable key (pk_live_...)',
            ],
            [
                'key'      => 'secret_key',
                'label'    => 'Live Secret Key',
                'type'     => 'password',
                'required' => true,
                'help'     => 'Stripe live secret key (sk_live_...)',
            ],
            [
                'key'      => 'webhook_secret',
                'label'    => 'Live Webhook Secret',
                'type'     => 'password',
                'required' => false,
                'help'     => 'Stripe live webhook signing secret (whsec_...)',
            ],
            // ── Test credentials ──────────────────────────────────────────
            [
                'key'      => 'test_publishable_key',
                'label'    => 'Test Publishable Key',
                'type'     => 'text',
                'required' => false,
                'help'     => 'Stripe test publishable key (pk_test_...)',
            ],
            [
                'key'      => 'test_secret_key',
                'label'    => 'Test Secret Key',
                'type'     => 'password',
                'required' => false,
                'help'     => 'Stripe test secret key (sk_test_...)',
            ],
            [
                'key'      => 'test_webhook_secret',
                'label'    => 'Test Webhook Secret',
                'type'     => 'password',
                'required' => false,
                'help'     => 'Stripe test webhook signing secret (whsec_...)',
            ],
        ];
    }

    /**
     * Check if gateway is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->getConfig('publishable_key')) && !empty($this->getConfig('secret_key'));
    }
}
