<?php

namespace Vortex\Admin\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class AdminLoginController extends Controller
{
    /**
     * Display the admin login view.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Auth/Login');
    }

    /**
     * Handle an incoming admin authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Attempt to authenticate
        if (Auth::guard('admin')->attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            $user = Auth::guard('admin')->user();

            // Check if user has admin role
            if ($user->role !== 'admin') {
                Auth::guard('admin')->logout();
                throw ValidationException::withMessages([
                    'email' => __('You do not have admin access.'),
                ]);
            }

            // Check if user is active
            if (!$user->is_active) {
                Auth::guard('admin')->logout();
                throw ValidationException::withMessages([
                    'email' => __('Your account has been deactivated.'),
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(config('admin.path', 'admin') . '/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    /**
     * Destroy an authenticated admin session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
