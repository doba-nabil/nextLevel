<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);
        // Get branch ID from route parameter (Laravel resource routes use singular form)
        $branchId = $isUpdate ? ($this->route('branch') ?? $this->route()->parameter('branch')) : null;

        return [
            'name' => ['required', 'array'],
            'active' => ['required'],
            'name.*' => ['required', 'string', 'max:255'],
            'country_id' => ['required', Rule::exists('locations', 'id')->where('type', 'country')],
            'states' => ['nullable', 'array'],
            'states.*' => ['required', Rule::exists('locations', 'id')->where('type', 'state')],
            'cities' => ['required', 'array', 'min:1'],
            'cities.*' => [
                'required', 
                Rule::exists('locations', 'id')->where('type', 'city'),
                function ($attribute, $value, $fail) use ($branchId) {
                    // Check if city is already assigned to another branch
                    $city = \App\Models\Location::find($value);
                    $cityName = $city ? $city->getTranslation('name', app()->getLocale()) : __('admin.city');
                    
                    $query = \App\Models\Branch::whereHas('cities', function($q) use ($value) {
                        $q->where('locations.id', $value);
                    });
                    
                    if ($branchId) {
                        $query->where('branches.id', '!=', $branchId);
                    }
                    
                    $existingBranch = $query->first();
                    
                    if ($existingBranch) {
                        $branchName = $existingBranch->getTranslation('name', app()->getLocale());
                        $fail(__('admin.city_name_already_assigned_to_branch', ['city' => $cityName, 'branch' => $branchName]));
                    }
                }
            ],
            'address' => ['required', 'array'],
            'address.*' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'active' => ['boolean'],
            'working_hours' => ['array'],
            'working_hours.*.from_day' => ['required', 'string', Rule::in(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])],
            'working_hours.*.to_day' => ['required', 'string', Rule::in(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])],
            'working_hours.*.from_time' => ['nullable', 'date_format:H:i'],
            'working_hours.*.to_time' => ['nullable', 'date_format:H:i'],
            'username' => ['required', 'string', 'max:20' , 'unique:users,email,'.$branchId],
            'password' => $this->isMethod('POST') ? 'required|string|min:6' : 'nullable|string|min:6',
            'armada_key' => ['nullable', 'string', 'max:255'],
        ];
    }
}
