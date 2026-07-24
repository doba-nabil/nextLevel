<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['required', 'boolean'],
            'products' => ['sometimes', 'array'],
            'products.*' => ['exists:products,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('admin.title') . ' ' . __('admin.required'),
            'discount_type.required' => __('admin.discount_type') . ' ' . __('admin.required'),
            'discount_value.required' => __('admin.discount') . ' ' . __('admin.required'),
            'discount_value.numeric' => __('admin.discount') . ' ' . __('admin.must_be_numeric'),
            'start_date.required' => __('admin.start_date') . ' ' . __('admin.required'),
            'end_date.required' => __('admin.end_date') . ' ' . __('admin.required'),
            'end_date.after' => __('admin.end_date') . ' ' . __('admin.must_be_after_start_date'),
        ];
    }
}

















