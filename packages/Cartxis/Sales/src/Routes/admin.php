<?php

use Illuminate\Support\Facades\Route;
use Cartxis\Sales\Http\Controllers\Admin\OrderController;
use Cartxis\Sales\Http\Controllers\Admin\InvoiceController;
use Cartxis\Sales\Http\Controllers\Admin\ShipmentController;
use Cartxis\Sales\Http\Controllers\Admin\CreditMemoController;
use Cartxis\Sales\Http\Controllers\Admin\TransactionController;

/*
|--------------------------------------------------------------------------
| Sales Admin Routes
|--------------------------------------------------------------------------
|
| All routes for the Sales module. These routes are automatically loaded
| by the SalesServiceProvider and are prefixed with 'admin/sales'.
|
*/

Route::middleware(['web', 'auth:admin'])->prefix('admin/sales')->name('admin.sales.')->group(function () {
    
    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        // List orders
        Route::get('/', [OrderController::class, 'index'])->name('index');
        
        // Create new order
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        
        // View order details
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        
        // Update order status
        Route::post('/{id}/status', [OrderController::class, 'updateStatus'])->name('update-status');
        
        // Update payment status
        Route::post('/{id}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('update-payment-status');
        
        // Cancel order
        Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        
        // Bulk actions
        Route::post('/bulk/status', [OrderController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        
        // Export orders
        Route::get('/export/csv', [OrderController::class, 'export'])->name('export');
        
        // Print documents
        Route::get('/{id}/invoice', [OrderController::class, 'printInvoice'])->name('print-invoice');
        Route::get('/{id}/packing-slip', [OrderController::class, 'printPackingSlip'])->name('print-packing-slip');
        
        // Statistics (for dashboard widgets)
        Route::get('/api/statistics', [OrderController::class, 'statistics'])->name('statistics');
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        // List invoices
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        
        // View invoice details
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        
        // Create invoice from order
        Route::post('/create-from-order/{orderId}', [InvoiceController::class, 'createFromOrder'])->name('create-from-order');
        
        // Update invoice
        Route::put('/{id}', [InvoiceController::class, 'update'])->name('update');
        
        // Mark as sent
        Route::post('/{id}/mark-as-sent', [InvoiceController::class, 'markAsSent'])->name('mark-as-sent');
        
        // Mark as paid
        Route::post('/{id}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('mark-as-paid');
        
        // Cancel invoice
        Route::post('/{id}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
        
        // Delete invoice
        Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('destroy');
        
        // Download PDF
        Route::get('/{id}/download-pdf', [InvoiceController::class, 'downloadPdf'])->name('download-pdf');
        
        // Send email
        Route::post('/{id}/send-email', [InvoiceController::class, 'sendEmail'])->name('send-email');
    });

    // Shipments
    Route::prefix('shipments')->name('shipments.')->group(function () {
        // List shipments
        Route::get('/', [ShipmentController::class, 'index'])->name('index');
        
        // Create shipment form
        Route::get('/create', [ShipmentController::class, 'create'])->name('create');
        
        // Store new shipment
        Route::post('/', [ShipmentController::class, 'store'])->name('store');
        
        // View shipment details
        Route::get('/{id}', [ShipmentController::class, 'show'])->name('show');
        
        // Edit shipment form
        Route::get('/{id}/edit', [ShipmentController::class, 'edit'])->name('edit');
        
        // Update shipment
        Route::put('/{id}', [ShipmentController::class, 'update'])->name('update');
        
        // Update tracking information
        Route::post('/{id}/update-tracking', [ShipmentController::class, 'updateTracking'])->name('update-tracking');
        
        // Mark as shipped
        Route::post('/{id}/mark-as-shipped', [ShipmentController::class, 'markAsShipped'])->name('mark-as-shipped');
        
        // Update status
        Route::post('/{id}/update-status', [ShipmentController::class, 'updateStatus'])->name('update-status');
        
        // Cancel shipment
        Route::post('/{id}/cancel', [ShipmentController::class, 'cancel'])->name('cancel');
        
        // Print shipping label
        Route::get('/{id}/print-label', [ShipmentController::class, 'printLabel'])->name('print-label');

        // Shiprocket integration
        Route::post('/{id}/shiprocket/create', [ShipmentController::class, 'createInShiprocket'])->name('shiprocket.create');
        Route::post('/{id}/shiprocket/sync', [ShipmentController::class, 'syncShiprocketTracking'])->name('shiprocket.sync');
    });

    // Credit Memos
    Route::prefix('credit-memos')->name('credit-memos.')->group(function () {
        // List credit memos
        Route::get('/', [CreditMemoController::class, 'index'])->name('index');
        
        // Create credit memo form
        Route::get('/create/{orderId}', [CreditMemoController::class, 'create'])->name('create');
        
        // Store new credit memo
        Route::post('/', [CreditMemoController::class, 'store'])->name('store');
        
        // View credit memo details
        Route::get('/{id}', [CreditMemoController::class, 'show'])->name('show');
        
        // Edit credit memo form
        Route::get('/{id}/edit', [CreditMemoController::class, 'edit'])->name('edit');
        
        // Update credit memo
        Route::put('/{id}', [CreditMemoController::class, 'update'])->name('update');
        
        // Delete credit memo
        Route::delete('/{id}', [CreditMemoController::class, 'destroy'])->name('destroy');
        
        // Process refund
        Route::post('/{id}/process-refund', [CreditMemoController::class, 'processRefund'])->name('process-refund');
        
        // Cancel credit memo
        Route::post('/{id}/cancel', [CreditMemoController::class, 'cancel'])->name('cancel');
        
        // Send email
        Route::post('/{id}/send-email', [CreditMemoController::class, 'sendEmail'])->name('send-email');
        
        // Download PDF
        Route::get('/{id}/download-pdf', [CreditMemoController::class, 'downloadPdf'])->name('download-pdf');
        
        // Bulk delete
        Route::post('/bulk-delete', [CreditMemoController::class, 'bulkDelete'])->name('bulk-delete');
    });

    // Transactions
    Route::prefix('transactions')->name('transactions.')->group(function () {
        // List transactions
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        
        // View transaction details
        Route::get('/{id}', [TransactionController::class, 'show'])->name('show');
        
        // Process refund for a transaction
        Route::post('/{id}/refund', [TransactionController::class, 'refund'])->name('refund');
        
        // Retry a failed transaction
        Route::post('/{id}/retry', [TransactionController::class, 'retry'])->name('retry');
        
        // Cancel a pending transaction
        Route::post('/{id}/cancel', [TransactionController::class, 'cancel'])->name('cancel');
        
        // Export transactions
        Route::get('/export/csv', [TransactionController::class, 'export'])->name('export');
    });

});
