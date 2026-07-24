<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $type = $this->input('type');
        $rules = [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'active' => ['required'],
        ];
        switch ($type) {
            case 'country':
                $locationId = $this->route('location') ?? $this->route('country');
                $rules = array_merge($rules, [
                    'currency_name' => ['required', 'array'],
                    'currency_name.en' => ['required', 'string', 'max:255'],
                    'currency_name.ar' => ['required', 'string', 'max:255'],
                    'sign' => ['required', 'string', 'max:10'],
                    'minimum_usable_points' => ['sometimes', 'numeric'],
                    'rate_per_point' => ['sometimes', 'numeric'],
                    'points_per_currency' => ['sometimes', 'numeric'],
                    'phone_code' => ['required', 'string', 'max:10'],
                    'code' => ['required', 'string', 'size:2', Rule::unique('locations', 'code')->ignore($locationId)],
                ]);
                break;

            case 'city':
                $rules = array_merge($rules, [
                    'shipping_fee_near' => ['required', 'numeric'],
                    'shipping_fee_far' => ['required', 'numeric'],
                    'min_order_near' => ['required', 'numeric'],
                    'min_order_far' => ['required', 'numeric'],
                    'delivery_time' => ['required', 'numeric', 'min:0.01'],
                ]);
                break;

            case 'state':
                break;
        }
        if (!$isUpdate) {
            $rules['type'] = ['required', 'string', Rule::in(['country', 'state', 'city'])];
        }
        $rules['parent_id'] = [
            Rule::requiredIf(fn () => $type && $type !== 'country'),
            'nullable',
            Rule::exists('locations', 'id'),
        ];

        return $rules;
    }
}
