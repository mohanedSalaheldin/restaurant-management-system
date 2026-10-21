<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function userWithRole(App\Enum\UserRole $role, array $attributes = []): App\Models\User
{
    return App\Models\User::factory()->create(['role' => $role, ...$attributes]);
}

/**
 * Seeds a full menu hierarchy: Drinks Menu > Hot Beverages > Coffee > Espresso
 *
 * @return array{section: App\Models\Section, category: App\Models\Category, subcategory: App\Models\Subcategory, item: App\Models\MenuItem}
 */
function seedDrinksMenu(): array
{
    $section = App\Models\Section::create(['name' => 'Drinks Menu', 'display_order' => 1, 'status' => true]);
    $category = App\Models\Category::create(['section_id' => $section->id, 'name' => 'Hot Beverages', 'display_order' => 1, 'status' => true]);
    $subcategory = App\Models\Subcategory::create(['category_id' => $category->id, 'name' => 'Coffee', 'display_order' => 1, 'status' => true]);
    $item = App\Models\MenuItem::create([
        'section_id' => $section->id,
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'name' => 'Espresso',
        'price' => 3.50,
        'image' => 'menu_items/espresso.jpg',
        'preparation_time' => 10,
        'availability' => 'available',
        'special_tags' => ['Vegan', 'Spicy'],
    ]);

    return compact('section', 'category', 'subcategory', 'item');
}
