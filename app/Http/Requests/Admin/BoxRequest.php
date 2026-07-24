<?php

namespace App\Http\Requests\Admin;

use App\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;

class BoxRequest extends FormRequest
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

        $rules = [
            'name' => 'required|array',
            'name.*' => 'required|string|min:1|max:255',

            'ingrediant_text' => 'required|array',
            'ingrediant_text.*' => 'required|string|min:1|max:255',

            'active' => ['required'],
            'product_type' => ['required'],
            'type' => ['required', 'in:pickup,delivery,both'],
            'is_home' => ['sometimes', 'boolean'],

            'description' => 'nullable|array',
            'description.*' => 'nullable|string|max:1000',

            'category_id' => 'nullable|exists:categories,id',

            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',

            'addons' => 'nullable|array',
            'addons.*.id' => [$isUpdate ? 'sometimes' : 'required', 'exists:addons,id'],
            'addons.*.type' => [$isUpdate ? 'sometimes' : 'required', 'in:optional,mandatory'],
            'group_blocks' => 'nullable|array',
            'group_blocks.*.group_id' => 'required|exists:addon_groups,id',
            'group_blocks.*.order' => 'nullable|integer|min:0',
            'group_blocks.*.addons' => 'nullable|array',
            'group_blocks.*.addons.*.id' => 'nullable|exists:addons,id',
            'group_blocks.*.addons.*.addon_group_id' => 'nullable|exists:addon_groups,id',
            'group_blocks.*.addons.*.type' => 'nullable|in:optional,mandatory',
            'group_blocks.*.addons.*.order' => 'nullable|integer|min:0',

            'box_titles' => 'nullable|array',
            'box_titles.*.title' => 'required|array',
            'box_titles.*.title.*' => 'required|string|max:255',
            'box_titles.*.is_required' => 'sometimes|boolean',
            'box_titles.*.max_count' => 'required|integer|min:1|max:10',
            'box_titles.*.products' => 'required|array|min:1',
            'box_titles.*.products.*' => 'required|exists:products,id',

            'product_addons' => 'nullable|array',
            'product_addons.*.*.id' => ['required', 'exists:addons,id'],
            'product_addons.*.*.type' => ['required', 'in:optional,mandatory'],

            'definitions' => 'nullable|array',
            'definitions.*.id' => 'required|exists:product_definitions,id',
            'definitions.*.value' => 'required|string',

            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
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

    protected function prepareForValidation()
    {
        if ($this->has('products') && is_string($this->products)) {
            $products = array_filter(array_map('trim', explode(',', $this->products)));
            $this->merge(['products' => $products]);
        }
        if ($this->has('addons') && is_string($this->addons)) {
            $addons = json_decode($this->addons, true);
            $this->merge(['addons' => $addons ?? []]);
        }
        if ($this->has('definitions') && is_string($this->definitions)) {
            $definitions = json_decode($this->definitions, true);
            $this->merge(['definitions' => $definitions ?? []]);
        }
        if ($this->has('product_addons') && is_string($this->product_addons)) {
            $productAddons = json_decode($this->product_addons, true);
            $this->merge(['product_addons' => $productAddons ?? []]);
        }
        if ($this->has('currencies') && is_array($this->currencies)) {
            $this->attributes['currencies'] = $this->currencies;
        }
        
        // Handle box_titles is_required checkboxes (set to false if not present)
        if ($this->has('box_titles') && is_array($this->box_titles)) {
            $boxTitles = $this->box_titles;
            foreach ($boxTitles as $index => $titleData) {
                // If is_required is not set (checkbox unchecked), set it to false
                if (!isset($titleData['is_required'])) {
                    $boxTitles[$index]['is_required'] = false;
                } else {
                    // Convert to boolean (checkbox sends "1" when checked)
                    $boxTitles[$index]['is_required'] = (bool)$titleData['is_required'];
                }
            }
            $this->merge(['box_titles' => $boxTitles]);
        }
        
        $this->merge([
            'currencies' => Currency::all(),
            // Convert select value to boolean
            'is_home' => (bool)($this->is_home ?? 0),
        ]);
    }

}
