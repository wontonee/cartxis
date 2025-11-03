<?php

use Illuminate\Support\Facades\Route;
use Vortex\Customer\Http\Controllers\CustomerController;
use Vortex\Customer\Http\Controllers\CustomerGroupController;

Route::middleware(['web', 'auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Customer Groups (must be defined BEFORE customers resource to avoid route conflicts)
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::resource('groups', CustomerGroupController::class);
        
        // Customer group reordering
        Route::post('groups/reorder', [CustomerGroupController::class, 'reorder'])
            ->name('groups.reorder');
        
        // Customer group auto-assignment
        Route::post('groups/{group}/apply-auto-assignment', [CustomerGroupController::class, 'applyAutoAssignment'])
            ->name('groups.apply-auto-assignment');
        
        // Customer group bulk actions
        Route::post('groups/bulk-delete', [CustomerGroupController::class, 'bulkDestroy'])
            ->name('groups.bulk-delete');
        Route::post('groups/bulk-update-status', [CustomerGroupController::class, 'bulkUpdateStatus'])
            ->name('groups.bulk-update-status');
    });
    
    // Customer bulk actions (must be before resource routes)
    Route::post('customers/bulk-update-status', [CustomerController::class, 'bulkUpdateStatus'])
        ->name('customers.bulk-update-status');
    Route::post('customers/bulk-delete', [CustomerController::class, 'bulkDelete'])
        ->name('customers.bulk-delete');
    
    // Customer export (must be before resource routes)
    Route::get('customers/export/csv', [CustomerController::class, 'export'])
        ->name('customers.export');
    
    // Customers resource (should be LAST to avoid catching specific routes)
    Route::resource('customers', CustomerController::class);
});
