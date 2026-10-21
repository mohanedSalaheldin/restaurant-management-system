<?php

// Scenario 4: Offers engine

use App\Enum\UserRole;
use App\Models\Offer;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->menu = seedDrinksMenu();
    $this->admin = userWithRole(UserRole::ADMIN);
});

function offerPayload(int $itemId, array $overrides = []): array
{
    return [
        'name' => 'Mega Coffee Deal',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'items' => [$itemId],
        'status' => '1',
        'display_on_menu' => '1',
        ...$overrides,
    ];
}

test('4.1 admin creates a 20% offer linked to Espresso', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.offers.store'), offerPayload($this->menu['item']->id))
        ->assertSessionHasNoErrors();

    $offer = Offer::where('name', 'Mega Coffee Deal')->firstOrFail();
    expect($offer->items->pluck('id')->all())->toBe([$this->menu['item']->id])
        ->and($offer->isValidNow())->toBeTrue();
});

test('4.2 menu shows OFFER badge, struck original price and discounted price', function () {
    $this->actingAs($this->admin)->post(route('admin.offers.store'), offerPayload($this->menu['item']->id));

    expect($this->menu['item']->fresh()->final_price)->toBe(2.80);

    $this->actingAs($this->admin)
        ->get('/menu-view')
        ->assertOk()
        ->assertSee('Mega Coffee Deal')
        ->assertSee('3.50')
        ->assertSee('2.80');
});

test('4.3 expired offer disappears and original price is restored', function () {
    $this->actingAs($this->admin)->post(route('admin.offers.store'), offerPayload($this->menu['item']->id));
    $offer = Offer::firstOrFail();

    // end_date must be >= start_date, so the start date moves back too
    $this->actingAs($this->admin)
        ->put(route('admin.offers.update', $offer), offerPayload($this->menu['item']->id, [
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
        ]))
        ->assertSessionHasNoErrors();

    expect($offer->fresh()->isValidNow())->toBeFalse()
        ->and($this->menu['item']->fresh()->final_price)->toBe(3.50);

    $this->actingAs($this->admin)
        ->get('/menu-view')
        ->assertDontSee('Mega Coffee Deal');
});

test('offer respects applicable days and time window', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-21 14:00:00')); // Wednesday

    $offer = Offer::create([
        'name' => 'Lunch', 'discount_type' => 'fixed', 'discount_value' => 1,
        'start_date' => '2026-10-01', 'end_date' => '2026-10-31',
        'start_time' => '12:00', 'end_time' => '15:00',
        'applicable_days' => ['Wed'], 'status' => true,
    ]);
    expect($offer->isValidNow())->toBeTrue();

    Carbon::setTestNow(Carbon::parse('2026-10-21 16:00:00'));
    expect($offer->isValidNow())->toBeFalse();

    Carbon::setTestNow(Carbon::parse('2026-10-22 14:00:00')); // Thursday
    expect($offer->isValidNow())->toBeFalse();

    Carbon::setTestNow();
});

test('fixed discount never makes the price negative', function () {
    $offer = new Offer(['discount_type' => 'fixed', 'discount_value' => 10]);

    expect($offer->calculateDiscountedPrice(3.50))->toBe(0.0);
});

test('percentage discount above 100 is rejected', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.offers.store'), offerPayload($this->menu['item']->id, ['discount_value' => 150]))
        ->assertSessionHasErrors('discount_value');
});
