<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Section;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. تجهيز صورة تجريبية في التخزين العام
        $sampleImagePath = 'menu_items/sample.jpg';
        if (!Storage::disk('public')->exists($sampleImagePath)) {
            // صورة شفافة قياسية 1x1 بكسل لغرض الاختبار
            $placeholderData = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
            Storage::disk('public')->put($sampleImagePath, $placeholderData);
        }

        // ================= 1. Drinks Menu Section =================
        $drinksSection = Section::updateOrCreate(
            ['name' => 'Drinks Menu'],
            ['description' => 'Refreshing hot and cold beverages', 'display_order' => 1, 'status' => true]
        );

        // 1.1 Hot Beverages
        $hotBev = Category::updateOrCreate(
            ['name' => 'Hot Beverages', 'section_id' => $drinksSection->id],
            ['description' => 'Steaming fresh brewed drinks', 'display_order' => 1, 'status' => true]
        );

        $coffeeSub = Subcategory::updateOrCreate(
            ['name' => 'Coffee', 'category_id' => $hotBev->id],
            ['description' => 'Freshly ground artisan coffee', 'display_order' => 1, 'status' => true]
        );

        $teaSub = Subcategory::updateOrCreate(
            ['name' => 'Tea', 'category_id' => $hotBev->id],
            ['description' => 'Traditional and herbal teas', 'display_order' => 2, 'status' => true]
        );

        // أصناف القهوة
        MenuItem::updateOrCreate(
            ['name' => 'Espresso', 'subcategory_id' => $coffeeSub->id],
            [
                'section_id' => $drinksSection->id,
                'category_id' => $hotBev->id,
                'price' => 3.50,
                'description' => 'Rich single shot Italian espresso',
                'preparation_time' => 5,
                'availability' => 'available',
                'image' => $sampleImagePath,
                'special_tags' => ['Popular']
            ]
        );

        MenuItem::updateOrCreate(
            ['name' => 'Cappuccino', 'subcategory_id' => $coffeeSub->id],
            [
                'section_id' => $drinksSection->id,
                'category_id' => $hotBev->id,
                'price' => 4.75,
                'description' => 'Espresso with steamed milk foam and cocoa powder',
                'preparation_time' => 7,
                'availability' => 'available',
                'image' => $sampleImagePath,
                'special_tags' => ["Chef's Special"]
            ]
        );

        // أصناف الشاي
        MenuItem::updateOrCreate(
            ['name' => 'Earl Grey Tea', 'subcategory_id' => $teaSub->id],
            [
                'section_id' => $drinksSection->id,
                'category_id' => $hotBev->id,
                'price' => 3.00,
                'description' => 'Fragrant black tea with oil of bergamot',
                'preparation_time' => 4,
                'availability' => 'available',
                'image' => $sampleImagePath,
                'special_tags' => ['Vegan']
            ]
        );

        // 1.2 Cold Beverages
        $coldBev = Category::updateOrCreate(
            ['name' => 'Cold Beverages', 'section_id' => $drinksSection->id],
            ['description' => 'Chilled and ice blended drinks', 'display_order' => 2, 'status' => true]
        );

        $juicesSub = Subcategory::updateOrCreate(
            ['name' => 'Fresh Juices', 'category_id' => $coldBev->id],
            ['description' => '100% natural cold pressed juices', 'display_order' => 1, 'status' => true]
        );

        MenuItem::updateOrCreate(
            ['name' => 'Orange Juice', 'subcategory_id' => $juicesSub->id],
            [
                'section_id' => $drinksSection->id,
                'category_id' => $coldBev->id,
                'price' => 5.00,
                'description' => 'Freshly squeezed sweet Valencia oranges',
                'preparation_time' => 5,
                'availability' => 'available',
                'image' => $sampleImagePath,
                'special_tags' => ['Vegan', 'Vegetarian']
            ]
        );

        // ================= 2. Main Menu Section =================
        $mainSection = Section::updateOrCreate(
            ['name' => 'Main Menu'],
            ['description' => 'Delicious entrees and chef specialties', 'display_order' => 2, 'status' => true]
        );

        $pastaCat = Category::updateOrCreate(
            ['name' => 'Pastas', 'section_id' => $mainSection->id],
            ['description' => 'Handmade Italian pastas', 'display_order' => 1, 'status' => true]
        );

        $spaghettiSub = Subcategory::updateOrCreate(
            ['name' => 'Spaghetti', 'category_id' => $pastaCat->id],
            ['description' => 'Classic long pasta dishes', 'display_order' => 1, 'status' => true]
        );

        MenuItem::updateOrCreate(
            ['name' => 'Spaghetti Arrabbiata', 'subcategory_id' => $spaghettiSub->id],
            [
                'section_id' => $mainSection->id,
                'category_id' => $pastaCat->id,
                'price' => 14.50,
                'description' => 'Spicy tomato sauce with garlic and fresh red chili peppers',
                'preparation_time' => 20,
                'availability' => 'available',
                'image' => $sampleImagePath,
                'special_tags' => ['Spicy', 'Vegetarian']
            ]
        );

        MenuItem::updateOrCreate(
            ['name' => 'Truffle Mushroom Pasta', 'subcategory_id' => $spaghettiSub->id],
            [
                'section_id' => $mainSection->id,
                'category_id' => $pastaCat->id,
                'price' => 18.00,
                'description' => 'Creamy black truffle sauce with wild forest mushrooms',
                'preparation_time' => 22,
                'availability' => 'out_of_stock', // تجربة عنصر غير متاح
                'image' => $sampleImagePath,
                'special_tags' => ["Chef's Special"]
            ]
        );
    }
}
