<?php

use Illuminate\Support\Facades\Route;
use Vortex\Reports\Http\Controllers\SalesReportController;
use Vortex\Reports\Http\Controllers\ProductReportController;
use Vortex\Reports\Http\Controllers\CustomerReportController;

Route::middleware(['web', 'auth:admin'])
    ->prefix('admin/reports')
    ->name('admin.reports.')
    ->group(function () {
        // Sales Reports
        Route::get('/sales', [SalesReportController::class, 'index'])->name('sales');
        Route::post('/sales/export', [SalesReportController::class, 'export'])->name('sales.export');

        // Product Reports
        Route::get('/products', [ProductReportController::class, 'index'])->name('products');
        Route::post('/products/export', [ProductReportController::class, 'export'])->name('products.export');

        // Customer Reports
        Route::get('/customers', [CustomerReportController::class, 'index'])->name('customers');
        Route::post('/customers/export', [CustomerReportController::class, 'export'])->name('customers.export');
    });
