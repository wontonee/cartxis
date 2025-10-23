<?php

namespace Vortex\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Vortex\Product\Models\Product;
use App\Models\User;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::take(5)->pluck('id')->toArray();
        $users = User::where('role', 'customer')->take(3)->pluck('id')->toArray();

        if (empty($products)) {
            $this->command->warn('No products found. Please seed products first.');
            return;
        }

        $reviews = [
            [
                'product_id' => $products[0] ?? 1,
                'user_id' => $users[0] ?? null,
                'reviewer_name' => 'John Smith',
                'reviewer_email' => 'john@example.com',
                'rating' => 5,
                'title' => 'Excellent Product!',
                'comment' => 'This product exceeded my expectations. The quality is outstanding and it arrived quickly. Highly recommend!',
                'status' => 'approved',
                'helpful_count' => 12,
                'verified_purchase' => true,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'product_id' => $products[0] ?? 1,
                'user_id' => $users[1] ?? null,
                'reviewer_name' => 'Sarah Johnson',
                'reviewer_email' => 'sarah@example.com',
                'rating' => 4,
                'title' => 'Good value for money',
                'comment' => 'Overall satisfied with the purchase. Good quality and fast shipping. Would buy again.',
                'status' => 'approved',
                'admin_reply' => 'Thank you for your feedback! We appreciate your business.',
                'admin_reply_by' => 1,
                'admin_replied_at' => now()->subDays(8),
                'helpful_count' => 8,
                'verified_purchase' => true,
                'created_at' => now()->subDays(9),
                'updated_at' => now()->subDays(8),
            ],
            [
                'product_id' => $products[1] ?? 1,
                'user_id' => $users[2] ?? null,
                'reviewer_name' => 'Mike Brown',
                'reviewer_email' => 'mike@example.com',
                'rating' => 3,
                'title' => 'Average product',
                'comment' => 'The product is okay, nothing special. It does what it\'s supposed to do but I expected better quality for the price.',
                'status' => 'approved',
                'helpful_count' => 5,
                'verified_purchase' => true,
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            [
                'product_id' => $products[1] ?? 1,
                'user_id' => null,
                'reviewer_name' => 'Emily Davis',
                'reviewer_email' => 'emily@example.com',
                'rating' => 5,
                'title' => 'Love it!',
                'comment' => 'Absolutely love this product! Best purchase I\'ve made this year. Will definitely recommend to friends and family.',
                'status' => 'pending',
                'helpful_count' => 0,
                'verified_purchase' => false,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'product_id' => $products[2] ?? 1,
                'user_id' => null,
                'reviewer_name' => 'David Wilson',
                'reviewer_email' => 'david@example.com',
                'rating' => 2,
                'title' => 'Not as described',
                'comment' => 'Product looks different from the pictures. Quality is not great. Disappointed with this purchase.',
                'status' => 'pending',
                'helpful_count' => 0,
                'verified_purchase' => false,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'product_id' => $products[2] ?? 1,
                'user_id' => null,
                'reviewer_name' => 'Lisa Anderson',
                'reviewer_email' => 'lisa@example.com',
                'rating' => 1,
                'title' => 'Poor quality',
                'comment' => 'Very disappointed. The product broke after just one use. Would not recommend.',
                'status' => 'rejected',
                'helpful_count' => 3,
                'verified_purchase' => false,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(4),
            ],
            [
                'product_id' => $products[3] ?? 1,
                'user_id' => $users[0] ?? null,
                'reviewer_name' => 'Robert Taylor',
                'reviewer_email' => 'robert@example.com',
                'rating' => 5,
                'title' => 'Perfect!',
                'comment' => 'Exactly what I was looking for. Great quality, fast shipping, and excellent customer service.',
                'status' => 'approved',
                'admin_reply' => 'We\'re so glad you love it! Thank you for your kind words.',
                'admin_reply_by' => 1,
                'admin_replied_at' => now()->subDays(3),
                'helpful_count' => 15,
                'verified_purchase' => true,
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(3),
            ],
            [
                'product_id' => $products[4] ?? 1,
                'user_id' => null,
                'reviewer_name' => 'Jennifer Martinez',
                'reviewer_email' => 'jennifer@example.com',
                'rating' => 4,
                'title' => 'Satisfied customer',
                'comment' => 'Good product overall. Delivery was a bit slow but the quality makes up for it.',
                'status' => 'approved',
                'helpful_count' => 6,
                'verified_purchase' => false,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
        ];

        foreach ($reviews as $review) {
            DB::table('product_reviews')->insert($review);
        }

        $this->command->info('✓ Product reviews seeded successfully!');
    }
}
