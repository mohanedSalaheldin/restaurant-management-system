<?php

// Scenario 3: Tables & QR codes

use App\Enum\UserRole;
use App\Models\Table;

beforeEach(function () {
    $this->admin = userWithRole(UserRole::ADMIN);
});

test('3.1 max capacity smaller than min capacity is rejected', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.tables.store'), [
            'table_number' => 'T-98', 'type' => 'public',
            'min_capacity' => 4, 'max_capacity' => 2, 'status' => 'available',
        ])
        ->assertSessionHasErrors('max_capacity');

    expect(Table::where('table_number', 'T-98')->exists())->toBeFalse();
});

test('3.2 valid table is saved with a 32-char token and shown as available', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.tables.store'), [
            'table_number' => 'T-99', 'type' => 'public',
            'min_capacity' => 2, 'max_capacity' => 4, 'status' => 'available',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.tables.index'));

    $table = Table::where('table_number', 'T-99')->firstOrFail();
    expect($table->unique_token)->toHaveLength(32);

    $this->actingAs($this->admin)
        ->get(route('admin.tables.index'))
        ->assertOk()
        ->assertSee('T-99')
        ->assertSee('Available');
});

test('3.3 tables page renders the QR code SVG and the actions', function () {
    $table = Table::create(['table_number' => 'T-99', 'type' => 'public', 'min_capacity' => 2, 'max_capacity' => 4, 'status' => 'available']);

    $this->actingAs($this->admin)
        ->get(route('admin.tables.index'))
        ->assertOk()
        ->assertSee('<svg', false)
        ->assertSee('Print')
        ->assertSee('Regenerate')
        ->assertSee($table->qr_url, false);
});

test('3.3 regenerating the QR invalidates the old link', function () {
    $table = Table::create(['table_number' => 'T-99', 'type' => 'public', 'min_capacity' => 2, 'max_capacity' => 4, 'status' => 'available']);
    $oldUrl = $table->qr_url;

    $this->actingAs($this->admin)
        ->post(route('admin.tables.regenerate.qr', $table))
        ->assertSessionHas('success');

    auth()->logout();
    $this->get($oldUrl)->assertNotFound();
    $this->get($table->fresh()->qr_url)->assertOk();
});

test('3.5 guest opening the QR link sees the welcome page and menu', function () {
    seedDrinksMenu();
    $table = Table::create(['table_number' => 'T-99', 'type' => 'public', 'min_capacity' => 2, 'max_capacity' => 4, 'status' => 'available']);

    $this->get($table->qr_url)
        ->assertOk()
        ->assertSee('T-99')
        ->assertSee('Welcome')
        ->assertSee('Espresso');

    $this->assertGuest();
});

test('3.5 forged token returns 404', function () {
    Table::create(['table_number' => 'T-99', 'type' => 'public', 'min_capacity' => 2, 'max_capacity' => 4, 'status' => 'available']);

    $this->get('/table/T-99/'.str_repeat('x', 32))->assertNotFound();
});
