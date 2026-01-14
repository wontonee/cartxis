<?php

namespace Vortex\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventUserAdminAccess
{
    /**
     * Prevent non-admin users (authenticated on the admin guard) from accessing the admin panel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guard('admin')->check()) {
            $admin = auth()->guard('admin')->user();

            $roleIsAdmin = false;
            $permissionsIsAdmin = false;

            if (is_object($admin)) {
                if (property_exists($admin, 'role') || isset($admin->role)) {
                    $roleIsAdmin = (string) ($admin->role ?? '') === 'admin';
                }

                if (method_exists($admin, 'isAdmin')) {
                    $permissionsIsAdmin = (bool) $admin->isAdmin();
                }
            }

            $isAdmin = $roleIsAdmin || $permissionsIsAdmin;

            if (!$isAdmin) {
                auth()->guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'You do not have permission to access the admin panel.');
            }
        }

        return $next($request);
    }
}
