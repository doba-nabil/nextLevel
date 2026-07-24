<?php

namespace App\Services;

use App\Models\Menu;
use App\Traits\mediaUploader;
use App\Traits\slugGenerator;

class MenuService
{
    use mediaUploader, slugGenerator;

    public function getAll()
    {
        return Menu::with('products')->get();
    }

    public function getById($id)
    {
        return Menu::with('products')->findOrFail($id);
    }

    public function create(array $data, $image = null)
    {
        $data['slug'] = $this->generateSlug($data);
        $menu = Menu::create($data);
        $this->handleImage($menu, $image, false, 'menus');
        
        // Sync products if provided
        if (isset($data['products_data'])) {
            $productsData = json_decode($data['products_data'], true);
            if (is_array($productsData)) {
                $this->syncProducts($menu, $productsData);
            }
        } elseif (isset($data['products'])) {
            $this->syncProducts($menu, $data['products']);
        }
        
        return $menu;
    }

    public function update(Menu $menu, array $data, $image = null)
    {
        $data['slug'] = $this->generateSlug($data);
        $menu->update($data);
        $this->handleImage($menu, $image, true, 'menus');
        
        // Sync products if provided
        if (isset($data['products_data'])) {
            $productsData = json_decode($data['products_data'], true);
            if (is_array($productsData)) {
                $this->syncProducts($menu, $productsData);
            }
        } elseif (isset($data['products'])) {
            $this->syncProducts($menu, $data['products']);
        }
        
        return $menu;
    }

    public function delete($id)
    {
        $menu = Menu::findOrFail($id);
        return $menu->delete();
    }

    private function syncProducts(Menu $menu, array $products)
    {
        $syncData = [];
        foreach ($products as $index => $productData) {
            $productId = is_array($productData) ? $productData['id'] : $productData;
            $categoryId = is_array($productData) ? ($productData['category_id'] ?? null) : null;
            $showPrice = is_array($productData) ? ($productData['show_price'] ?? true) : true;
            
            $syncData[$productId] = [
                'order' => $index + 1,
                'show_price' => $showPrice,
                'category_id' => $categoryId ? (int)$categoryId : null
            ];
        }
        $menu->products()->sync($syncData);
    }
}


