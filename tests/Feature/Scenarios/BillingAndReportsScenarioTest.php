<?php

// Billing lifecycle and the analytics built on top of it.

use App\Enum\UserRole;
use App\Models\Bill;
use App\Models\Order;
use App\Models\Table;
use App\Services\OrderService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->menu = seedDrinksMenu();
    $this->admin = userWithRole(UserRole::ADMIN);
    $this->cashier = userWithRole(UserRole::CASHIER);
    $this->table = Table::create(['table_number' => 'T01', 'type' => 'public', 'min_capacity' => 1, 'max_capacity' => 4, 'status' => 'available']);
});

function readyOrder($test, string $type = 'dine_in', int $qty = 2): Order
{
    $order = app(OrderService::class)->createOrder(
        ['order_type' => $type, 'table_id' => $type === 'dine_in' ? $test->table->id : null, 'status' => 'sent_to_kitchen'],
        [['menu_item_id' => $test->menu['item']->id, 'quantity' => $qty]],
        $test->admin->id
    );
    $order->update(['status' => 'ready']);

    return $order->fresh();
}

function billFor($test, Order $order): Bill
{
    $test->actingAs($test->cashier)->post(route('billing.generate', $order))->assertSessionHasNoErrors();

    return Bill::where('order_id', $order->id)->firstOrFail();
}

