<?php

namespace Cartxis\Shop\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Cartxis\Core\Services\ThemeViewResolver;
use Cartxis\Shop\Models\OrderDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __construct(protected ThemeViewResolver $themeResolver) {}

    public function index(Request $request): Response
    {
        $user = Auth::user();

        $downloads = OrderDownload::query()
            ->with('order')
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('customer_email', $user->email);
            })
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'))
            ->latest()
            ->get()
            ->map(fn (OrderDownload $d) => [
                'id' => $d->id,
                'title' => $d->title,
                'file_name' => $d->file_name,
                'order_number' => $d->order?->order_number,
                'download_count' => $d->download_count,
                'max_downloads' => $d->max_downloads,
                'expires_at' => $d->expires_at?->toIso8601String(),
                'can_download' => $d->canDownload(),
                'token' => $d->token,
                'download_url' => route('shop.account.downloads.file', $d->token),
            ]);

        return Inertia::render($this->themeResolver->resolve('Account/Downloads/Index'), [
            'downloads' => $downloads,
        ]);
    }

    public function download(Request $request, string $token): StreamedResponse
    {
        $user = Auth::user();

        $download = OrderDownload::with('order')->where('token', $token)->firstOrFail();
        $order = $download->order;

        $authorized = $order
            && $order->payment_status === 'paid'
            && (
                (int) $order->user_id === (int) $user->id
                || strcasecmp((string) $order->customer_email, (string) $user->email) === 0
            );

        if (! $authorized || ! $download->canDownload()) {
            abort(403, 'This download is unavailable or has expired.');
        }

        $download->recordDownload();

        return Storage::disk('local')->download(
            $download->file_path,
            $download->file_name ?: basename($download->file_path)
        );
    }
}
