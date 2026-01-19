<?php

namespace Cartxis\Reports\Repositories;

use Illuminate\Support\Facades\DB;
use Cartxis\Product\Models\Product;
use Cartxis\Shop\Models\OrderItem;

class ProductReportRepository
{
    /**
     * Get best selling products
     */
    public function getBestSellers(string $startDate, string $endDate, int $limit = 10): array
    {
        return OrderItem::select([
            'order_items.product_id',
            'products.name',
            'products.sku',
            DB::raw('SUM(order_items.quantity) as total_quantity'),
            DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'),
            DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
        ])
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->join('products', 'order_items.product_id', '=', 'products.id')
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->whereIn('orders.status', ['processing', 'completed'])
        ->groupBy('order_items.product_id', 'products.name', 'products.sku')
        ->orderByDesc('total_quantity')
        ->limit($limit)
        ->get()
        ->toArray();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10): array
    {
        return Product::select([
            'id',
            'name',
            'sku',
            'quantity',
            'status'
        ])
        ->where('status', 'enabled')
        ->where('quantity', '<=', $threshold)
        ->where('quantity', '>', 0)
        ->orderBy('quantity', 'asc')
        ->limit(20)
        ->get()
        ->toArray();
    }

    /**
     * Get out of stock products
     */
    public function getOutOfStockProducts(): array
    {
        return Product::select([
            'id',
            'name',
            'sku',
            'quantity',
            'status'
        ])
        ->where('status', 'enabled')
        ->where('quantity', '<=', 0)
        ->orderBy('updated_at', 'desc')
        ->limit(20)
        ->get()
        ->toArray();
    }

    /**
     * Get category performance
     */
    public function getCategoryPerformance(string $startDate, string $endDate): array
    {
        return OrderItem::select([
            'categories.id as category_id',
            'categories.name as category_name',
            DB::raw('SUM(order_items.quantity) as total_quantity'),
            DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'),
            DB::raw('COUNT(DISTINCT order_items.product_id) as product_count'),
            DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
        ])
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->join('products', 'order_items.product_id', '=', 'products.id')
        ->join('category_product', 'products.id', '=', 'category_product.product_id')
        ->join('categories', 'category_product.category_id', '=', 'categories.id')
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->whereIn('orders.status', ['processing', 'completed'])
        ->groupBy('categories.id', 'categories.name')
        ->orderByDesc('total_revenue')
        ->get()
        ->toArray();
    }

    /**
     * Get slow moving products (products with low sales)
     */
    public function getSlowMovingProducts(string $startDate, string $endDate, int $limit = 10): array
    {
        return Product::select([
            'products.id',
            'products.name',
            'products.sku',
            'products.quantity',
            'products.price',
            DB::raw('COALESCE(SUM(order_items.quantity), 0) as sold_quantity'),
            DB::raw('COALESCE(COUNT(DISTINCT order_items.order_id), 0) as order_count')
        ])
        ->leftJoin('order_items', function($join) use ($startDate, $endDate) {
            $join->on('products.id', '=', 'order_items.product_id')
                ->whereExists(function($query) use ($startDate, $endDate) {
                    $query->select(DB::raw(1))
                        ->from('orders')
                        ->whereColumn('orders.id', 'order_items.order_id')
                        ->whereBetween('orders.created_at', [$startDate, $endDate])
                        ->whereIn('orders.status', ['processing', 'completed']);
                });
        })
        ->where('products.status', 'enabled')
        ->where('products.quantity', '>', 0)
        ->groupBy('products.id', 'products.name', 'products.sku', 'products.quantity', 'products.price')
        ->havingRaw('COALESCE(SUM(order_items.quantity), 0) < 5')
        ->orderBy('sold_quantity', 'asc')
        ->limit($limit)
        ->get()
        ->toArray();
    }

    /**
     * Get product sales trend over time
     */
    public function getProductSalesTrend(string $startDate, string $endDate): array
    {
        return OrderItem::select([
            DB::raw('DATE(orders.created_at) as date'),
            DB::raw('SUM(order_items.quantity) as quantity'),
            DB::raw('SUM(order_items.price * order_items.quantity) as revenue'),
            DB::raw('COUNT(DISTINCT order_items.product_id) as unique_products')
        ])
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->whereIn('orders.status', ['processing', 'completed'])
        ->groupBy(DB::raw('DATE(orders.created_at)'))
        ->orderBy('date', 'asc')
        ->get()
        ->toArray();
    }

    /**
     * Get product statistics
     */
    public function getProductStatistics(string $startDate, string $endDate): array
    {
        $stats = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('orders.status', ['processing', 'completed'])
            ->select([
                DB::raw('SUM(order_items.quantity) as total_quantity_sold'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_items.product_id) as unique_products_sold'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as total_orders')
            ])
            ->first();

        $totalProducts = Product::where('status', 'enabled')->count();
        $lowStockCount = Product::where('status', 'enabled')
            ->where('quantity', '<=', 10)
            ->where('quantity', '>', 0)
            ->count();
        $outOfStockCount = Product::where('status', 'enabled')
            ->where('quantity', '<=', 0)
            ->count();

        return [
            'total_quantity_sold' => $stats->total_quantity_sold ?? 0,
            'total_revenue' => $stats->total_revenue ?? 0,
            'unique_products_sold' => $stats->unique_products_sold ?? 0,
            'total_orders' => $stats->total_orders ?? 0,
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'avg_revenue_per_product' => $stats->unique_products_sold > 0 
                ? ($stats->total_revenue / $stats->unique_products_sold) 
                : 0,
        ];
    }
}
