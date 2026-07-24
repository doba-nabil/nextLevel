<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Product;

class OfferService
{
    public function getAll()
    {
        return Offer::with('products')->get();
    }

    public function getById($id)
    {
        return Offer::with('products')->findOrFail($id);
    }

    public function create(array $data)
    {
        $productIds = $data['products'] ?? [];
        unset($data['products']);

        // Normalize dates: set start_date to 00:00:00 and end_date to 23:59:59
        if (isset($data['start_date'])) {
            $data['start_date'] = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
        }
        if (isset($data['end_date'])) {
            $data['end_date'] = \Carbon\Carbon::parse($data['end_date'])->endOfDay();
        }

        $offer = Offer::create($data);
        
        if (!empty($productIds)) {
            $offer->products()->sync($productIds);
        }

        return $offer;
    }

    public function update(Offer $offer, array $data)
    {
        $productIds = $data['products'] ?? [];
        unset($data['products']);

        // Normalize dates: set start_date to 00:00:00 and end_date to 23:59:59
        if (isset($data['start_date'])) {
            $data['start_date'] = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
        }
        if (isset($data['end_date'])) {
            $data['end_date'] = \Carbon\Carbon::parse($data['end_date'])->endOfDay();
        }

        $offer->update($data);
        
        if (isset($productIds)) {
            $offer->products()->sync($productIds);
        }

        return $offer;
    }

    public function delete($id)
    {
        $offer = Offer::findOrFail($id);
        return $offer->delete();
    }
}

