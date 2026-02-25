<?php

namespace Cartxis\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAdminFrontendAccess
{
    /**
     * Prevent admin users (as defined by roles/permissions) from using the storefront session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guard('web')->check()) {
            $user = auth()->guard('web')->user();

            $roleIsAdmin = false;
            $permissionsIsAdmin = false;

            if (is_object($user)) {
                if (property_exists($user, 'role') || isset($user->role)) {
                    $roleIsAdmin = (string) ($user->role ?? '') === 'admin';
                }

                if (method_exists($user, 'isAdmin')) {
                    $permissionsIsAdmin = (bool) $user->isAdmin();
                }
            }

            $isAdmin = $roleIsAdmin || $permissionsIsAdmin;

            if ($isAdmin) {
                auth()->guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')
                    ->with('error', 'Admin users cannot login to the storefront. Please use the admin panel.');
            }
        }

        return $next($request);
    }
}
