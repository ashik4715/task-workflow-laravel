<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'ADMIN', 'description' => 'Administrator with full access', 'is_active' => true],
            ['name' => 'USER', 'description' => 'Regular user with limited access', 'is_active' => true],
            ['name' => 'MANAGER', 'description' => 'Manager with task approval rights', 'is_active' => true],
            ['name' => 'BRAND_MANAGER', 'description' => 'Brand Manager with specific brand-related permissions', 'is_active' => true],
        ];

        foreach ($roles as $role) {
            $roleModel = Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        $adminRole = Role::where('name', 'ADMIN')->first();
        if ($adminRole) {
            $adminPermissions = Permission::whereIn('name', [
                'task.create', 'task.view', 'task.view_any', 'task.edit', 'task.edit_any', 'task.delete', 'task.delete_any', 'task.approve', 'task.reject',
                'comment.create', 'comment.delete',
                'user.view', 'user.edit', 'user.delete', 'user.all',
                'audit.view',
                'dashboard.view', 'dashboard.edit', 'dashboard.delete', 'dashboard.all',
                'task.all'
            ])->get();
            $adminRole->permissions()->sync($adminPermissions->pluck('id'));
        }

        $managerRole = Role::where('name', 'MANAGER')->first();
        if ($managerRole) {
            $managerPermissions = Permission::whereIn('name', [
                'task.create', 'task.view', 'task.view_any', 'task.edit', 'task.delete',
                'task.approve', 'task.reject',
                'comment.create', 'comment.delete',
                'dashboard.view'
            ])->get();
            $managerRole->permissions()->sync($managerPermissions->pluck('id'));
        }

        $brandManagerRole = Role::where('name', 'BRAND_MANAGER')->first();
        if ($brandManagerRole) {
            $brandPermissions = Permission::whereIn('name', [
                'task.create', 'task.view', 'task.edit',
                'comment.create',
                'dashboard.view'
            ])->get();
            $brandManagerRole->permissions()->sync($brandPermissions->pluck('id'));
        }

        $userRole = Role::where('name', 'USER')->first();
        if ($userRole) {
            $userPermissions = Permission::whereIn('name', [
                'task.create', 'task.view', 'task.edit', 'task.delete',
                'comment.create',
                'dashboard.view'
            ])->get();
            $userRole->permissions()->sync($userPermissions->pluck('id'));
        }
    }
}