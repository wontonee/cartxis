<?php

use Vortex\Settings\Http\Controllers\Admin\GeneralSettingsController;
use Vortex\Settings\Http\Controllers\Admin\StoreConfigurationController;
use Vortex\Settings\Http\Controllers\Admin\LocalesController;
use Vortex\Settings\Http\Controllers\Admin\PaymentMethodsController;
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
    });

