<?php

namespace Cartxis\Product\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Product\Models\Product;
use Cartxis\Product\Models\ProductDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductDownloadController extends Controller
{
    public function store(Request $request, Product $product)
    {
        if ($product->type !== Product::TYPE_DOWNLOADABLE) {
            return back()->with('error', 'Downloads can only be attached to downloadable products.');
        }

        $validated = $request->validate([
            'file' => 'required|file|max:51200', // 50MB
            'title' => 'nullable|string|max:255',
            'max_downloads' => 'nullable|integer|min:1',
        ]);

        $file = $validated['file'];
        $path = $file->storeAs(
            'product-downloads/'.$product->id,
            Str::uuid()->toString().'_'.$file->getClientOriginalName(),
            'local'
        );

        $download = ProductDownload::create([
            'product_id' => $product->id,
            'title' => $validated['title'] ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'max_downloads' => $validated['max_downloads'] ?? null,
            'sort_order' => (int) $product->downloads()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Download file uploaded successfully.');
    }

    public function destroy(Product $product, ProductDownload $download)
    {
        if ($download->product_id !== $product->id) {
            abort(404);
        }

        if ($download->file_path && Storage::disk('local')->exists($download->file_path)) {
            Storage::disk('local')->delete($download->file_path);
        }

        $download->delete();

        return back()->with('success', 'Download file removed.');
    }

    public function update(Request $request, Product $product, ProductDownload $download)
    {
        if ($download->product_id !== $product->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'max_downloads' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $download->update($validated);

        return back()->with('success', 'Download updated.');
    }
}
