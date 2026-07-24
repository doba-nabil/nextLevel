<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Currency;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'currencies' => Currency::all(),
            // Convert select values to boolean
            'is_pickup' => (bool)($this->is_pickup ?? 0),
            'is_trending' => (bool)($this->is_trending ?? 0),
            'is_new_plates' => (bool)($this->is_new_plates ?? 0),
            'show_in_limit_offer' => (bool)($this->show_in_limit_offer ?? 0),
        ]);
    }

    public function rules(): array
    {
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);
        $rules = [
            'name' => 'required|array',
            'name.*' => 'required|string|min:3|max:255',

            'ingrediant_text' => 'required|array',
            'ingrediant_text.*' => 'required|string|min:3|max:255',

            'active' => ['required'],
            'product_type' => ['required'],
            'type' => ['required', 'in:pickup,delivery,both'],
            'is_pickup' => ['sometimes', 'boolean'],
            'is_trending' => ['sometimes', 'boolean'],
            'is_new_plates' => ['sometimes', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
            'show_in_limit_offer' => ['sometimes', 'boolean'],

            'description' => 'nullable|array',
            'description.*' => 'nullable|string|max:1000',

            'category_id' => 'nullable|exists:categories,id',

            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',

            'addons' => 'nullable|array',
            'addons.*.id' => 'required_with:addons|exists:addons,id',
            'addons.*.addon_group_id' => 'nullable|exists:addon_groups,id',
            'addons.*.type' => 'required_with:addons.*.id|in:optional,mandatory',
            'addons.*.order' => 'nullable|integer|min:0',
            'group_order' => 'nullable|array',
            'group_order.*' => 'nullable|integer|min:0',
            'type_blocks' => 'nullable|array',
            'type_blocks.*.type' => 'required|in:optional,mandatory',
            'type_blocks.*.order' => 'nullable|integer|min:0',
            'type_blocks.*.addons' => 'nullable|array',
            'type_blocks.*.addons.*.id' => 'required|exists:addons,id',
            'type_blocks.*.addons.*.addon_group_id' => 'nullable|exists:addon_groups,id',
            'type_blocks.*.addons.*.order' => 'nullable|integer|min:0',
            'group_blocks' => 'nullable|array',
            'group_blocks.*.group_id' => 'required|exists:addon_groups,id',
            'group_blocks.*.order' => 'nullable|integer|min:0',
            'group_blocks.*.addons' => 'nullable|array',
            'group_blocks.*.addons.*.id' => 'nullable|exists:addons,id',
            'group_blocks.*.addons.*.addon_group_id' => 'nullable|exists:addon_groups,id',
            'group_blocks.*.addons.*.type' => 'nullable|in:optional,mandatory',
            'group_blocks.*.addons.*.order' => 'nullable|integer|min:0',

            'definitions' => 'nullable|array',
            'definitions.*.value' => 'required|string',
            'definitions.*.id' => 'required|exists:product_definitions,id',
        ];

        if ($this->currencies) {
            foreach ($this->currencies as $currency) {
                $rules["prices.{$currency->id}.discount_type"] = 'required|in:none,fixed,percentage';
                $rules["prices.{$currency->id}.before"] = 'required|numeric|min:0';
                $rules["prices.{$currency->id}.after"] = 'nullable|numeric|min:0|required_if:prices.' . $currency->id . '.discount_type,fixed';
                $rules["prices.{$currency->id}.discount_percentage"] = 'nullable|numeric|min:0|max:100|required_if:prices.' . $currency->id . '.discount_type,percentage';
            }
        }

        return $rules;
    }
}