test('only admin and cashier can reach billing', function () {
    $this->actingAs($this->cashier)->get(route('billing.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('billing.index'))->assertOk();
    $this->actingAs(userWithRole(UserRole::WAITER))->get(route('billing.index'))->assertForbidden();
    $this->actingAs(userWithRole(UserRole::KITCHEN_STAFF))->get(route('billing.index'))->assertForbidden();
});

test('dine-in bill adds 14% tax and 10% service', function () {
    $bill = billFor($this, readyOrder($this));

    expect((float) $bill->subtotal)->toBe(7.00)
        ->and((float) $bill->tax_amount)->toBe(0.98)
        ->and((float) $bill->service_amount)->toBe(0.70)
        ->and((float) $bill->total_amount)->toBe(8.68)
        ->and($bill->invoice_number)->toBe('INV-'.now()->format('Ymd').'-0001');

    $this->actingAs($this->cashier)->get(route('billing.show', $bill))->assertOk()->assertSee('8.68');
});

test('takeaway bill has no service fee', function () {
    $bill = billFor($this, readyOrder($this, 'takeaway'));

    expect((float) $bill->service_amount)->toBe(0.00)
        ->and((float) $bill->total_amount)->toBe(7.98);
});

test('generating twice returns the same bill', function () {
    $order = readyOrder($this);
    billFor($this, $order);
    billFor($this, $order->fresh());

    expect(Bill::where('order_id', $order->id)->count())->toBe(1);
});

test('draft orders cannot be billed', function () {
    $order = readyOrder($this);
    $order->update(['status' => 'draft']);

    $this->actingAs($this->cashier)->post(route('billing.generate', $order))->assertSessionHas('error');

    expect(Bill::count())->toBe(0);
});

test('cash payment records change, closes the order and frees the table', function () {
    $order = readyOrder($this);
    $bill = billFor($this, $order);

    $this->actingAs($this->cashier)
        ->post(route('billing.pay', $bill), ['payment_method' => 'cash', 'amount_tendered' => 10])
        ->assertRedirect(route('billing.receipt', $bill));

    $payment = $bill->payments()->firstOrFail();
    expect((float) $payment->amount_paid)->toBe(8.68)
        ->and((float) $payment->change_returned)->toBe(1.32)
        ->and($bill->fresh()->status)->toBe('paid')
        ->and($order->fresh()->status)->toBe('completed')
        ->and($this->table->fresh()->status)->toBe('available');

    $this->actingAs($this->cashier)->get(route('billing.receipt', $bill))->assertOk()->assertSee($bill->invoice_number);
});

test('underpayment is refused with a readable message showing the balance', function () {
    $bill = billFor($this, readyOrder($this));

    $this->actingAs($this->cashier)
        ->post(route('billing.pay', $bill), ['payment_method' => 'cash', 'amount_tendered' => 1])
        ->assertSessionHas('error', fn ($message) => str_contains($message, '8.68') && ! str_contains($message, 'Undefined'));

    expect($bill->fresh()->status)->toBe('unpaid')
        ->and($bill->payments()->count())->toBe(0);
});

test('a paid bill cannot be paid twice', function () {
    $bill = billFor($this, readyOrder($this));
    $this->actingAs($this->cashier)->post(route('billing.pay', $bill), ['payment_method' => 'cash', 'amount_tendered' => 10]);

    $this->actingAs($this->cashier)
        ->post(route('billing.pay', $bill), ['payment_method' => 'cash', 'amount_tendered' => 10])
        ->assertSessionHas('error');

    expect($bill->payments()->count())->toBe(1);
});

test('reports are admin only', function () {
    $this->actingAs($this->admin)->get('/admin/reports')->assertOk();
    $this->actingAs($this->cashier)->get('/admin/reports')->assertForbidden();
    auth()->logout();
    $this->get('/admin/reports')->assertRedirect('/login');
});

test('reports render with no data', function () {
    $this->actingAs($this->admin)
        ->get('/admin/reports')
        ->assertOk()
        ->assertSee('No completed item sales recorded in this period.')
        ->assertSee('No payments recorded in this period.');
});

test('reports aggregate paid bills, payments and top items', function () {
    $paid = billFor($this, readyOrder($this));
    $this->actingAs($this->cashier)->post(route('billing.pay', $paid), ['payment_method' => 'cash', 'amount_tendered' => 10]);

    $paidCard = billFor($this, readyOrder($this, 'takeaway', 1));
    $this->actingAs($this->cashier)->post(route('billing.pay', $paidCard), ['payment_method' => 'card', 'amount_tendered' => (float) $paidCard->total_amount]);

    billFor($this, readyOrder($this, 'takeaway', 5)); // unpaid: must not count as revenue

    $response = $this->actingAs($this->admin)->get('/admin/reports')->assertOk()->assertSee('Espresso');

    $stats = $response->viewData('stats');
    expect($stats['bills_count'])->toBe(2)
        ->and($stats['orders_count'])->toBe(3)
        ->and($stats['total_revenue'])->toBe(12.67)  // 8.68 + 3.99
        ->and($stats['net_sales'])->toBe(10.50)
        ->and($stats['tax_collected'])->toBe(1.47)
        ->and($stats['service_fee'])->toBe(0.70)
        ->and($stats['avg_order_value'])->toBe(6.34);

    expect($response->viewData('paymentSplit'))->toBe(['card' => 3.99, 'cash' => 8.68]);

    $top = $response->viewData('topItems');
    expect($top)->toHaveCount(1)
        ->and((int) $top[0]['total_quantity'])->toBe(3); // the unpaid order is not completed
});

test('revenue trend has one point per day including empty days', function () {
    Carbon::setTestNow('2026-10-20 12:00:00');
    $bill = billFor($this, readyOrder($this));
    $this->actingAs($this->cashier)->post(route('billing.pay', $bill), ['payment_method' => 'cash', 'amount_tendered' => 10]);
    Carbon::setTestNow();

    $trend = $this->actingAs($this->admin)
        ->get('/admin/reports?start_date=2026-10-19&end_date=2026-10-21')
        ->assertOk()
        ->viewData('revenueTrend');

    expect($trend['labels'])->toBe(['Oct 19', 'Oct 20', 'Oct 21'])
        ->and($trend['values'])->toBe([0.0, 8.68, 0.0]);
});

test('invalid report date ranges are rejected', function () {
    $this->actingAs($this->admin)
        ->get('/admin/reports?start_date=2026-10-21&end_date=2026-10-19')
        ->assertSessionHasErrors('end_date');

    $this->actingAs($this->admin)
        ->get('/admin/reports?start_date=not-a-date')
        ->assertSessionHasErrors('start_date');
});

test('every role lands on a page with working navigation', function (UserRole $role, string $path, string $link) {
    $this->actingAs(userWithRole($role))
        ->get($path)
        ->assertOk()
        ->assertSee('RestoManage')
        ->assertSee($link, false);
})->with([
    'admin' => [UserRole::ADMIN, '/admin/dashboard', 'Analytics & Reports'],
    'waiter' => [UserRole::WAITER, '/waiter/dashboard', 'New Order'],
    'cashier' => [UserRole::CASHIER, '/cashier/dashboard', 'Billing & Payments'],
    'kitchen' => [UserRole::KITCHEN_STAFF, '/kitchen/kds', 'Kitchen Display'],
]);
