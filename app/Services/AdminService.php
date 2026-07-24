<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use App\Traits\mediaUploader;
use App\Traits\slugGenerator;

class AdminService
{
    use mediaUploader, slugGenerator;

    public function getAll()
    {
        return User::with('roles')->get();
    }

    public function getById($id)
    {
        return User::findOrFail($id);
    }

    public function create(array $data, $image = null)
    {
        $roles = $data['roles'] ?? [];
        $data['is_admin'] = 1;
        $user = User::create($data);
        if ($roles) {
            $roles = Role::whereIn('id', $data['roles'])->get();
            $user->syncRoles($roles);
        }
        $this->handleImage($user, $image, false,'users');
        return $user;
    }

    public function update(User $user, array $data, $image = null)
    {
        $updateData = [
            'roles' => $data['roles'] ?? [],
            'is_admin' => 1,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = bcrypt($data['password']);
        }

        $user->update($updateData);
        if ($updateData['roles']) {
            $roles = Role::whereIn('id', $data['roles'])->get();
            $user->syncRoles($roles);
        }
        $this->handleImage($user, $image, true, 'users');
        return $user;
    }


    public function delete($id)
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }
}
