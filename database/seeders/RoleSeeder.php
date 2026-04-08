<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'ADMIN', 'description' => 'Administrator with full access', 'is_active' => true],
            ['name' => 'USER', 'description' => 'Regular user with limited access', 'is_active' => true],
            ['name' => 'MANAGER', 'description' => 'Manager with task approval rights', 'is_active' => true],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}