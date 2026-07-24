<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SliderRequest extends FormRequest
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
            'small_title' => ['nullable', 'array'],
            'small_title.en' => ['nullable', 'string', 'max:255'],
            'small_title.ar' => ['nullable', 'string', 'max:255'],
            'big_title' => ['nullable', 'array'],
            'big_title.en' => ['nullable', 'string', 'max:255'],
            'big_title.ar' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'array'],
            'content.en' => ['nullable', 'string', 'max:500'],
            'content.ar' => ['nullable', 'string', 'max:500'],
            'active' => ['required'],
            'url' => ['sometimes', 'url'],
            'image_ar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ],
            'image_en' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ]
        ];
    }
}
