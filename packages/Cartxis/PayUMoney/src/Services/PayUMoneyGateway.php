<?php

namespace Cartxis\PayUMoney\Services;

use Cartxis\Core\Contracts\PaymentGatewayInterface;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Shop\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * PayUMoney Payment Gateway Implementation
 */
class PayUMoneyGateway implements PaymentGatewayInterface
{
    protected ?PaymentMethod $paymentMethod = null;

    /**
     * PayUMoney API URLs
     */
    protected const TEST_URL = 'https://test.payu.in/_payment';
    protected const LIVE_URL = 'https://secure.payu.in/_payment';

    /**
     * Get payment method configuration from database.
     */
    protected function getPaymentMethod(): ?PaymentMethod
    {
        if (!$this->paymentMethod) {
            $this->paymentMethod = PaymentMethod::where('code', 'payumoney')
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
     * Get PayUMoney payment URL based on mode.
     */
    protected function getPaymentUrl(): string
    {
        $mode = $this->getConfig('mode', 'test');
        return $mode === 'production' ? self::LIVE_URL : self::TEST_URL;
    }

    /**
     * Generate payment hash for PayUMoney.
     */
    protected function generateHash(array $params): string
    {
        $merchantKey = $this->getConfig('merchant_key');
        $salt = $this->getConfig('merchant_salt');

        // Hash sequence: key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||salt
        $hashString = sprintf(
            '%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s||||||%s',
            $merchantKey,
            $params['txnid'],
            $params['amount'],
            $params['productinfo'],
            $params['firstname'],
            $params['email'],
            $params['udf1'] ?? '',
            $params['udf2'] ?? '',
            $params['udf3'] ?? '',
            $params['udf4'] ?? '',
            $params['udf5'] ?? '',
            $salt
        );

        return hash('sha512', $hashString);
    }

    /**
     * Verify hash from PayUMoney response.
     */
    protected function verifyHash(array $response): bool
    {
        $salt = $this->getConfig('merchant_salt');

        // Reverse hash sequence for response verification
        $hashString = sprintf(
            '%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s',
            $salt,
            $response['status'] ?? '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $response['udf5'] ?? '',
            $response['udf4'] ?? '',
            $response['udf3'] ?? '',
            $response['udf2'] ?? '',
            $response['udf1'] ?? ''
        );

        $hashString .= sprintf(
            '|%s|%s|%s|%s|%s',
            $response['email'] ?? '',
            $response['firstname'] ?? '',
            $response['productinfo'] ?? '',
            $response['amount'] ?? '',
            $response['txnid'] ?? ''
        );

        $hashString .= '|' . $this->getConfig('merchant_key');

        $calculatedHash = hash('sha512', $hashString);

        return strcasecmp($calculatedHash, $response['hash'] ?? '') === 0;
    }

    /**
     * Get the gateway code.
     */
    public function getCode(): string
    {
        return 'payumoney';
    }

    /**
     * Get the gateway display name.
     */
    public function getName(): string
    {
        return 'PayUMoney';
    }

    /**
     * Check if this gateway supports the given payment method.
     */
    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'payumoney';
    }

    /**
     * Process payment for an order.
     * Returns payment form data for frontend submission.
     */
    public function processPayment(Order $order, array $data = [])
    {
        Log::info('PayUMoneyGateway: processPayment called', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        try {
            $merchantKey = $this->getConfig('merchant_key');
            $merchantSalt = $this->getConfig('merchant_salt');

            if (!$merchantKey || !$merchantSalt) {
                throw new \Exception('PayUMoney API credentials not configured');
            }

            // Get customer details
            $billingAddress = $order->billingAddress();
            $customer = $order->customer;

            // Generate unique transaction ID
            $txnId = 'TXN' . $order->id . '_' . time();

            // Prepare payment parameters
            $params = [
                'key' => $merchantKey,
                'txnid' => $txnId,
                'amount' => number_format($order->grand_total, 2, '.', ''),
                'productinfo' => 'Order #' . $order->order_number,
                'firstname' => $billingAddress->first_name,
                'lastname' => $billingAddress->last_name,
                'email' => $customer->email,
                'phone' => $billingAddress->phone,
                'address1' => $billingAddress->address_line_1,
                'address2' => $billingAddress->address_line_2 ?? '',
                'city' => $billingAddress->city,
                'state' => $billingAddress->state,
                'country' => $billingAddress->country_code,
                'zipcode' => $billingAddress->postcode,
                'surl' => route('payumoney.callback'),
                'furl' => route('payumoney.callback'),
                'curl' => route('payumoney.callback'),
                'udf1' => (string) $order->id, // Store order ID for callback
                'udf2' => $order->order_number,
                'udf3' => '',
                'udf4' => '',
                'udf5' => '',
            ];

            // Generate hash
            $params['hash'] = $this->generateHash($params);

            Log::info('PayUMoneyGateway: Payment parameters prepared', [
                'order_id' => $order->id,
                'txnid' => $txnId,
                'amount' => $params['amount'],
            ]);

            // Store transaction ID in order metadata
            $order->update([
                'payment_gateway_data' => [
                    'txnid' => $txnId,
                    'status' => 'pending',
                ],
            ]);

            // Return payment data for frontend form submission
            return [
                'success' => true,
                'gateway_type' => 'form_post',
                'payment_data' => [
                    'gateway_code' => $this->getCode(),
                    'action_url' => $this->getPaymentUrl(),
                    'method' => 'POST',
                    'params' => $params,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('PayUMoneyGateway: Payment processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('PayUMoney payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment callback from PayUMoney.
     */
    public function handleCallback(array $data): array
    {
        Log::info('PayUMoneyGateway: handleCallback called', $data);

        try {
            // Verify hash
            if (!$this->verifyHash($data)) {
                throw new \Exception('Invalid payment response hash');
            }

            $orderId = $data['udf1'] ?? null;
            $status = $data['status'] ?? '';

            if (!$orderId) {
                throw new \Exception('Order ID not found in callback');
            }

            $order = Order::find($orderId);
            if (!$order) {
                throw new \Exception('Order not found');
            }

            Log::info('PayUMoneyGateway: Payment callback verified', [
                'order_id' => $order->id,
                'status' => $status,
                'txnid' => $data['txnid'] ?? '',
            ]);

            if (strcasecmp($status, 'success') === 0) {
                return [
                    'success' => true,
                    'order_id' => $order->id,
                    'transaction_id' => $data['mihpayid'] ?? $data['txnid'],
                    'message' => 'Payment completed successfully',
                    'response_data' => [
                        'txnid' => $data['txnid'] ?? null,
                        'mihpayid' => $data['mihpayid'] ?? null,
                        'status' => $status,
                        'mode' => $data['mode'] ?? null,
                        'bank_ref_num' => $data['bank_ref_num'] ?? null,
                    ],
                ];
            }

            throw new \Exception('Payment failed. Status: ' . $status);
        } catch (\Exception $e) {
            Log::error('PayUMoneyGateway: Callback handling failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment status for an order.
     */
    public function verifyPayment(Order $order): bool
    {
        try {
            $txnId = $order->payment_gateway_data['txnid'] ?? null;

            if (!$txnId) {
                return false;
            }

            // PayUMoney doesn't have a direct verification API
            // Payment verification is done through callback hash verification
            return !empty($order->payment_gateway_data['status']) &&
                   $order->payment_gateway_data['status'] === 'success';
        } catch (\Exception $e) {
            Log::error('PayUMoneyGateway: Payment verification failed', [
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
        Log::info('PayUMoneyGateway: refund called', [
            'order_id' => $order->id,
            'amount' => $amount,
        ]);

        try {
            // PayUMoney requires manual refund processing or separate refund API credentials
            // This is a placeholder implementation
            throw new \Exception('PayUMoney refunds must be processed through the PayU merchant dashboard');

            // If you have refund API access, implement here:
            // $mihpayid = $order->payment_gateway_data['mihpayid'] ?? null;
            // Make API call to PayU refund endpoint

            return [
                'success' => false,
                'message' => 'PayUMoney refunds must be processed manually through the merchant dashboard',
            ];
        } catch (\Exception $e) {
            Log::error('PayUMoneyGateway: Refund failed', [
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
            'merchant_key' => [
                'type' => 'text',
                'label' => 'Merchant Key',
                'required' => true,
                'help' => 'Your PayUMoney Merchant Key',
            ],
            'merchant_salt' => [
                'type' => 'password',
                'label' => 'Merchant Salt',
                'required' => true,
                'help' => 'Your PayUMoney Merchant Salt (used for hash generation)',
            ],
            'mode' => [
                'type' => 'select',
                'label' => 'Mode',
                'required' => true,
                'options' => [
                    'test' => 'Test',
                    'production' => 'Production',
                ],
                'default' => 'test',
                'help' => 'Use test for testing, production for live transactions',
            ],
            'auth_header' => [
                'type' => 'text',
                'label' => 'Authorization Header',
                'required' => false,
                'help' => 'Optional: Authorization header for API requests',
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

        $merchantKey = $method->getConfigValue('merchant_key');
        $merchantSalt = $method->getConfigValue('merchant_salt');

        return !empty($merchantKey) && !empty($merchantSalt);
    }
}
