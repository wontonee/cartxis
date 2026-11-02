<?php

declare(strict_types=1);

use Cartxis\Core\Http\Controllers\LocaleSwitchController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/locale/{locale}', LocaleSwitchController::class)
        ->where('locale', '[a-z]{2,5}')
        ->name('locale.switch');
});
