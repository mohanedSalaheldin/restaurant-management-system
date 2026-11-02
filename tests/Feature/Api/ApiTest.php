<?php

use App\Enum\UserRole;
use App\Models\MenuItem;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Table;

beforeEach(function () {
    $this->menu = seedDrinksMenu();
    $this->waiter = userWithRole(UserRole::WAITER);
    $this->table = Table::create(['table_number' => 'T01', 'type' => 'public', 'min_capacity' => 1, 'max_capacity' => 4, 'status' => 'available']);
});

function bearer(App\Models\User $user): array
{
    return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
}

test('login returns a usable token and the user role', function () {
    $token = $this->postJson('/api/login', ['email' => $this->waiter->email, 'password' => 'password'])
        ->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('user.role', 'waiter')
        ->json('token');

    $this->getJson('/api/orders', ['Authorization' => "Bearer {$token}"])->assertOk();
});

test('login rejects wrong credentials', function () {
    $this->postJson('/api/login', ['email' => $this->waiter->email, 'password' => 'wrong'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('login is limited to 5 attempts per minute', function () {
    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/login', ['email' => $this->waiter->email, 'password' => 'wrong'])->assertStatus(422);
    }

    $this->postJson('/api/login', ['email' => $this->waiter->email, 'password' => 'wrong'])->assertStatus(429);
});

test('logout revokes the token', function () {
    $headers = bearer($this->waiter);

    $this->postJson('/api/logout', [], $headers)->assertOk();

    expect($this->waiter->tokens()->count())->toBe(0);
});

test('protected endpoints return 401 JSON without a token, even without an Accept header', function () {
    $this->getJson('/api/orders')->assertUnauthorized();
    $this->get('/api/orders')->assertUnauthorized()->assertJsonStructure(['message']);
});

test('kitchen staff cannot use the order endpoints', function () {
    $this->getJson('/api/orders', bearer(userWithRole(UserRole::KITCHEN_STAFF)))->assertForbidden();
});

test('full menu is public, hierarchical and includes offer prices', function () {
    $offer = Offer::create([
        'name' => 'Deal', 'discount_type' => 'percentage', 'discount_value' => 20,
        'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => true,
    ]);
    $offer->items()->sync([$this->menu['item']->id]);

    $this->getJson('/api/menu/full')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Drinks Menu')
        ->assertJsonPath('data.0.categories.0.subcategories.0.items.0.name', 'Espresso')
        ->assertJsonPath('data.0.categories.0.subcategories.0.items.0.final_price', 2.8)
        ->assertJsonPath('data.0.categories.0.subcategories.0.items.0.has_active_offer', true)
        ->assertJsonPath('data.0.categories.0.subcategories.0.items.0.offer_details.name', 'Deal');
});

test('menu items support search, category filter and pagination', function () {
    MenuItem::create([
        'section_id' => $this->menu['section']->id,
        'category_id' => $this->menu['category']->id,
        'subcategory_id' => $this->menu['subcategory']->id,
        'name' => 'Sold Out Latte', 'price' => 5, 'image' => 'x.jpg', 'availability' => 'out_of_stock',
    ]);

    $this->getJson('/api/menu/items?search=espr')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.final_price', 3.5)
        ->assertJsonPath('pagination.total', 1);

    $this->getJson('/api/menu/items?category_id='.$this->menu['category']->id)->assertJsonPath('pagination.total', 1);
    $this->getJson('/api/menu/items?category_id=999999')->assertJsonPath('pagination.total', 0);
    $this->getJson('/api/menu/items?search[]=x')->assertStatus(422);
});

test('waiter creates an order, lists it and opens it by the returned id', function () {
    $headers = bearer($this->waiter);

    $id = $this->postJson('/api/orders', [
        'order_type' => 'dine_in',
        'table_id' => $this->table->id,
        'status' => 'sent_to_kitchen',
        'items' => [['menu_item_id' => $this->menu['item']->id, 'quantity' => 2, 'special_instructions' => 'No sugar']],
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('data.table_number', 'T01')
        ->assertJsonPath('data.total_amount', 7)
        ->assertJsonPath('data.items.0.name', 'Espresso')
        ->json('data.id');

    expect($this->table->fresh()->status)->toBe('occupied')
        ->and(Order::find($id)->user_id)->toBe($this->waiter->id);

    $this->getJson('/api/orders?status=sent_to_kitchen', $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('pagination.total', 1);

    $this->getJson("/api/orders/{$id}", $headers)->assertOk()->assertJsonPath('data.id', $id);
    $this->getJson('/api/orders/999999', $headers)->assertNotFound();
});

test('invalid orders return 422 JSON', function () {
    $this->postJson('/api/orders', ['order_type' => 'dine_in', 'items' => []], bearer($this->waiter))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['table_id', 'items']);
});
