<?php

use Cartxis\Settings\Http\Controllers\Admin\GeneralSettingsController;
use Cartxis\Settings\Http\Controllers\Admin\StoreConfigurationController;
use Cartxis\Settings\Http\Controllers\Admin\LocalesController;
use Cartxis\Settings\Http\Controllers\Admin\PaymentMethodsController;
use Cartxis\Settings\Http\Controllers\Admin\TaxClassesController;
use Cartxis\Settings\Http\Controllers\Admin\TaxRatesController;
use Cartxis\Settings\Http\Controllers\Admin\TaxZonesController;
use Cartxis\Settings\Http\Controllers\Admin\TaxRulesController;
use Cartxis\Settings\Http\Controllers\Admin\EmailController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:admin'])
    ->prefix('admin/settings')
    ->name('admin.settings.')
    ->group(function () {
        // General Settings
        Route::get('general', [GeneralSettingsController::class, 'index'])->name('general.index');
        Route::post('general', [GeneralSettingsController::class, 'save'])->name('general.save');
        
        // Store Configuration
        Route::get('store', [StoreConfigurationController::class, 'index'])->name('store.index');
        Route::post('store', [StoreConfigurationController::class, 'save'])->name('store.save');
        
        // Locales & Currencies
        Route::get('locales', [LocalesController::class, 'index'])->name('locales.index');
        
        // Locale routes
        Route::post('locales/locale', [LocalesController::class, 'storeLocale'])->name('locales.locale.store');
        Route::put('locales/locale/{locale}', [LocalesController::class, 'updateLocale'])->name('locales.locale.update');
        Route::delete('locales/locale/{locale}', [LocalesController::class, 'destroyLocale'])->name('locales.locale.destroy');
        
        // Currency routes
        Route::post('locales/currency', [LocalesController::class, 'storeCurrency'])->name('locales.currency.store');
        Route::put('locales/currency/{id}', [LocalesController::class, 'updateCurrency'])->name('locales.currency.update');
        Route::delete('locales/currency/{id}', [LocalesController::class, 'destroyCurrency'])->name('locales.currency.destroy');
        
        // Payment Methods
        Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
            Route::get('/', [PaymentMethodsController::class, 'index'])->name('index');
            Route::get('{type}/configure', [PaymentMethodsController::class, 'getConfigure'])->name('configure');
            Route::post('{type}/save', [PaymentMethodsController::class, 'save'])->name('save');
            Route::post('{paymentMethod}/toggle', [PaymentMethodsController::class, 'toggle'])->name('toggle');
            Route::post('{paymentMethod}/set-default', [PaymentMethodsController::class, 'setDefault'])->name('set-default');
            Route::put('{paymentMethod}/sort', [PaymentMethodsController::class, 'sort'])->name('sort');
        });

        // Tax Rules
        Route::prefix('tax-rules')->name('tax-rules.')->group(function () {
            Route::get('/', [TaxRulesController::class, 'index'])->name('index');
            Route::get('create', [TaxRulesController::class, 'create'])->name('create');
            Route::post('/', [TaxRulesController::class, 'store'])->name('store');
            Route::get('{id}/edit', [TaxRulesController::class, 'edit'])->name('edit');
            Route::put('{id}', [TaxRulesController::class, 'update'])->name('update');
            Route::delete('{id}', [TaxRulesController::class, 'destroy'])->name('destroy');
            Route::post('bulk-destroy', [TaxRulesController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::post('bulk-status', [TaxRulesController::class, 'bulkStatus'])->name('bulk-status');
        });

        // Tax Classes
        Route::prefix('tax-classes')->name('tax-classes.')->group(function () {
            Route::get('/', [TaxClassesController::class, 'index'])->name('index');
            Route::post('/', [TaxClassesController::class, 'store'])->name('store');
            Route::put('{id}', [TaxClassesController::class, 'update'])->name('update');
            Route::delete('{id}', [TaxClassesController::class, 'destroy'])->name('destroy');
        });

        // Tax Rates
        Route::prefix('tax-rates')->name('tax-rates.')->group(function () {
            Route::get('/', [TaxRatesController::class, 'index'])->name('index');
            Route::post('/', [TaxRatesController::class, 'store'])->name('store');
            Route::put('{id}', [TaxRatesController::class, 'update'])->name('update');
            Route::delete('{id}', [TaxRatesController::class, 'destroy'])->name('destroy');
        });

        // Tax Zones
        Route::prefix('tax-zones')->name('tax-zones.')->group(function () {
            Route::get('/', [TaxZonesController::class, 'index'])->name('index');
            Route::post('/', [TaxZonesController::class, 'store'])->name('store');
            Route::put('{id}', [TaxZonesController::class, 'update'])->name('update');
            Route::delete('{id}', [TaxZonesController::class, 'destroy'])->name('destroy');
        });

        // Email Settings
        Route::prefix('email')->name('email.')->group(function () {
            Route::get('/', [EmailController::class, 'index'])->name('index');
            Route::post('/configuration', [EmailController::class, 'saveConfiguration'])->name('configuration.save');
            Route::post('/test-connection', [EmailController::class, 'testConnection'])->name('test-connection');
            Route::post('/send-test', [EmailController::class, 'sendTestEmail'])->name('send-test');
            Route::put('/templates/{id}', [EmailController::class, 'updateTemplate'])->name('templates.update');
            Route::post('/templates/{id}/toggle', [EmailController::class, 'toggleTemplate'])->name('templates.toggle');
            Route::get('/templates/{id}/preview', [EmailController::class, 'previewTemplate'])->name('templates.preview');
            Route::post('/templates/{id}/send-test', [EmailController::class, 'sendTestTemplate'])->name('templates.send-test');
        });
    });

