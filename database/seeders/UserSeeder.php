<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'ADMIN')->first();
        $userRole = Role::where('name', 'USER')->first();
        $brandManagerRole = Role::where('name', 'BRAND_MANAGER')->first();
        $managerRole = Role::where('name', 'MANAGER')->first();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'ADMIN',
            'role_id' => $adminRole ? $adminRole->id : null,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'USER',
            'role_id' => $userRole ? $userRole->id : null,
            'is_active' => true,
        ]);

        if ($brandManagerRole) {
            User::create([
                'name' => 'Brand Manager',
                'email' => 'brandmanager@example.com',
                'password' => Hash::make('password'),
                'role' => 'USER',
                'role_id' => $brandManagerRole->id,
                'is_active' => true,
            ]);
        }

        if ($managerRole) {
            User::create([
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'role' => 'USER',
                'role_id' => $managerRole->id,
                'is_active' => true,
            ]);
        }
    }
}