<?php

namespace App\Services;

use App\Models\ProductDefinition;

class ProductDefinitionService
{
    public function getAll()
    {
        return ProductDefinition::all();
    }

    public function getById($id)
    {
        return ProductDefinition::findOrFail($id);
    }

    public function create(array $data)
    {
        if (!isset($data['key']) && isset($data['name']['en'])) {
            $data['key'] = \Illuminate\Support\Str::slug($data['name']['en']);
        }
        $productDefinition = ProductDefinition::create($data);
        return $productDefinition;
    }

    public function update(ProductDefinition $productDefinition, array $data)
    {
        if (!isset($data['key']) && isset($data['name']['en'])) {
            $data['key'] = \Illuminate\Support\Str::slug($data['name']['en']);
        }
        $productDefinition->update($data);
        return $productDefinition;
    }


    public function delete($id)
    {
        $productDefinition = ProductDefinition::findOrFail($id);
        return $productDefinition->delete();
    }
}
