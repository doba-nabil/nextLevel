<?php

namespace App\Services;

use App\Models\Product;
use App\Traits\mediaUploader;
use App\Traits\slugGenerator;
use Illuminate\Support\Facades\DB;

class BoxService
{
    use mediaUploader, slugGenerator;

    public function getAll()
    {
        return Product::with('products')->get();
    }

    public function getById($id)
    {
        return Product::with('products')->findOrFail($id);
    }

    public function create(array $data, $image = null)
    {
        $data['slug'] = $this->generateSlug($data);
        $data['is_box'] = true;

        return DB::transaction(function () use ($data, $image) {
            $product = Product::create($data);
            $this->syncBoxTitles(
                $product,
                $data['box_titles'] ?? [],
                $data['product_addons'] ?? []
            );
            $this->syncPrices($product, $data['prices'] ?? [], true);
            // Pass group_blocks if exists, otherwise pass addons
            $addonsData = [];
            // Use array_key_exists to handle empty arrays
            if (array_key_exists('group_blocks', $data)) {
                $addonsData['group_blocks'] = $data['group_blocks'] ?? [];
            } elseif (isset($data['addons'])) {
                $addonsData = $data['addons'];
            }
            $this->syncAddons($product, $addonsData);
            $this->syncDefinitions($product, $data['definitions'] ?? [], true);
            $product->branches()->sync(array_map('intval', $data['branches'] ?? []));
            if ($image) {
                DB::afterCommit(function () use ($product, $image) {
                    $this->handleImage($product, $image, false, 'products');
                });
            }
            return $product;
        });
    }

    public function update(Product $product, array $data, $image = null): Product
    {
        return DB::transaction(function () use ($product, $data, $image) {
            $data['slug'] = $this->generateSlug($data);
            $product->update($data);
            $this->syncBoxTitles(
                $product,
                $data['box_titles'] ?? [],
                $data['product_addons'] ?? []
            );
            $this->syncPrices($product, $data['prices'] ?? [], true);
            // Pass group_blocks if exists, otherwise pass addons
            $addonsData = [];
            // Use array_key_exists to handle empty arrays
            if (array_key_exists('group_blocks', $data)) {
                $addonsData['group_blocks'] = $data['group_blocks'] ?? [];
            } elseif (isset($data['addons'])) {
                $addonsData = $data['addons'];
            }
            $this->syncAddons($product, $addonsData);
            $this->syncDefinitions($product, $data['definitions'] ?? [], true);
            $product->branches()->sync(array_map('intval', $data['branches'] ?? []));

            if ($image) {
                DB::afterCommit(function () use ($product, $image) {
                    $this->handleImage($product, $image, true, 'products');
                });
            }

            return $product;
        });
    }

