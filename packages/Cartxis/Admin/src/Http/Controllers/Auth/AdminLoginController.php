<?php

namespace Cartxis\Admin\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Cartxis\Core\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class AdminLoginController extends Controller
{
    public function __construct(private SettingService $settingService) {}

    /**
     * Display the admin login view.
     */
    public function create(): Response
    {
        $storedLogo = $this->settingService->get('admin_logo');
        $adminLogo = $storedLogo
            ? Storage::disk('public')->url($storedLogo)
            : '/logos/cartxis.png';

        return Inertia::render('Admin/Auth/Login', [
            'adminLogo' => $adminLogo,
        ]);
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

            $roleIsAdmin = (string) ($user->role ?? '') === 'admin';
            $permissionsIsAdmin = method_exists($user, 'isAdmin') ? (bool) $user->isAdmin() : false;
            $isAdmin = $roleIsAdmin || $permissionsIsAdmin;

            // Check if user has admin role
            if (!$isAdmin) {
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
