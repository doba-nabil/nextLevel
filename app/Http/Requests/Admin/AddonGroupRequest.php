<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddonGroupRequest extends FormRequest
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
            'max_items' => ['required'],
            'active' => ['required'],
            'is_selection_mandatory' => ['nullable', 'boolean'],
            'max_selections' => ['nullable', 'integer', 'min:1'],
            'min_selections' => [
                'nullable',
                'integer',
                'min:1',
                'required_if:is_selection_mandatory,1',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !empty($this->max_selections) && $value > $this->max_selections) {
                        $fail(__('admin.min_selections_cannot_exceed_max'));
                    }
                }
            ],
        ];
    }
}
