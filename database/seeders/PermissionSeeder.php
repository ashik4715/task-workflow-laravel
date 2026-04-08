<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'task.create', 'description' => 'Create new tasks'],
            ['name' => 'task.view', 'description' => 'View own tasks'],
            ['name' => 'task.view_any', 'description' => 'View all tasks'],
            ['name' => 'task.edit', 'description' => 'Edit own tasks'],
            ['name' => 'task.edit_any', 'description' => 'Edit any task'],
            ['name' => 'task.delete', 'description' => 'Delete own tasks'],
            ['name' => 'task.delete_any', 'description' => 'Delete any task'],
            ['name' => 'task.approve', 'description' => 'Approve tasks'],
            ['name' => 'task.reject', 'description' => 'Reject tasks'],
            ['name' => 'comment.create', 'description' => 'Add comments to tasks'],
            ['name' => 'comment.delete', 'description' => 'Delete comments'],
            ['name' => 'user.view', 'description' => 'View user profiles'],
            ['name' => 'user.edit', 'description' => 'Edit user profiles'],
            ['name' => 'user.delete', 'description' => 'Delete users'],
            ['name' => 'audit.view', 'description' => 'View audit logs'],
            ['name' => 'dashboard.view', 'description' => 'View dashboard'],
            ['name' => 'dashboard.edit', 'description' => 'Edit dashboard'],
            ['name' => 'dashboard.delete', 'description' => 'Delete dashboard'],
            ['name' => 'dashboard.all', 'description' => 'All dashboard actions'],
            ['name' => 'user.all', 'description' => 'All user actions'],
            ['name' => 'task.all', 'description' => 'All task actions'],
            ['name' => 'role.manage', 'description' => 'Manage roles'],
            ['name' => 'permission.manage', 'description' => 'Manage permissions'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}