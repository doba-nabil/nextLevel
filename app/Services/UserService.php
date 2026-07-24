<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use App\Traits\mediaUploader;
use App\Traits\slugGenerator;

class UserService
{
    use mediaUploader, slugGenerator;

    public function getAll()
    {
        return User::all();
    }

    public function getById($id)
    {
        return User::findOrFail($id);
    }

    public function create(array $data, $image = null)
    {
        $user = User::create($data);
        $this->handleImage($user, $image, false,'users');
        return $user;
    }

    public function update(User $user, array $data, $image = null)
    {
        $updateData = [
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'phone' => $data['phone'] ?? $user->phone,
            'address' => $data['address'] ?? $user->address,
        ];
        
        // Only update status if it's provided (for admin/user management, not profile updates)
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }
        
        if (isset($data['points'])) {
            $updateData['points'] = $data['points'];
        }

        if (!empty($data['password'])) {
            $updateData['password'] = bcrypt($data['password']);
        }

        $user->update($updateData);
        $this->handleImage($user, $image, true, 'users');
        return $user;
    }


    public function delete($id)
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }
}
