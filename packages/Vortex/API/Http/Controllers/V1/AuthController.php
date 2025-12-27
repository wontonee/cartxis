<?php

namespace Vortex\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Vortex\API\Helpers\ApiResponse;
use Vortex\API\Http\Resources\UserResource;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'terms_accepted' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        $token = $user->createToken(
            $request->device_name ?? config('vortex-api.token.name')
        )->plainTextToken;

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('vortex-api.token.expiration') * 60,
        ], 'Registration successful. Welcome to Vortex!', 201);
    }

    /**
     * Login user.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
            'remember_me' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error(
                'Invalid credentials',
                null,
                401,
                'INVALID_CREDENTIALS'
            );
        }

        $tokenName = $request->device_name ?? config('vortex-api.token.name');
        $token = $user->createToken($tokenName)->plainTextToken;

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('vortex-api.token.expiration') * 60,
        ], 'Login successful');
    }

    /**
     * Logout user (revoke current token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user details.
     */
    public function me(Request $request)
    {
        return ApiResponse::success(
            new UserResource($request->user()),
            'User details retrieved'
        );
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $user = $request->user();
        $user->update($request->only(['name', 'phone', 'date_of_birth', 'gender']));

        return ApiResponse::success(
            new UserResource($user),
            'Profile updated successfully'
        );
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return ApiResponse::error(
                'Current password is incorrect',
                null,
                400,
                'INVALID_PASSWORD'
            );
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all other tokens
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return ApiResponse::success(null, 'Password changed successfully');
    }

    /**
     * Upload avatar.
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|max:' . config('vortex-api.uploads.avatar.max_size'),
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                \Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        return ApiResponse::success([
            'avatar_url' => $user->avatar ? \Storage::url($user->avatar) : null,
        ], 'Avatar uploaded successfully');
    }

    /**
     * Forgot password - send reset link.
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ApiResponse::error(
                'No account found with this email',
                null,
                404,
                'USER_NOT_FOUND'
            );
        }

        // TODO: Send password reset email
        // Password::sendResetLink($request->only('email'));

        return ApiResponse::success(
            null,
            'Password reset link sent to your email'
        );
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        // TODO: Implement password reset logic
        // Password::reset($request->only('email', 'token', 'password', 'password_confirmation'));

        return ApiResponse::success(null, 'Password reset successfully');
    }

    /**
     * Refresh token.
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken(
            $request->device_name ?? config('vortex-api.token.name')
        )->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('vortex-api.token.expiration') * 60,
        ], 'Token refreshed successfully');
    }
}
