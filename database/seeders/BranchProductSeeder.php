<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting to associate branches with products, meals, and boxes...');

        // Get all active branches
        $branches = Branch::where('active', true)->get();
        
        if ($branches->isEmpty()) {
            $this->command->warn('No active branches found. Please create branches first.');
            return;
        }

        // Get all active products (including meals, boxes, and regular products)
        $products = Product::where('active', true)->get();
        
        if ($products->isEmpty()) {
            $this->command->warn('No active products found. Please create products first.');
            return;
        }

        $this->command->info("Found {$branches->count()} branches and {$products->count()} products.");

        // Count products by type for reporting
        $mealsCount = $products->where('product_type', 'meal')->count();
        $boxesCount = $products->where('is_box', 1)->count() + $products->where('product_type', 'box')->count();
        $regularProductsCount = $products->where('product_type', 'product')->count();
        
        $this->command->info("Products breakdown:");
        $this->command->info("  - Meals: {$mealsCount}");
        $this->command->info("  - Boxes: {$boxesCount}");
        $this->command->info("  - Regular Products: {$regularProductsCount}");

        // Get existing associations to avoid duplicates
        $existingAssociations = DB::table('product_branches')
            ->select('branch_id', 'product_id')
            ->get()
            ->map(function ($item) {
                return $item->branch_id . '-' . $item->product_id;
            })
            ->toArray();

        $associationsToInsert = [];
        $totalAssociations = 0;
        $skippedAssociations = 0;
        $now = now();

        // Use transaction for better performance
        DB::beginTransaction();

        try {
            foreach ($branches as $branch) {
                $this->command->info("Processing branch: {$branch->name} (ID: {$branch->id})");

                foreach ($products as $product) {
                    $key = $branch->id . '-' . $product->id;
                    
                    // Check if association already exists
                    if (!in_array($key, $existingAssociations)) {
                        $associationsToInsert[] = [
                            'product_id' => $product->id,
                            'branch_id' => $branch->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $totalAssociations++;
                        
                        // Batch insert every 500 records for better performance
                        if (count($associationsToInsert) >= 500) {
                            DB::table('product_branches')->insert($associationsToInsert);
                            $associationsToInsert = [];
                        }
                    } else {
                        $skippedAssociations++;
                    }
                }
            }

            // Insert remaining associations
            if (!empty($associationsToInsert)) {
                DB::table('product_branches')->insert($associationsToInsert);
            }

            DB::commit();
            
            $this->command->info("\n✅ Seeding completed successfully!");
            $this->command->info("Total new associations created: {$totalAssociations}");
            $this->command->info("Skipped (already exists): {$skippedAssociations}");
            $this->command->info("Total branches processed: {$branches->count()}");
            $this->command->info("Total products processed: {$products->count()}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Error occurred: " . $e->getMessage());
            throw $e;
        }
    }
}

