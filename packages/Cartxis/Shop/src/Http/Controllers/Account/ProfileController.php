<?php

namespace Cartxis\Shop\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Cartxis\Shop\Http\Controllers\Controller;
use Cartxis\Core\Services\ThemeViewResolver;

class ProfileController extends Controller
{
    protected ThemeViewResolver $themeResolver;

    public function __construct(ThemeViewResolver $themeResolver)
    {
        $this->themeResolver = $themeResolver;
    }

    /**
     * Display profile page.
     */
    public function show(Request $request)
    {
        return $this->edit($request);
    }

    /**
     * Show the profile edit form.
     */
    public function edit(Request $request)
    {
        return $this->themeResolver->inertia('Account/Profile/Edit', [
            'user' => [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'email_verified_at' => auth()->user()->email_verified_at,
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
        ]);

        $user = auth()->user();
        $emailChanged = $user->email !== $validated['email'];

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // If email changed, mark as unverified
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.' . 
            ($emailChanged ? ' Please verify your new email address.' : ''));
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Update email preferences.
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'newsletter_subscribed' => ['boolean'],
            'order_notifications' => ['boolean'],
            'promotional_emails' => ['boolean'],
        ]);

        // Store preferences in user meta or separate table
        // For now, we'll just return success
        // TODO: Implement user preferences storage

        return back()->with('success', 'Email preferences updated successfully.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = auth()->user();

        // Logout the user
        auth()->logout();

        // Delete the user account
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }
}