    public function delete($id)
    {
        $box = Product::findOrFail($id);
        return $box->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */
    private function syncPrices(Product $product, array $prices, bool $replace = false): void
    {
        if ($replace) {
            $product->prices()->delete();
        }
        foreach ($prices as $currencyId => $priceData) {
            $priceBefore = (float) ($priceData['before'] ?? 0);
            $priceAfter = isset($priceData['after']) && $priceData['after'] !== '' ? (float) $priceData['after'] : null;
            $discountType = $priceData['discount_type'] ?? 'none';
            
            // Only set discount if after price is provided and is less than before price
            $hasDiscount = $priceAfter !== null && $priceAfter < $priceBefore && $priceAfter > 0;
            
            $product->prices()->create([
                'currency_id'         => $currencyId,
                'price'               => $priceBefore,
                'discount_price'      => $hasDiscount ? $priceAfter : $priceBefore,
                'discount_percentage' => $priceData['discount_percentage'] ?? null,
                'has_discount'        => $hasDiscount,
                'discount_type'       => $hasDiscount && $discountType !== 'none' ? $discountType : null,
            ]);
        }
    }

    private function syncAddons(Product $product, array $addons, ?int $boxId = null): void
    {
        // New group-based system
        // Use array_key_exists to handle empty arrays
        if (array_key_exists('group_blocks', $addons)) {
            $this->syncAddonsFromGroupBlocks($product, $addons['group_blocks'] ?? [], $boxId);
            return;
        }
        
        // Legacy type-based system
        if (isset($addons['type_blocks'])) {
            $this->syncAddonsFromTypeBlocks($product, $addons['type_blocks'], $boxId);
            return;
        }
        
        // Legacy system (fallback)
        // If addons array is empty, preserve all existing addons
        if (empty($addons)) {
            $query = \DB::table('addon_group_products')
                ->where('product_id', $product->id);
            
            if ($boxId) {
                $query->where('box_id', $boxId);
            } else {
                $query->whereNull('box_id');
            }
            
            $existingAddons = $query->get();
            // All existing addons are already in the database, no need to do anything
            return;
        }

        // First, remove all existing addons for this product/box
        if ($boxId) {
            \DB::table('addon_group_products')
                ->where('product_id', $product->id)
                ->where('box_id', $boxId)
                ->delete();
        } else {
            \DB::table('addon_group_products')
                ->where('product_id', $product->id)
                ->whereNull('box_id')
                ->delete();
        }

        // Then insert the new addons
        foreach ($addons as $addonId => $addon) {
            if (!isset($addon['id']) || !isset($addon['type'])) {
                continue;
            }

            $pivotData = [
                'product_id' => $product->id,
                'addon_id' => (int)$addon['id'],
                'type' => $addon['type'],
                'order' => isset($addon['order']) ? (int)$addon['order'] : 0,
                'addon_group_id' => isset($addon['addon_group_id']) ? (int)$addon['addon_group_id'] : null,
                'box_id' => $boxId,
            ];
            
            \DB::table('addon_group_products')->insert($pivotData);
        }
    }
    
    private function syncAddonsFromGroupBlocks(Product $product, array $groupBlocks, ?int $boxId = null): void
    {
        // Get existing addons to preserve those not in the form data
        $query = \DB::table('addon_group_products')
            ->where('product_id', $product->id);
        
        if ($boxId) {
            $query->where('box_id', $boxId);
        } else {
            $query->whereNull('box_id');
        }
        
        $existingAddons = $query->get();
        
        // If group_blocks is empty, preserve all existing addons and return
        if (empty($groupBlocks)) {
            // All existing addons are already in the database, no need to do anything
            return;
        }
        
        // Get group IDs from submitted form data
        $submittedGroupIds = [];
        foreach ($groupBlocks as $block) {
            if (isset($block['group_id'])) {
                $submittedGroupIds[] = (int)$block['group_id'];
            }
        }
        
        // Delete only addons from groups that are in the submitted form
        // This allows us to update them, while preserving addons from removed groups
        $addonIdsToDelete = [];
        foreach ($existingAddons as $addon) {
            $addonGroupId = $addon->addon_group_id ?? null;
            // Delete addons from groups that are in the submitted form (we'll re-insert them)
            if ($addonGroupId && in_array($addonGroupId, $submittedGroupIds)) {
                $addonIdsToDelete[] = $addon->addon_id;
            }
        }
        
        if (!empty($addonIdsToDelete)) {
            $deleteQuery = \DB::table('addon_group_products')
                ->where('product_id', $product->id)
                ->whereIn('addon_id', $addonIdsToDelete);
            
            if ($boxId) {
                $deleteQuery->where('box_id', $boxId);
            } else {
                $deleteQuery->whereNull('box_id');
            }
            
            $deleteQuery->delete();
        }
        
        // Sort group blocks by order
        usort($groupBlocks, function($a, $b) {
            return ($a['order'] ?? 0) - ($b['order'] ?? 0);
        });
        
        // Prepare new addons to insert
        $addonsToInsert = [];
        
        foreach ($groupBlocks as $blockIndex => $block) {
            $groupId = $block['group_id'] ?? null;
            $blockOrder = $block['order'] ?? $blockIndex;
            $addons = $block['addons'] ?? [];
            
            // Filter only checked addons (those with 'id' set)
            $checkedAddons = array_filter($addons, function($addon) {
                return isset($addon['id']);
            });
            
            // Sort addons by order within the block
            uasort($checkedAddons, function($a, $b) {
                return ($a['order'] ?? 0) - ($b['order'] ?? 0);
            });
            
            foreach ($checkedAddons as $addonId => $addonData) {
                if (!isset($addonData['id'])) {
                    continue;
                }
                
                // Calculate final order: group block order * 10000 + addon order within block
                $finalOrder = ($blockOrder * 10000) + ($addonData['order'] ?? 0);
                
                $addonsToInsert[] = [
                    'product_id' => $product->id,
                    'addon_id' => (int)$addonData['id'],
                    'type' => $addonData['type'] ?? 'optional',
                    'order' => $finalOrder,
                    'addon_group_id' => $groupId ? (int)$groupId : (isset($addonData['addon_group_id']) ? (int)$addonData['addon_group_id'] : null),
                    'box_id' => $boxId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Insert new/updated addons (preserved addons remain untouched)
        if (!empty($addonsToInsert)) {
            \DB::table('addon_group_products')->insert($addonsToInsert);
        }
    }
    
    private function syncAddonsFromTypeBlocks(Product $product, array $typeBlocks, ?int $boxId = null): void
    {
        // First, remove all existing addons for this product/box
        if ($boxId) {
            \DB::table('addon_group_products')
                ->where('product_id', $product->id)
                ->where('box_id', $boxId)
                ->delete();
        } else {
            \DB::table('addon_group_products')
                ->where('product_id', $product->id)
                ->whereNull('box_id')
                ->delete();
        }
        
        // Sort type blocks by order
        usort($typeBlocks, function($a, $b) {
            return ($a['order'] ?? 0) - ($b['order'] ?? 0);
        });
        
        foreach ($typeBlocks as $blockIndex => $block) {
            $type = $block['type'] ?? 'optional';
            $blockOrder = $block['order'] ?? $blockIndex;
            $addons = $block['addons'] ?? [];
            
            // Sort addons by order within the block
            uasort($addons, function($a, $b) {
                return ($a['order'] ?? 0) - ($b['order'] ?? 0);
            });
            
            foreach ($addons as $addonId => $addonData) {
                if (!isset($addonData['id'])) {
                    continue;
                }
                
                // Calculate final order: type block order * 10000 + addon order within block
                $finalOrder = ($blockOrder * 10000) + ($addonData['order'] ?? 0);
                
                $pivotData = [
                    'product_id' => $product->id,
                    'addon_id' => (int)$addonData['id'],
                    'type' => $type,
                    'order' => $finalOrder,
                    'addon_group_id' => isset($addonData['addon_group_id']) ? (int)$addonData['addon_group_id'] : null,
                    'box_id' => $boxId,
                ];
                
                \DB::table('addon_group_products')->insert($pivotData);
            }
        }
    }



    private function syncDefinitions(Product $product, array $definitions, bool $replace = false): void
    {
        if ($replace) {
            $product->properties()->detach();
        }
        $syncData = [];
        foreach ($definitions as $def) {
            $value = $def['value'] ?? null;
            
            // Round calories to integer
            if ($value !== null) {
                $definition = \App\Models\ProductDefinition::find($def['id']);
                if ($definition && $definition->key === 'calories') {
                    $value = (int) round($value);
                }
            }
            
            $syncData[(int)$def['id']] = ['value' => $value];
        }
        $product->properties()->sync($syncData);
    }

    private function syncBoxTitles(Product $product, array $boxTitles, array $productAddons = []): void
    {
        $syncData = [];
        $productAddons = is_array($productAddons) ? $productAddons : [];
        
        // Delete all existing box products
        $product->products()->detach();
        
        foreach ($boxTitles as $titleIndex => $titleData) {
            $title = $titleData['title'] ?? [];
            $products = $titleData['products'] ?? [];
            $isRequired = isset($titleData['is_required']) ? (bool)$titleData['is_required'] : false;
            $maxCount = isset($titleData['max_count']) && $titleData['max_count'] !== '' ? (int)$titleData['max_count'] : 1;
            // Always get min_count if provided, even if is_required is false
            $minCount = isset($titleData['min_count']) && $titleData['min_count'] !== '' ? (int)$titleData['min_count'] : 0;
            // If is_required is true but min_count is 0, set it to 1 as default
            if ($isRequired && $minCount == 0) {
                $minCount = 1;
            }
            $order = isset($titleData['order']) ? (int)$titleData['order'] : $titleIndex;
            
            // Products are now just an array of IDs
            foreach ($products as $productId) {
                if (!$productId) continue;
                
                $subProduct = Product::find($productId);
                if (!$subProduct) {
                    continue;
                }
                
                // Sync addons for this product if any
                $keyString = (string)$productId;
                $addonsForSub = [];
                if (array_key_exists($keyString, $productAddons)) {
                    $addonsForSub = $productAddons[$keyString] ?? [];
                } elseif (array_key_exists((int)$productId, $productAddons)) {
                    $addonsForSub = $productAddons[(int)$productId] ?? [];
                }
                $this->syncAddons($subProduct, $addonsForSub, $product->id);
                
                // Add to sync data with title, is_required, max_count, min_count, and order (from title level)
                // JSON encode the title array for database storage
                $syncData[(int)$productId] = [
                    'title' => !empty($title) ? json_encode($title) : null,
                    'is_required' => $isRequired,
                    'max_count' => $maxCount,
                    'min_count' => $minCount,
                    'order' => $order,
                ];
            }
        }
        
        // Sync all products at once
        if (!empty($syncData)) {
            $product->products()->sync($syncData);
        }
    }
}
