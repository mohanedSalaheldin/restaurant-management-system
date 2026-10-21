<?php

// Scenario 2: Menu hierarchy & delete constraints

use App\Enum\UserRole;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Section;
use App\Models\Subcategory;
use App\Services\OrderService;

beforeEach(function () {
    $this->menu = seedDrinksMenu();
    $this->admin = userWithRole(UserRole::ADMIN);
});

test('2.1 section with categories cannot be deleted and the error is shown', function () {
    $this->actingAs($this->admin)
        ->followingRedirects()
        ->delete(route('admin.sections.destroy', $this->menu['section']))
        ->assertOk()
        ->assertSee('Cannot delete section containing active categories.');

    expect(Section::find($this->menu['section']->id))->not->toBeNull();
});

test('2.2 category with subcategories cannot be deleted', function () {
    $this->actingAs($this->admin)
        ->followingRedirects()
        ->delete(route('admin.categories.destroy', $this->menu['category']))
        ->assertSee('Cannot delete category containing subcategories or items.');

    expect(Category::find($this->menu['category']->id))->not->toBeNull();
});

test('2.3 subcategory with items cannot be deleted', function () {
    $this->actingAs($this->admin)
        ->followingRedirects()
        ->delete(route('admin.subcategories.destroy', $this->menu['subcategory']))
        ->assertSee('Cannot delete subcategory containing active menu items.');

    expect(Subcategory::find($this->menu['subcategory']->id))->not->toBeNull();
});

test('2.3 empty subcategory can be deleted', function () {
    $empty = Subcategory::create(['category_id' => $this->menu['category']->id, 'name' => 'Tea', 'status' => true]);

    $this->actingAs($this->admin)
        ->delete(route('admin.subcategories.destroy', $empty))
        ->assertSessionHas('success');

    expect(Subcategory::find($empty->id))->toBeNull();
});

test('menu item that was ordered cannot be deleted (no 500 error)', function () {
    $table = App\Models\Table::create(['table_number' => 'T01', 'type' => 'public', 'min_capacity' => 1, 'max_capacity' => 4, 'status' => 'available']);
    app(OrderService::class)->createOrder(
        ['order_type' => 'dine_in', 'table_id' => $table->id, 'status' => 'sent_to_kitchen'],
        [['menu_item_id' => $this->menu['item']->id, 'quantity' => 1]],
        $this->admin->id
    );

    $this->actingAs($this->admin)
        ->delete(route('admin.menu-items.destroy', $this->menu['item']))
        ->assertRedirect(route('admin.menu-items.index'))
        ->assertSessionHas('error');

    expect(MenuItem::find($this->menu['item']->id))->not->toBeNull();
});

test('2.4 cascading dropdown endpoints return only children of the selected parent', function () {
    $other = Section::create(['name' => 'Food Menu', 'status' => true]);
    Category::create(['section_id' => $other->id, 'name' => 'Burgers', 'status' => true]);

    $this->actingAs($this->admin)
        ->getJson(route('admin.sections.categories.data', $this->menu['section']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.name', 'Hot Beverages');

    $this->actingAs($this->admin)
        ->getJson(route('admin.categories.subcategories.data', $this->menu['category']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.name', 'Coffee');
});

test('2.4 add item page renders', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.menu-items.create'))
        ->assertOk()
        ->assertSee('Drinks Menu');
});

test('2.5 menu display shows sections, items and special tags', function () {
    $this->actingAs(userWithRole(UserRole::WAITER))
        ->get('/menu-view')
        ->assertOk()
        ->assertSee('Drinks Menu')
        ->assertSee('Hot Beverages')
        ->assertSee('Espresso')
        ->assertSee('Vegan')
        ->assertSee('Spicy');
});
