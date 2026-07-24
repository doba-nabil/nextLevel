<?php

namespace App\Services;

use App\Models\Product;
use App\Traits\mediaUploader;
use App\Traits\slugGenerator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    use mediaUploader, slugGenerator;

    public function getAll()
    {
        return Product::all();
    }

    public function findOrFail(int $id): Product
    {
        return Product::findOrFail($id);
    }

    public function create(array $data, $image = null): Product
    {
        return DB::transaction(function () use ($data, $image) {
            $data['slug'] = $this->generateSlug($data);

            $product = Product::create($data);

            $this->syncPrices($product, $data['prices'] ?? []);
            // Check if using new group_blocks system
            if (isset($data['group_blocks'])) {
                $this->syncAddons($product, ['group_blocks' => $data['group_blocks']]);
            } elseif (isset($data['type_blocks'])) {
                $this->syncAddons($product, ['type_blocks' => $data['type_blocks']]);
            } else {
                $this->syncAddons($product, $data['addons'] ?? [], $data['group_order'] ?? []);
            }
            $this->syncDefinitions($product, $data['definitions'] ?? []);
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

            $this->syncPrices($product, $data['prices'] ?? [], true);
            // Check if using new group_blocks system
            // Always use group_blocks if it exists (even if empty array), to preserve existing addons
            if (array_key_exists('group_blocks', $data)) {
                $this->syncAddons($product, ['group_blocks' => $data['group_blocks'] ?? []]);
            } elseif (isset($data['type_blocks'])) {
                $this->syncAddons($product, ['type_blocks' => $data['type_blocks']]);
            } else {
                $this->syncAddons($product, $data['addons'] ?? [], $data['group_order'] ?? []);
            }
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

    public function delete(Product $product): bool
    {
        return $product->delete();
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


    private function syncAddons(Product $product, array $addons, array $groupOrder = []): void
    {
        // New group-based system
        if (isset($addons['group_blocks'])) {
            $this->syncAddonsFromGroupBlocks($product, $addons['group_blocks']);
            return;
        }
        
        // Legacy type-based system
        if (isset($addons['type_blocks'])) {
            $this->syncAddonsFromTypeBlocks($product, $addons['type_blocks']);
            return;
        }
        
        // Legacy system (fallback)
        // If addons array is empty, preserve all existing addons
        if (empty($addons)) {
            $existingAddons = $product->addons()->get()->keyBy('id');
            $syncData = [];
            foreach ($existingAddons as $addonId => $addon) {
                $syncData[$addonId] = [
                    'type' => $addon->pivot->type ?? 'optional',
                    'order' => $addon->pivot->order ?? 0,
                    'addon_group_id' => $addon->pivot->addon_group_id ?? null,
                ];
            }
            $product->addons()->sync($syncData);
            return;
        }
        
        $syncData = [];
        $groupOrderMap = [];
        
        // Build group order map
        foreach ($groupOrder as $groupId => $order) {
            $groupOrderMap[(int)$groupId] = (int)$order;
        }
        
        foreach ($addons as $addonId => $addon) {
            // Skip if id is not set or if it's not a valid addon entry
            if (!isset($addon['id']) || !isset($addon['type'])) {
                continue;
            }
            
            $groupId = isset($addon['addon_group_id']) ? (int)$addon['addon_group_id'] : null;
            $addonOrder = isset($addon['order']) ? (int)$addon['order'] : 0;
            
            // Calculate final order: group order * 1000 + addon order within group
            // This ensures groups are ordered first, then addons within groups
            $finalOrder = 0;
            if ($groupId && isset($groupOrderMap[$groupId])) {
                $finalOrder = ($groupOrderMap[$groupId] * 1000) + $addonOrder;
            } else {
                $finalOrder = (999 * 1000) + $addonOrder; // Groups without order go to end
            }
            
            $syncData[(int)$addon['id']] = [
                'type' => $addon['type'],
                'order' => $finalOrder,
                'addon_group_id' => $groupId,
            ];
        }
        $product->addons()->sync($syncData);
    }
    
    private function syncAddonsFromGroupBlocks(Product $product, array $groupBlocks): void
    {
        // Get existing addons to preserve those not in the form data
        $existingAddons = $product->addons()->get()->keyBy('id');
        $syncData = [];
        
        // If group_blocks is empty, preserve all existing addons and return
        if (empty($groupBlocks)) {
            foreach ($existingAddons as $addonId => $addon) {
                $syncData[$addonId] = [
                    'type' => $addon->pivot->type ?? 'optional',
                    'order' => $addon->pivot->order ?? 0,
                    'addon_group_id' => $addon->pivot->addon_group_id ?? null,
                ];
            }
            $product->addons()->sync($syncData);
            return;
        }
        
        // Collect all addon IDs that are explicitly in the submitted form
        $submittedAddonIds = [];
        $submittedGroupIds = [];
        
        // Sort group blocks by order
        usort($groupBlocks, function($a, $b) {
            return ($a['order'] ?? 0) - ($b['order'] ?? 0);
        });
        
        // Process submitted group blocks and collect submitted addon IDs
        foreach ($groupBlocks as $blockIndex => $block) {
            $groupId = $block['group_id'] ?? null;
            if ($groupId) {
                $submittedGroupIds[] = (int)$groupId;
            }
            
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
                
                $addonIdInt = (int)$addonData['id'];
                $submittedAddonIds[] = $addonIdInt;
                
                // Calculate final order: group block order * 10000 + addon order within block
                $finalOrder = ($blockOrder * 10000) + ($addonData['order'] ?? 0);
                
                $syncData[$addonIdInt] = [
                    'type' => $addonData['type'] ?? 'optional',
                    'order' => $finalOrder,
                    'addon_group_id' => $groupId ? (int)$groupId : (isset($addonData['addon_group_id']) ? (int)$addonData['addon_group_id'] : null),
                ];
            }
        }
        
        // Preserve existing addons that are NOT in the submitted form
        // This includes:
        // 1. Addons from groups not in the submitted form (group block was removed)
        // 2. Addons from groups in the form but not checked (should be preserved if they existed before)
        foreach ($existingAddons as $addonId => $addon) {
            // Only preserve if this addon is not in the submitted form
            if (!in_array($addonId, $submittedAddonIds)) {
                $addonGroupId = $addon->pivot->addon_group_id ?? null;
                $syncData[$addonId] = [
                    'type' => $addon->pivot->type ?? 'optional',
                    'order' => $addon->pivot->order ?? 0,
                    'addon_group_id' => $addonGroupId,
                ];
            }
        }
        
        $product->addons()->sync($syncData);
    }
    
    private function syncAddonsFromTypeBlocks(Product $product, array $typeBlocks): void
    {
        $syncData = [];
        
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
                
                $syncData[(int)$addonData['id']] = [
                    'type' => $type,
                    'order' => $finalOrder,
                    'addon_group_id' => isset($addonData['addon_group_id']) ? (int)$addonData['addon_group_id'] : null,
                ];
            }
        }
        
        $product->addons()->sync($syncData);
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
}
