<?php

use Illuminate\Support\Facades\Route;
use Cartxis\Setup\Http\Controllers\SetupController;

// Setup wizard routes - accessible only when setup is NOT complete
Route::middleware(['web', 'setup.complete'])->prefix('setup')->name('setup.')->group(function () {
    Route::get('/', [SetupController::class, 'welcome'])->name('welcome');
    Route::get('/business-type', [SetupController::class, 'businessType'])->name('business-type');
    Route::get('/business-settings', [SetupController::class, 'businessSettings'])->name('business-settings');
    Route::post('/save-business-settings', [SetupController::class, 'saveBusinessSettings'])->name('save-business-settings');
    Route::get('/demo-data', [SetupController::class, 'demoData'])->name('demo-data');
    Route::post('/import-demo-data', [SetupController::class, 'importDemoData'])->name('import-demo-data');
    Route::post('/complete', [SetupController::class, 'complete'])->name('complete');
    Route::get('/finish', [SetupController::class, 'finish'])->name('finish');
});
