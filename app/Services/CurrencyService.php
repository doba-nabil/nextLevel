<?php

namespace App\Services;

use App\Models\Currency;

class CurrencyService
{
    public function getAll()
    {
        return Currency::all();
    }

    public function getById($id)
    {
        return Currency::findOrFail($id);
    }

    public function create(array $data)
    {
        if (!isset($data['key']) && isset($data['name']['en'])) {
            $data['key'] = \Illuminate\Support\Str::slug($data['name']['en']);
        }
        $currency = Currency::create($data);
        return $currency;
    }

    public function update(Currency $currency, array $data)
    {
        if (!isset($data['key']) && isset($data['name']['en'])) {
            $data['key'] = \Illuminate\Support\Str::slug($data['name']['en']);
        }
        $currency->update($data);
        return $currency;
    }


    public function delete($id)
    {
        $currency = Currency::findOrFail($id);
        return $currency->delete();
    }
}
