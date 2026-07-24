<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Location;

class LocationService
{
    public function getAll($type)
    {
        return Location::where('type', $type)->get();
    }

    public function getById($id)
    {
        return Location::findOrFail($id);
    }

    public function create(array $data)
    {
        $location = Location::create($data);
        if($data['type'] == 'country'){
            if (!isset($data['key']) && isset($data['currency_name']['en'])) {
                $data['key'] = \Illuminate\Support\Str::slug($data['currency_name']['en']);
            }
            $currencyData = [
                'name' => $data['currency_name'],
                'key'  => $data['key'],
                'sign' => $data['sign'],
                'location_id' => $location->id,

                'rate_per_point' => isset($data['rate_per_point']) ? $data['rate_per_point'] : 0,
                'points_per_currency' => isset($data['points_per_currency']) ? $data['points_per_currency'] : 0,
                'minimum_usable_points' => isset($data['minimum_usable_points']) ? $data['minimum_usable_points'] : 0,
            ];
            Currency::create($currencyData);
        }
        return $location;
    }

    public function update(Location $location, array $data)
    {
        $location->update($data);
        if($location->type == 'country'){
            if (!isset($data['key']) && isset($data['currency_name']['en'])) {
                $data['key'] = \Illuminate\Support\Str::slug($data['currency_name']['en']);
            }
            $currency = $location->currency;
            $currencyData = [
                'name' => $data['currency_name'],
                'key'  => $data['key'],
                'sign' => $data['sign'],
                'location_id' => $location->id,

                'rate_per_point' => isset($data['rate_per_point']) ? $data['rate_per_point'] : 0,
                'points_per_currency' => isset($data['points_per_currency']) ? $data['points_per_currency'] : 0,
                'minimum_usable_points' => isset($data['minimum_usable_points']) ? $data['minimum_usable_points'] : 0,
            ];
            if($currency){
                $currency->update($currencyData);
            }
            if(!$currency){
                Currency::create($currencyData);
            }
        }
        return $location;
    }


    public function delete($id)
    {
        $location = Location::findOrFail($id);
        return $location->delete();
    }
}
