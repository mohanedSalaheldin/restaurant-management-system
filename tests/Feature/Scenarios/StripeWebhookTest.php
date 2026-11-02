<?php

// Webhook signatures are verified offline, so these tests never call Stripe.

use App\Enum\UserRole;
use App\Models\Bill;
use App\Models\Table;
use App\Services\BillingService;
use App\Services\OrderService;

beforeEach(function () {
    config(['services.stripe.webhook_secret' => 'whsec_test_secret']);

    $menu = seedDrinksMenu();
    $this->cashier = userWithRole(UserRole::CASHIER);
    $this->table = Table::create(['table_number' => 'T01', 'type' => 'public', 'min_capacity' => 1, 'max_capacity' => 4, 'status' => 'available']);

    $this->order = app(OrderService::class)->createOrder(
        ['order_type' => 'dine_in', 'table_id' => $this->table->id, 'status' => 'sent_to_kitchen'],
        [['menu_item_id' => $menu['item']->id, 'quantity' => 2]],
        $this->cashier->id
    );
    $this->order->update(['status' => 'served']);
    $this->bill = app(BillingService::class)->generateBillForOrder($this->order->fresh(), $this->cashier->id);
});

function stripeEvent(Bill $bill, array $overrides = [], string $type = 'checkout.session.completed'): string
{
    return json_encode([
        'id' => 'evt_test',
        'object' => 'event',
        'type' => $type,
        'data' => ['object' => [
            'id' => 'cs_test_123',
            'object' => 'checkout.session',
            'payment_status' => 'paid',
            'payment_intent' => 'pi_test_123',
            'amount_total' => (int) round($bill->total_amount * 100),
            'metadata' => ['bill_id' => (string) $bill->id],
            ...$overrides,
        ]],
    ]);
}

function postWebhook($test, string $payload, string $secret = 'whsec_test_secret')
{
    $timestamp = time();
    $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

    return $test->call('POST', '/stripe/webhook', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
    ], $payload);
}

test('a signed webhook pays the bill, completes the order and frees the table', function () {
    postWebhook($this, stripeEvent($this->bill))->assertOk();

    $payment = $this->bill->payments()->firstOrFail();

    expect($payment->payment_method)->toBe('stripe')
        ->and($payment->reference_number)->toBe('pi_test_123')
        ->and((float) $payment->amount_paid)->toBe((float) $this->bill->total_amount)
        ->and($this->bill->fresh()->status)->toBe('paid')
        ->and($this->order->fresh()->status)->toBe('completed')
        ->and($this->table->fresh()->status)->toBe('available');
});

test('stripe payments appear in the admin report breakdown', function () {
    postWebhook($this, stripeEvent($this->bill))->assertOk();

    $split = $this->actingAs(userWithRole(UserRole::ADMIN))->get('/admin/reports')->assertOk()->viewData('paymentSplit');

    expect($split)->toHaveKey('stripe')
        ->and($split['stripe'])->toBe((float) $this->bill->total_amount);
});

test('a redelivered webhook does not record the payment twice', function () {
    postWebhook($this, stripeEvent($this->bill))->assertOk();
    postWebhook($this, stripeEvent($this->bill))->assertOk();

    expect($this->bill->payments()->count())->toBe(1);
});

test('a forged signature is rejected', function () {
    postWebhook($this, stripeEvent($this->bill), 'whsec_wrong')->assertStatus(400);

    expect($this->bill->payments()->count())->toBe(0)
        ->and($this->bill->fresh()->status)->toBe('unpaid');
});

test('every webhook is rejected while the secret is not configured', function () {
    config(['services.stripe.webhook_secret' => null]);

    // Signed with an empty secret, which is what a forger would try.
    postWebhook($this, stripeEvent($this->bill), '')->assertStatus(400);

    expect($this->bill->payments()->count())->toBe(0);
});

test('unpaid sessions, foreign sessions and other event types change nothing', function (array $overrides, string $type) {
    postWebhook($this, stripeEvent($this->bill, $overrides, $type))->assertOk();

    expect($this->bill->payments()->count())->toBe(0)
        ->and($this->bill->fresh()->status)->toBe('unpaid');
})->with([
    'unpaid' => [['payment_status' => 'unpaid'], 'checkout.session.completed'],
    'no bill id' => [['metadata' => []], 'checkout.session.completed'],
    'unknown bill' => [['metadata' => ['bill_id' => '999999']], 'checkout.session.completed'],
    'other event' => [[], 'payment_intent.created'],
]);

test('an underpaid session fails so that Stripe retries it', function () {
    $this->withoutExceptionHandling();

    expect(fn () => postWebhook($this, stripeEvent($this->bill, ['amount_total' => 100])))
        ->toThrow(Exception::class);

    expect($this->bill->fresh()->status)->toBe('unpaid');
});

test('stripe checkout routes are limited to admin and cashier', function () {
    $this->actingAs(userWithRole(UserRole::WAITER))->get(route('stripe.checkout', $this->bill))->assertForbidden();
    $this->actingAs(userWithRole(UserRole::WAITER))->get(route('stripe.success'))->assertForbidden();
});

test('the success page without a session id returns to billing with an error', function () {
    $this->actingAs($this->cashier)
        ->get(route('stripe.success'))
        ->assertRedirect(route('billing.index'))
        ->assertSessionHas('error', 'Session ID missing.');
});

test('the invoice page links to stripe and has no stripe option in the cash form', function () {
    $this->actingAs($this->cashier)
        ->get(route('billing.show', $this->bill))
        ->assertOk()
        ->assertSee(route('stripe.checkout', $this->bill), false)
        ->assertDontSee('value="stripe"', false);
});

test('the cash form cannot mark a bill as paid through stripe', function () {
    $this->actingAs($this->cashier)
        ->post(route('billing.pay', $this->bill), ['payment_method' => 'stripe', 'amount_tendered' => 100])
        ->assertSessionHasErrors('payment_method');

    expect($this->bill->fresh()->status)->toBe('unpaid');
});
