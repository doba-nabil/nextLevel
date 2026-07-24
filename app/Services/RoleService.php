<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;

class RoleService
{
    public function getAll()
    {
        return Role::all();
    }

    public function getById($id)
    {
        return Role::findOrFail($id);
    }

    public function create(array $data)
    {
        $data['name'] = Str::slug($data['display_name']['en']);
        $data['display_name'] = $data['display_name'];

        $role = Role::create($data);
        if (!empty($data['permissions'])) {
            $permissions = Permission::whereIn('id', $data['permissions'])->get();
            $role->syncPermissions($permissions);
        }
        return $role;
    }

    public function update(Role $role, array $data)
    {
        if (!empty($data['display_name']['en'])) {
            $data['name'] = Str::slug($data['display_name']['en']);
        }
        $role->update($data);
        if (!empty($data['permissions'])) {
            $permissions = Permission::whereIn('id', $data['permissions'])->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }
        return $role;
    }


    public function delete($id)
    {
        $role = Role::findOrFail($id);
        return $role->delete();
    }
}
