<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function (User $user) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        Gate::define('task.create', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('task.create');
        });

        Gate::define('task.view', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('task.view');
        });

        Gate::define('task.view_any', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('task.view_any');
        });

        Gate::define('task.edit', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('task.edit');
        });

        Gate::define('task.edit_any', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('task.edit_any');
        });

        Gate::define('task.delete', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('task.delete');
        });

        Gate::define('task.delete_any', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('task.delete_any');
        });

        Gate::define('task.approve', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('task.approve');
        });

        Gate::define('task.reject', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('task.reject');
        });

        Gate::define('comment.create', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('comment.create');
        });

        Gate::define('comment.delete', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('comment.delete');
        });

        Gate::define('user.view', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('user.view');
        });

        Gate::define('user.edit', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('user.edit');
        });

        Gate::define('user.delete', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('user.delete');
        });

        Gate::define('user.all', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('user.all');
        });

        Gate::define('dashboard.view', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('dashboard.view');
        });

        Gate::define('dashboard.all', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('dashboard.all');
        });

        Gate::define('audit.view', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('audit.view');
        });

        Gate::define('role.manage', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('role.manage');
        });

        Gate::define('permission.manage', function (User $user) {
            return $user->roleModel && $user->roleModel->hasPermission('permission.manage');
        });
    }
}