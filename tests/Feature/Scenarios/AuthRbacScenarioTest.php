<?php

// Scenario 1: Auth & RBAC

use App\Enum\UserRole;
use App\Models\User;

test('1.1 five wrong passwords trigger the login throttle', function () {
    $user = userWithRole(UserRole::WAITER, ['email' => 'waiter@restaurant.com']);

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
    }

    $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Too many login attempts');
    $this->assertGuest();
});

test('1.2 each role is redirected to its own dashboard after login', function (UserRole $role, string $path) {
    $user = userWithRole($role);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect($path);

    $this->assertAuthenticatedAs($user);
    $this->get($path)->assertOk();
})->with([
    'admin' => [UserRole::ADMIN, '/admin/dashboard'],
    'waiter' => [UserRole::WAITER, '/waiter/dashboard'],
    'cashier' => [UserRole::CASHIER, '/cashier/dashboard'],
    'kitchen' => [UserRole::KITCHEN_STAFF, '/kitchen/kds'],
]);

test('1.2 login works when the "Remember me" checkbox is ticked', function () {
    $user = userWithRole(UserRole::WAITER);

    // Browsers send remember=on when the box is ticked
    $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => 'on'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/waiter/dashboard');

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->remember_token)->not->toBeNull();
});

test('1.3 waiter gets 403 on admin routes', function (string $uri) {
    $this->actingAs(userWithRole(UserRole::WAITER))
        ->get($uri)
        ->assertForbidden();
})->with(['/admin/sections', '/admin/dashboard', '/admin/users', '/admin/tables', '/admin/offers']);

test('1.3 kitchen staff cannot access order management', function () {
    $this->actingAs(userWithRole(UserRole::KITCHEN_STAFF))
        ->get('/orders/create')
        ->assertForbidden();
});

test('1.3 guests are redirected to login from protected pages', function () {
    $this->get('/admin/sections')->assertRedirect('/login');
    $this->get('/kitchen/kds')->assertRedirect('/login');
});

test('1.4 admin can create a staff member with a hashed password', function () {
    $this->actingAs(userWithRole(UserRole::ADMIN))
        ->post('/admin/users', [
            'name' => 'New Cashier',
            'email' => 'new.cashier@restaurant.com',
            'role' => UserRole::CASHIER->value,
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.users.index'));

    $staff = User::where('email', 'new.cashier@restaurant.com')->firstOrFail();
    expect($staff->role)->toBe(UserRole::CASHIER)
        ->and(Hash::check('secret-pass-1', $staff->password))->toBeTrue();

    // Proves the password is not double-hashed
    auth()->logout();
    $this->post('/login', ['email' => $staff->email, 'password' => 'secret-pass-1'])
        ->assertRedirect('/cashier/dashboard');

    $this->get('/admin/users')->assertForbidden();
});

test('1.4 staff list shows the new member role and there is no public register link', function () {
    userWithRole(UserRole::KITCHEN_STAFF, ['name' => 'Chef Mario']);

    $this->actingAs(userWithRole(UserRole::ADMIN))
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('Chef Mario');

    auth()->logout();
    $this->get('/login')->assertOk()->assertDontSee('/register');
});

test('1.4 non-admin cannot create staff', function () {
    $this->actingAs(userWithRole(UserRole::WAITER))
        ->post('/admin/users', [
            'name' => 'Hacker',
            'email' => 'hacker@restaurant.com',
            'role' => UserRole::ADMIN->value,
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
        ])
        ->assertForbidden();

    expect(User::where('email', 'hacker@restaurant.com')->exists())->toBeFalse();
});

test('logout ends the session', function () {
    $this->actingAs(userWithRole(UserRole::WAITER))
        ->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});
