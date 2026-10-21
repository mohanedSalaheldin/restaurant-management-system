<?php

// Scenario 5: Orders lifecycle & KDS

use App\Enum\UserRole;
use App\Models\MenuItem;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Table;

beforeEach(function () {
    $this->menu = seedDrinksMenu();
    $this->waiter = userWithRole(UserRole::WAITER);
    $this->chef = userWithRole(UserRole::KITCHEN_STAFF);
    $this->table = Table::create(['table_number' => 'T01', 'type' => 'public', 'min_capacity' => 1, 'max_capacity' => 4, 'status' => 'available']);

    $this->latte = MenuItem::create([
        'section_id' => $this->menu['section']->id,
        'category_id' => $this->menu['category']->id,
        'subcategory_id' => $this->menu['subcategory']->id,
        'name' => 'Latte', 'price' => 5.00, 'image' => 'x.jpg', 'preparation_time' => 20,
    ]);

    $offer = Offer::create([
        'name' => 'Mega Coffee Deal', 'discount_type' => 'percentage', 'discount_value' => 20,
        'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => true,
    ]);
    $offer->items()->sync([$this->menu['item']->id]);
});

function placeOrder($test): Order
{
    $test->actingAs($test->waiter)->post(route('orders.store'), [
        'order_type' => 'dine_in',
        'table_id' => $test->table->id,
        'status' => 'sent_to_kitchen',
        'items' => [
            ['menu_item_id' => $test->menu['item']->id, 'quantity' => 2, 'special_instructions' => 'Extra hot, no sugar'],
            ['menu_item_id' => $test->latte->id, 'quantity' => 1],
        ],
    ])->assertSessionHasNoErrors();

    return Order::latest('id')->firstOrFail();
}

test('5.1 waiter order screen lists available tables', function () {
    $this->actingAs($this->waiter)
        ->get(route('orders.create'))
        ->assertOk()
        ->assertSee('T01')
        ->assertSee('Espresso');
});

test('5.2/5.3 order is created with frozen prices, discount and an order number', function () {
    $order = placeOrder($this);

    expect($order->order_number)->toBe('ORD-'.now()->format('Ymd').'-0001')
        ->and($order->status)->toBe('sent_to_kitchen')
        ->and((float) $order->subtotal)->toBe(12.00)        // 2×3.50 + 5.00
        ->and((float) $order->discount_amount)->toBe(1.40) // 20% of 7.00
        ->and((float) $order->total_amount)->toBe(10.60);

    $espressoLine = $order->items->firstWhere('menu_item_id', $this->menu['item']->id);
    expect((float) $espressoLine->unit_price)->toBe(3.50)
        ->and((float) $espressoLine->discounted_price)->toBe(2.80)
        ->and($espressoLine->special_instructions)->toBe('Extra hot, no sugar');

    // Later menu price changes must not affect the order
    $this->menu['item']->update(['price' => 9.99]);
    expect((float) $espressoLine->fresh()->unit_price)->toBe(3.50);

    expect(placeOrder($this)->order_number)->toEndWith('-0002');
});

test('takeaway order needs no table', function () {
    $this->actingAs($this->waiter)->post(route('orders.store'), [
        'order_type' => 'takeaway',
        'items' => [['menu_item_id' => $this->latte->id, 'quantity' => 1]],
    ])->assertSessionHasNoErrors();

    expect(Order::firstOrFail()->table_id)->toBeNull()
        ->and($this->table->fresh()->status)->toBe('available');
});

test('dine-in order without a table is rejected', function () {
    $this->actingAs($this->waiter)->post(route('orders.store'), [
        'order_type' => 'dine_in',
        'items' => [['menu_item_id' => $this->latte->id, 'quantity' => 1]],
    ])->assertSessionHasErrors('table_id');
});

test('5.4 table becomes occupied', function () {
    placeOrder($this);

    expect($this->table->fresh()->status)->toBe('occupied');
});

test('5.5 KDS shows the order with notes and timer', function () {
    $order = placeOrder($this);

    $this->actingAs($this->chef)
        ->get('/kitchen/kds')
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Extra hot, no sugar')
        ->assertSee('data-target="20"', false)
        ->assertSee('Start Cooking');

    $this->actingAs($this->chef)->getJson('/kitchen/orders-data')->assertOk()->assertJsonCount(1);
});

test('5.6/5.7 kitchen moves order to in_preparation then ready via AJAX', function () {
    $order = placeOrder($this);

    $this->actingAs($this->chef)
        ->patchJson("/kitchen/orders/{$order->id}/status", ['status' => 'in_preparation'])
        ->assertOk()->assertJson(['success' => true]);
    expect($order->fresh()->status)->toBe('in_preparation');

    $this->actingAs($this->chef)->get('/kitchen/kds')->assertSee('Mark as Ready');

    $this->actingAs($this->chef)
        ->patchJson("/kitchen/orders/{$order->id}/status", ['status' => 'ready'])
        ->assertOk();
    expect($order->fresh()->status)->toBe('ready');

    $this->actingAs($this->chef)->getJson('/kitchen/orders-data')->assertJsonCount(0);
});

test('kitchen cannot skip states', function () {
    $order = placeOrder($this);

    $this->actingAs($this->chef)
        ->patchJson("/kitchen/orders/{$order->id}/status", ['status' => 'ready'])
        ->assertStatus(422);
    expect($order->fresh()->status)->toBe('sent_to_kitchen');
});

test('5.8 waiter serves then completes the order and the table is freed', function () {
    $order = placeOrder($this);
    $order->update(['status' => 'ready']);

    $this->actingAs($this->waiter)->get(route('orders.show', $order))->assertSee('Mark as Served');
    $this->actingAs($this->waiter)->patch(route('orders.update-status', $order), ['status' => 'served'])->assertSessionHas('success');

    $this->actingAs($this->waiter)->get(route('orders.show', $order))->assertSee('Mark as Completed');
    $this->actingAs($this->waiter)->patch(route('orders.update-status', $order), ['status' => 'completed'])->assertSessionHas('success');

    expect($order->fresh()->status)->toBe('completed')
        ->and($this->table->fresh()->status)->toBe('available');
});

test('table stays occupied while another order on it is active', function () {
    $first = placeOrder($this);
    placeOrder($this);

    $this->actingAs($this->waiter)->patch(route('orders.update-status', $first), ['status' => 'cancelled']);

    expect($this->table->fresh()->status)->toBe('occupied');
});
