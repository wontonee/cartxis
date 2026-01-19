<?php

namespace Cartxis\Core\Services;

use Cartxis\Core\Contracts\PaymentGatewayInterface;
use Cartxis\Shop\Models\Order;
use Illuminate\Support\Collection;

/**
 * Payment Gateway Manager
 * 
 * Central registry for all payment gateway extensions.
 * Extensions register themselves during service provider boot.
 */
class PaymentGatewayManager
{
    /**
     * Registered payment gateways.
     * 
     * @var Collection<string, PaymentGatewayInterface>
     */
    protected Collection $gateways;

    public function __construct()
    {
        $this->gateways = collect();
    }

    /**
     * Register a payment gateway.
     */
    public function register(PaymentGatewayInterface $gateway): void
    {
        $this->gateways->put($gateway->getCode(), $gateway);
    }

    /**
     * Get a gateway by code.
     */
    public function get(string $code): ?PaymentGatewayInterface
    {
        return $this->gateways->get($code);
    }

    /**
     * Get a gateway that supports the given payment method.
     */
    public function getByPaymentMethod(string $paymentMethod): ?PaymentGatewayInterface
    {
        return $this->gateways->first(function (PaymentGatewayInterface $gateway) use ($paymentMethod) {
            return $gateway->supports($paymentMethod);
        });
    }

    /**
     * Get all registered gateways.
     * 
     * @return Collection<string, PaymentGatewayInterface>
     */
    public function all(): Collection
    {
        return $this->gateways;
    }

    /**
     * Get all configured and ready gateways.
     */
    public function getConfigured(): Collection
    {
        return $this->gateways->filter(function (PaymentGatewayInterface $gateway) {
            return $gateway->isConfigured();
        });
    }

    /**
     * Check if a gateway is registered.
     */
    public function has(string $code): bool
    {
        return $this->gateways->has($code);
    }

    /**
     * Process payment for an order using the appropriate gateway.
     */
    public function processPayment(Order $order, array $data = [])
    {
        $paymentMethod = $order->payment_method;

        $gateway = $this->getByPaymentMethod($paymentMethod);

        if (!$gateway) {
            throw new \Exception("No payment gateway found for method: {$paymentMethod}");
        }

        if (!$gateway->isConfigured()) {
            throw new \Exception("Payment gateway {$gateway->getCode()} is not properly configured");
        }

        return $gateway->processPayment($order, $data);
    }

    /**
     * Verify payment for an order.
     */
    public function verifyPayment(Order $order): bool
    {
        $gateway = $this->getByPaymentMethod($order->payment_method);

        if (!$gateway) {
            return false;
        }

        return $gateway->verifyPayment($order);
    }

    /**
     * Process refund for an order.
     */
    public function refund(Order $order, ?float $amount = null, ?string $reason = null): array
    {
        $gateway = $this->getByPaymentMethod($order->payment_method);

        if (!$gateway) {
            throw new \Exception("No payment gateway found for order payment method: {$order->payment_method}");
        }

        return $gateway->refund($order, $amount, $reason);
    }
}
