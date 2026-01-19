<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Cartxis\Admin\Http\Middleware\PreventAdminFrontendAccess;
use Cartxis\Admin\Http\Middleware\PreventUserAdminAccess;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Define API rate limiters
            RateLimiter::for('api', function (Request $request) {
                return $request->user()
                    ? Limit::perMinute(300)->by($request->user()->id)
                    : Limit::perMinute(60)->by($request->ip());
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            PreventAdminFrontendAccess::class, // Prevent admins from accessing frontend
        ]);

        // Add middleware to admin routes to prevent regular users
        $middleware->alias([
            'prevent.user.admin' => PreventUserAdminAccess::class,
        ]);

        // Redirect guests based on the guard
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin/*')) {
                return route('admin.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
