<?php

namespace App\Services;

use App\Models\Addon;

class AddonService
{
    public function getAll()
    {
        return Addon::all();
    }

    public function getById($id)
    {
        return Addon::findOrFail($id);
    }

    public function create(array $data)
    {
        $addon = Addon::create($data);
        $prices = $data['prices'] ?? [];
        unset($data['prices']);
        foreach ($prices as $currencyId => $price) {
            $addon->prices()->create([
                'currency_id' => $currencyId,
                'price' => $price,
            ]);
        }
        return $addon;
    }

    public function update(Addon $addon, array $data)
    {
        $addon->update($data);
        $prices = $data['prices'] ?? [];
        unset($data['prices']);
        foreach ($prices as $currencyId => $price) {
            $addon->prices()->updateOrCreate(
                ['currency_id' => $currencyId],
                ['price' => $price]
            );
        }
        return $addon;
    }


    public function delete($id)
    {
        $addon = Addon::findOrFail($id);
        return $addon->delete();
    }
}
