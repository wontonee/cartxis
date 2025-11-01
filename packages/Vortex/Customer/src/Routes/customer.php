<?php

use Illuminate\Support\Facades\Route;
use Vortex\Customer\Http\Controllers\CustomerController;

Route::middleware(['web', 'auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Customers
    Route::resource('customers', CustomerController::class);
    
    // Customer bulk actions
    Route::post('customers/bulk-update-status', [CustomerController::class, 'bulkUpdateStatus'])
        ->name('customers.bulk-update-status');
    Route::post('customers/bulk-delete', [CustomerController::class, 'bulkDelete'])
        ->name('customers.bulk-delete');
    
    // Customer export
    Route::get('customers/export/csv', [CustomerController::class, 'export'])
        ->name('customers.export');
});
