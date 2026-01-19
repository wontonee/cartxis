<?php

declare(strict_types=1);

namespace Cartxis\Setup\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect away from setup wizard if setup is already complete
 */
class RedirectIfSetupComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isSetupComplete()) {
            return redirect('/admin');
        }

        return $next($request);
    }

    private function isSetupComplete(): bool
    {
        try {
            // Check if settings table exists
            if (!DB::getSchemaBuilder()->hasTable('settings')) {
                return false;
            }

            // Check if setup_completed setting exists and is true
            $setting = DB::table('settings')
                ->where('key', 'setup_completed')
                ->first();

            return $setting && $setting->value === '1';
        } catch (\Exception $e) {
            // During installation, tables might not exist
            return false;
        }
    }
}
