<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Vortex\CMS\Models\Block;

// Update Features Block
$block = Block::where('identifier', 'homepage-features')->first();
if ($block) {
    $block->content = '<div class="grid grid-cols-2 md:grid-cols-4 gap-6">
    <div class="text-center">
        <div class="text-4xl mb-3">🚚</div>
        <h3 class="font-semibold text-gray-900 mb-1">Free Shipping</h3>
        <p class="text-sm text-gray-600">On orders over $50</p>
    </div>
    <div class="text-center">
        <div class="text-4xl mb-3">🔒</div>
        <h3 class="font-semibold text-gray-900 mb-1">Secure Payment</h3>
        <p class="text-sm text-gray-600">100% secure transactions</p>
    </div>
    <div class="text-center">
        <div class="text-4xl mb-3">💬</div>
        <h3 class="font-semibold text-gray-900 mb-1">24/7 Support</h3>
        <p class="text-sm text-gray-600">Dedicated customer service</p>
    </div>
    <div class="text-center">
        <div class="text-4xl mb-3">↩️</div>
        <h3 class="font-semibold text-gray-900 mb-1">Easy Returns</h3>
        <p class="text-sm text-gray-600">30-day return policy</p>
    </div>
</div>';
    $block->save();
    echo "✓ Features block updated\n";
}

// Update Testimonials Block
$block = Block::where('identifier', 'homepage-testimonials')->first();
if ($block) {
    $block->content = '<div class="text-center mb-12">
    <h2 class="text-3xl font-bold text-gray-900 mb-4">What Our Customers Say</h2>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="text-yellow-400 text-xl mb-3">⭐⭐⭐⭐⭐</div>
        <p class="text-gray-700 mb-4 italic">"Amazing quality and fast shipping! Highly recommend."</p>
        <p class="text-gray-600 font-semibold">- Sarah M.</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="text-yellow-400 text-xl mb-3">⭐⭐⭐⭐⭐</div>
        <p class="text-gray-700 mb-4 italic">"Great customer service and excellent products."</p>
        <p class="text-gray-600 font-semibold">- John D.</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="text-yellow-400 text-xl mb-3">⭐⭐⭐⭐</div>
        <p class="text-gray-700 mb-4 italic">"Love the variety of products. Will shop again!"</p>
        <p class="text-gray-600 font-semibold">- Emily R.</p>
    </div>
</div>';
    $block->save();
    echo "✓ Testimonials block updated\n";
}

// Update Brands Block
$block = Block::where('identifier', 'homepage-brands')->first();
if ($block) {
    $block->content = '<div class="text-center mb-8">
    <h2 class="text-3xl font-bold text-gray-900 mb-4">Trusted Brands</h2>
    <p class="text-gray-600">We partner with leading brands to bring you quality products</p>
</div>
<div class="grid grid-cols-2 md:grid-cols-6 gap-8 items-center">
    <div class="bg-gray-100 rounded-lg p-6 h-24 flex items-center justify-center text-gray-600 font-semibold hover:bg-gray-200 transition">Brand 1</div>
    <div class="bg-gray-100 rounded-lg p-6 h-24 flex items-center justify-center text-gray-600 font-semibold hover:bg-gray-200 transition">Brand 2</div>
    <div class="bg-gray-100 rounded-lg p-6 h-24 flex items-center justify-center text-gray-600 font-semibold hover:bg-gray-200 transition">Brand 3</div>
    <div class="bg-gray-100 rounded-lg p-6 h-24 flex items-center justify-center text-gray-600 font-semibold hover:bg-gray-200 transition">Brand 4</div>
    <div class="bg-gray-100 rounded-lg p-6 h-24 flex items-center justify-center text-gray-600 font-semibold hover:bg-gray-200 transition">Brand 5</div>
    <div class="bg-gray-100 rounded-lg p-6 h-24 flex items-center justify-center text-gray-600 font-semibold hover:bg-gray-200 transition">Brand 6</div>
</div>';
    $block->save();
    echo "✓ Brands block updated\n";
}

echo "\n✅ All CMS blocks updated successfully!\n";
