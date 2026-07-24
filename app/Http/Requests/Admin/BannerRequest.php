<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);
        return [
            'link' => ['nullable', 'url', 'max:500'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['required', 'boolean'],
            'image_ar' => [
                $isUpdate ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ],
            'image_en' => [
                $isUpdate ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ]
        ];
    }
}

