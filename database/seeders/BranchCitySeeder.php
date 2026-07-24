<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder assigns cities to branches.
     * Set $assignAllCitiesToAllBranches = true to assign all cities to all branches.
     * Set $assignAllCitiesToAllBranches = false to only assign branch's location city.
     */
    public function run(): void
    {
        // Set this to true if you want all cities assigned to all branches
        // Set to false to only assign branch's location_id city
        $assignAllCitiesToAllBranches = false;

        $this->command->info('Starting to assign cities to branches...');
        if ($assignAllCitiesToAllBranches) {
            $this->command->info('Mode: Assigning ALL cities to ALL branches');
        } else {
            $this->command->info('Mode: Assigning only branch location cities');
        }

        // Get all active branches
        $branches = Branch::where('active', true)->get();
        
        if ($branches->isEmpty()) {
            $this->command->warn('No active branches found. Please create branches first.');
            return;
        }

        // Get all active cities
        $cities = Location::where('type', 'city')->where('active', true)->get();
        
        if ($cities->isEmpty()) {
            $this->command->warn('No active cities found. Please create cities first.');
            return;
        }

        $this->command->info("Found {$branches->count()} branches and {$cities->count()} cities.");

        // Get existing associations to avoid duplicates
        $existingAssociations = DB::table('branch_cities')
            ->select('branch_id', 'city_id')
            ->get()
            ->map(function ($item) {
                return $item->branch_id . '-' . $item->city_id;
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

                if ($assignAllCitiesToAllBranches) {
                    // Assign all cities to all branches
                    foreach ($cities as $city) {
                        $key = $branch->id . '-' . $city->id;
                        
                        if (!in_array($key, $existingAssociations)) {
                            $associationsToInsert[] = [
                                'branch_id' => $branch->id,
                                'city_id' => $city->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                            $totalAssociations++;
                            
                            // Batch insert every 500 records for better performance
                            if (count($associationsToInsert) >= 500) {
                                DB::table('branch_cities')->insert($associationsToInsert);
                                $associationsToInsert = [];
                            }
                        } else {
                            $skippedAssociations++;
                        }
                    }
                } else {
                    // Only assign branch's location city (if it's a city)
                    if ($branch->location_id) {
                        $location = Location::find($branch->location_id);
                        if ($location && $location->type === 'city') {
                            $key = $branch->id . '-' . $location->id;
                            
                            if (!in_array($key, $existingAssociations)) {
                                $associationsToInsert[] = [
                                    'branch_id' => $branch->id,
                                    'city_id' => $location->id,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                                $totalAssociations++;
                                $this->command->info("  - Assigned city: {$location->getTranslation('name', app()->getLocale())} (from location_id)");
                            } else {
                                $skippedAssociations++;
                            }
                        }
                    }
                }
            }

            // Insert remaining associations
            if (!empty($associationsToInsert)) {
                DB::table('branch_cities')->insert($associationsToInsert);
            }

            DB::commit();
            
            $this->command->info("\n✅ Seeding completed successfully!");
            $this->command->info("Total new city assignments created: {$totalAssociations}");
            $this->command->info("Skipped (already exists): {$skippedAssociations}");
            $this->command->info("Total branches processed: {$branches->count()}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Error occurred: " . $e->getMessage());
            throw $e;
        }
    }
}

