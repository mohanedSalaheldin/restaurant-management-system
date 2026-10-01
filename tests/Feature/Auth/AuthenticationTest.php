<?php

use App\Enum\UserRole;
use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route($user->role->redirectRoute()));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/login');
});

test('admin can login and reach admin dashboard', function () {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard'));

    $dashboardResponse = $this->actingAs($user)->get(route('admin.dashboard'));
    $dashboardResponse->assertStatus(200);
});

test('guest accessing root is redirected to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

test('authenticated user accessing root is redirected to their dashboard', function () {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect(route('admin.dashboard'));
});

test('authenticated user accessing login page is redirected to their dashboard', function () {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect(route('admin.dashboard'));
});
