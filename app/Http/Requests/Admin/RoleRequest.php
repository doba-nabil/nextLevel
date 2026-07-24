<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class RoleRequest extends FormRequest
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

    protected function prepareForValidation()
    {
        if ($this->filled('display_name.en')) {
            $this->merge([
                'name' => Str::slug($this->input('display_name.en')),
            ]);
        }
    }

    public function rules(): array
    {
        $roleId = $this->route('role');

        $slug = $this->filled('display_name.en') ? Str::slug($this->input('display_name.en')) : null;
        return [
            'display_name' => ['required', 'array'],
            'display_name.en' => ['required', 'string'],
            'display_name.ar' => ['required', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
            'name' => [
                'required',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],
        ];
    }

}
