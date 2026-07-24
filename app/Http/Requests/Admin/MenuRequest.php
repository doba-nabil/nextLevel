<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
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
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'content' => ['sometimes', 'array'],
            'content.en' => ['nullable', 'string'],
            'content.ar' => ['nullable', 'string'],
            'is_active' => ['required'],
            'products' => ['sometimes', 'array'],
            'products.*' => ['exists:products,id'],
            'products_data' => ['sometimes', 'string', 'json'],
            'image' => [
                $isUpdate ? 'sometimes' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ]
        ];
    }
}


