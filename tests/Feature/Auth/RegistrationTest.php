<?php

use App\Models\User;

test('public registration screen is disabled', function () {
    $this->get('/register')->assertNotFound();
});

test('guests cannot register through a public endpoint', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
});
