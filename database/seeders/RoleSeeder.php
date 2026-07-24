<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'super-admin'],
            ['display_name' => ['ar'=>'سوبر ادمن', 'en'=>'Super Admin']]
        );

        Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => ['ar'=>'ادمن', 'en'=>'Admin']]
        );

        Role::firstOrCreate(
            ['name' => 'member'],
            ['display_name' => ['ar'=>'مشرف عادي', 'en'=>'Normal admin']]
        );
    }
}
