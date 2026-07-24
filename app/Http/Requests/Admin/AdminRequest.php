<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
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
        $adminId = null;
        
        $admin = $this->route('admin');
        if ($admin) {
            if (is_object($admin) && method_exists($admin, 'getKey')) {
                $adminId = (int) $admin->getKey();
            } elseif (is_numeric($admin)) {
                $adminId = (int) $admin;
            }
        }
        
        if (!$adminId) {
            $idParam = $this->route('id');
            if ($idParam) {
                if (is_object($idParam) && method_exists($idParam, 'getKey')) {
                    $adminId = (int) $idParam->getKey();
                } elseif (is_numeric($idParam)) {
                    $adminId = (int) $idParam;
                }
            }
        }
        
        if (!$adminId && $this->route()) {
            $parameters = $this->route()->parameters();
            $adminParam = $parameters['admin'] ?? $parameters['id'] ?? null;
            if ($adminParam) {
                if (is_object($adminParam) && method_exists($adminParam, 'getKey')) {
                    $adminId = (int) $adminParam->getKey();
                } elseif (is_numeric($adminParam)) {
                    $adminId = (int) $adminParam;
                }
            }
        }

        $emailRules = [
            'required',
            'string',
            'email',
            'max:255',
        ];
        
        // Get authenticated admin ID
        $authAdminId = auth('admin')->id();
        
        // Determine which ID to ignore for email uniqueness
        $ignoreId = null;
        
        // If we have an admin ID from route (updating existing admin)
        if ($adminId) {
            $ignoreId = $adminId;
        } 
        // If no route admin ID but we're updating (PUT/PATCH) and have authenticated admin
        // This handles case when admin is updating their own profile
        elseif (in_array($this->method(), ['PUT', 'PATCH']) && $authAdminId) {
            $ignoreId = $authAdminId;
        }
        
        // If we have an ID to ignore, use it
        if ($ignoreId) {
            $emailRules[] = Rule::unique('users', 'email')->ignore($ignoreId);
        } else {
            // For create (POST), just check uniqueness
            $emailRules[] = Rule::unique('users', 'email');
        }

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRules,
            'phone' => 'nullable|string|max:20',
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
            'status' => ['required'],
            'password' => $this->isMethod('POST') ? 'required|string|min:6|confirmed' : 'nullable|string|min:6|confirmed',
        ];
    }
}
