<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * Resolve the value of a field for display in audit logs.
     * Converts IDs to Model Names where possible.
     *
     * @param string $field
     * @param mixed $value
     * @return string
     */
    public static function resolveValue($field, $value)
    {
        if (empty($value)) {
            return $value;
        }

        // Handle ID fields
        if (Str::endsWith($field, '_id')) {
            return self::resolveRelationship($field, $value);
        }

        // Handle other specific fields if necessary (e.g., status enums)

        return $value;
    }

    protected static function resolveRelationship($field, $id)
    {
        // Guess model name from field
        // e.g. user_id -> User, category_id -> Category
        $modelName = Str::studly(Str::beforeLast($field, '_id'));

        // Handle specific map overrides if standard naming doesn't apply
        $map = [
            'parent_id' => 'Category', // Assuming parent_id usually refers to recursive Category
            'city_id' => 'Location',
            'state_id' => 'Location',
            'country_id' => 'Location',
            'role_id' => 'Role',
        ];

        if (isset($map[$field])) {
            $modelName = $map[$field];
        }

        // Try to find the model class
        $modelClass = "App\\Models\\{$modelName}";

        // Check for Spatie Permissions/Roles if not found in App\Models
        if (!class_exists($modelClass)) {
            if ($modelName === 'Role') {
                $modelClass = "Spatie\\Permission\\Models\\Role";
            } elseif ($modelName === 'Permission') {
                $modelClass = "Spatie\\Permission\\Models\\Permission";
            }
        }

        if (class_exists($modelClass)) {
            try {
                $instance = $modelClass::find($id);
                if ($instance) {
                    // Try common name attributes
                    if ($modelName === 'User') {
                        return $instance->name . " (#$id)";
                    }

                    // Check for translatable name
                    if (method_exists($instance, 'getTranslation')) {
                        // Try current locale, fallback to 'en' or 'ar'
                        try {
                            return $instance->name; // Accessing name on translatable model usually returns translated string if trait is used
                        } catch (\Exception $e) {
                            // Fallback if accessor fails
                        }
                    }

                    $attributes = ['name', 'title', 'name_en', 'name_ar', 'code', 'slug'];
                    foreach ($attributes as $attr) {
                        if (!empty($instance->$attr)) {
                            return $instance->$attr . " (#$id)";
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log::warning("AuditService: Failed to resolve {$modelClass} with ID {$id}");
            }
        }

        return $id;
    }
}
