<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:20', 'unique:coupons,code,' . $this->coupon?->id],
            'type' => ['required', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_price' => ['required', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expire_at' => ['nullable', 'date', 'after_or_equal:today'],
            'user_id' => ['nullable', 'exists:users,id'],
            'active' => ['required'],
        ];
    }
}
