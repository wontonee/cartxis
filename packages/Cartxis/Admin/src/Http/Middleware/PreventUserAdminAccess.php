<?php

namespace Cartxis\Admin\Http\Middleware;

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

            $isActive = is_object($admin)
                && (property_exists($admin, 'is_active') || isset($admin->is_active))
                && (bool) $admin->is_active;

            if (!$isAdmin || !$isActive) {
                auth()->guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = !$isAdmin
                    ? 'You do not have permission to access the admin panel.'
                    : 'Your account has been deactivated.';

                return redirect()->route('admin.login')
                    ->with('error', $message);
            }
        }

        return $next($request);
    }
}
