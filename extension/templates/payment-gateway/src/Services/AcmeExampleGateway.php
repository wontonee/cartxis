<?php

namespace Acme\ExampleGateway\Services;

use Cartxis\Core\Contracts\PaymentGatewayInterface;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Shop\Models\Order;

/**
 * Template gateway implementation.
 *
 * Replace with real API integration.
 */
class AcmeExampleGateway implements PaymentGatewayInterface
{
    protected ?PaymentMethod $paymentMethod = null;

    public function getCode(): string
    {
        return 'acme_example';
    }

    public function getName(): string
    {
        return 'Acme Example';
    }

    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'acme_example';
    }

    public function isConfigured(): bool
    {
        $method = $this->getPaymentMethod();
        return (bool) $method?->getConfigValue('api_key') && (bool) $method?->getConfigValue('api_secret');
    }

    public function processPayment(Order $order, array $data = [])
    {
        throw new \Exception('AcmeExampleGateway is a template and is not implemented yet.');
    }

    public function verifyPayment(Order $order): bool
    {
        return false;
    }

    public function refund(Order $order, ?float $amount = null, ?string $reason = null): array
    {
        return ['success' => false, 'message' => 'Refund not implemented for template gateway.'];
    }

    protected function getPaymentMethod(): ?PaymentMethod
    {
        if (!$this->paymentMethod) {
            $this->paymentMethod = PaymentMethod::where('code', $this->getCode())
                ->where('is_active', true)
                ->first();
        }

        return $this->paymentMethod;
    }
}
