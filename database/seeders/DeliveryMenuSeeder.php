<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Product;
use App\Models\MenuProduct;
use Illuminate\Support\Str;

class DeliveryMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if delivery menu already exists
        $existingMenu = Menu::where('slug', 'delivery')->first();
        
        if ($existingMenu) {
            $this->command->warn('Delivery menu already exists. Skipping...');
            return;
        }

        // Create delivery menu
        $menu = Menu::create([
            'name' => [
                'en' => 'Delivery',
                'ar' => 'دليفري'
            ],
            'slug' => 'delivery',
            'is_active' => true,
        ]);

        $this->command->info('Delivery menu created successfully!');

        // Get all categories
        $categories = Category::where('active', 1)->get();
        $this->command->info("Found {$categories->count()} categories");

        // Get all active products
        $products = Product::where('active', 1)->get();
        $this->command->info("Found {$products->count()} products");

        // Counter for order
        $order = 1;
        $attachedCount = 0;

        // Group products by category and attach them to menu
        foreach ($categories as $category) {
            $categoryProducts = $products->where('category_id', $category->id);
            
            foreach ($categoryProducts as $product) {
                // Check if product already attached to this menu
                $existing = MenuProduct::where('menu_id', $menu->id)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$existing) {
                    MenuProduct::create([
                        'menu_id' => $menu->id,
                        'product_id' => $product->id,
                        'category_id' => $category->id,
                        'order' => $order++,
                        'show_price' => true,
                    ]);
                    $attachedCount++;
                }
            }
        }

        // Also attach products without category
        $productsWithoutCategory = $products->whereNull('category_id');
        foreach ($productsWithoutCategory as $product) {
            $existing = MenuProduct::where('menu_id', $menu->id)
                ->where('product_id', $product->id)
                ->first();

            if (!$existing) {
                MenuProduct::create([
                    'menu_id' => $menu->id,
                    'product_id' => $product->id,
                    'category_id' => null,
                    'order' => $order++,
                    'show_price' => true,
                ]);
                $attachedCount++;
            }
        }

        $this->command->info("Successfully attached {$attachedCount} products to delivery menu!");
        $this->command->info("Menu ID: {$menu->id}");
    }
}
