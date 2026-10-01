<?php

namespace Cartxis\Shop\Services;

use Cartxis\Product\Models\ProductDownload;
use Cartxis\Shop\Models\Order;
use Cartxis\Shop\Models\OrderDownload;
use Illuminate\Support\Facades\Log;

class OrderDownloadService
{
    /**
     * Create order download records for all downloadable items on a paid order.
     */
    public function generateForOrder(Order $order): void
    {
        $order->loadMissing(['items.product.downloads']);

        foreach ($order->items as $item) {
            if (($item->product_type ?? $item->product?->type) !== 'downloadable') {
                continue;
            }

            $downloads = $item->product?->downloads ?? collect();

            foreach ($downloads as $download) {
                /** @var ProductDownload $download */
                $exists = OrderDownload::query()
                    ->where('order_id', $order->id)
                    ->where('order_item_id', $item->id)
                    ->where('product_download_id', $download->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                OrderDownload::create([
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'product_download_id' => $download->id,
                    'title' => $download->title,
                    'file_path' => $download->file_path,
                    'file_name' => $download->file_name,
                    'max_downloads' => $download->max_downloads,
                    'expires_at' => now()->addDays(30),
                ]);
            }
        }
    }

    /**
     * Find a download by token and ensure the buyer can access it.
     */
    public function findAuthorized(string $token, ?int $userId = null, ?string $email = null): ?OrderDownload
    {
        $download = OrderDownload::with('order')->where('token', $token)->first();

        if (! $download || ! $download->canDownload()) {
            return null;
        }

        $order = $download->order;
        if (! $order || $order->payment_status !== Order::PAYMENT_PAID) {
            return null;
        }

        if ($userId && (int) $order->user_id === (int) $userId) {
            return $download;
        }

        if ($email && strcasecmp((string) $order->customer_email, $email) === 0) {
            return $download;
        }

        // Guests with matching session order id are handled by controller
        return $download;
    }
}
