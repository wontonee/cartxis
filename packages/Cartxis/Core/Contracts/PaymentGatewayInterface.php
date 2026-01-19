<?php

namespace Cartxis\Core\Contracts;

use Cartxis\Shop\Models\Order;

/**
 * Payment Gateway Interface
 * 
 * All payment gateway extensions must implement this interface.
 * This allows the core system to remain flexible and extensible.
 */
interface PaymentGatewayInterface
{
    /**
     * Get the gateway code (e.g., 'stripe', 'paypal', 'razorpay').
     */
    public function getCode(): string;

    /**
     * Get the gateway display name.
     */
    public function getName(): string;

    /**
     * Check if this gateway can handle the given payment method.
     */
    public function supports(string $paymentMethod): bool;

    /**
     * Process payment for an order.
     * 
     * This method should either:
     * 1. Return a redirect response to the gateway's payment page
     * 2. Return success response if payment completed immediately (e.g., COD)
     * 3. Throw exception if payment failed
     * 
     * @param Order $order The order to process payment for
     * @param array $data Additional payment data from checkout
     * @return \Illuminate\Http\RedirectResponse|array
     */
    public function processPayment(Order $order, array $data = []);

    /**
     * Handle payment callback/webhook from the gateway.
     * 
     * @param array $data Callback data from the gateway
     * @return array ['success' => bool, 'order_id' => int, 'message' => string]
     */
    public function handleCallback(array $data): array;

    /**
     * Verify payment status for an order.
     * 
     * @param Order $order
     * @return bool True if payment is confirmed
     */
    public function verifyPayment(Order $order): bool;

    /**
     * Process refund for an order.
     * 
     * @param Order $order
     * @param float $amount Amount to refund (null for full refund)
     * @param string|null $reason Refund reason
     * @return array ['success' => bool, 'transaction_id' => string, 'message' => string]
     */
    public function refund(Order $order, ?float $amount = null, ?string $reason = null): array;

    /**
     * Get configuration fields required by this gateway.
     * Used in admin panel to configure the gateway.
     * 
     * @return array
     */
    public function getConfigFields(): array;

    /**
     * Check if gateway is properly configured and ready to use.
     */
    public function isConfigured(): bool;
}
