<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;
use Vortex\Core\Services\ThemeViewResolver;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $themeResolver = app(ThemeViewResolver::class);
        
        Fortify::twoFactorChallengeView(fn () => Inertia::render($themeResolver->resolve('Auth/TwoFactorChallenge')));
        Fortify::confirmPasswordView(fn () => Inertia::render($themeResolver->resolve('Auth/ConfirmPassword')));

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
