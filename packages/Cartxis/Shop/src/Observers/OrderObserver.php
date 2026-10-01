<?php

namespace Cartxis\Shop\Observers;

use Cartxis\Shop\Models\Order;
use Cartxis\Shop\Services\OrderDownloadService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function __construct(protected OrderDownloadService $downloadService) {}

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('payment_status')) {
            return;
        }

        if ($order->payment_status !== Order::PAYMENT_PAID) {
            return;
        }

        try {
            $this->downloadService->generateForOrder($order);
        } catch (\Throwable $e) {
            Log::error('Failed to generate order downloads', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
