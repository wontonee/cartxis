<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

test('admin login screen can be rendered', function () {
    $response = $this->get(route('admin.login'));

    $response->assertStatus(200);
});

test('admins can authenticate using the admin login screen', function () {
    $admin = User::factory()->withoutTwoFactor()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $response = $this->post(route('admin.login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated('admin');
    $response->assertRedirect('/admin/dashboard');
});

test('admins can not authenticate with invalid password', function () {
    $admin = User::factory()->withoutTwoFactor()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->post(route('admin.login.store'), [
        'email' => $admin->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest('admin');
});

test('admin login is rate limited with validation errors', function () {
    $admin = User::factory()->withoutTwoFactor()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    RateLimiter::increment(
        strtolower($admin->email).'|127.0.0.1',
        amount: 5
    );

    $response = $this->post(route('admin.login.store'), [
        'email' => $admin->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $response->assertStatus(302);

    $errors = session('errors');

    $this->assertStringContainsString('Too many login attempts', $errors->first('email'));
});

test('non-admin users cannot authenticate via admin login', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'role' => 'customer',
        'is_active' => true,
    ]);

    $this->post(route('admin.login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest('admin');
});
