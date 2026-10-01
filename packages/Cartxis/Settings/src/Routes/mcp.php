<?php

declare(strict_types=1);

use Cartxis\Settings\Http\Controllers\McpController;
use Cartxis\Settings\Mcp\Middleware\AuthenticateMcpToken;
use Cartxis\Settings\Mcp\Middleware\EnsureMcpEnabled;
use Illuminate\Support\Facades\Route;

// Enabled gate runs before auth so disabled MCP always returns HTTP 503.
Route::middleware(['throttle:60,1', EnsureMcpEnabled::class, AuthenticateMcpToken::class])
    ->group(function () {
        Route::post('/mcp', [McpController::class, 'handle'])->name('mcp.handle');
        Route::get('/mcp', [McpController::class, 'info'])->name('mcp.info');
    });
