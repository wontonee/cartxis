<?php

namespace Vortex\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdminAuthenticated
{
    /**
     * If an admin session already exists, keep them inside the admin panel.
     *
     * This replaces Laravel's `guest:admin` behavior which redirects to the
     * global HOME (often `/dashboard`) and can bounce admins to storefront login.
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

                return $next($request);
            }

            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
