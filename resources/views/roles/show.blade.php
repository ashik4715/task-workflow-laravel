@extends('layouts.app')

@section('title', 'Manage Role Permissions - ' . $role->name)

@section('content')
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                Set permissions for Role: <span class="text-indigo-600">{{ $role->name }}</span>
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                Assign specific permissions to this role. Users with this role will inherit these permissions.
            </p>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md p-4">
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('roles.updatePermissions', $role->id) }}">
            @csrf
            @method('PUT')

            <div class="px-4 py-5 sm:p-6">
                <div class="mb-4 flex flex-wrap gap-2">
                    <button type="button" onclick="selectAll()"
                        class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">Select All</button>
                    <span class="text-gray-400">|</span>
                    <button type="button" onclick="clearAll()"
                        class="text-sm text-red-600 hover:text-red-800 dark:text-red-400">Clear All</button>
                    <span class="text-gray-400">|</span>
                    <button type="button" onclick="selectByAction('add')"
                        class="text-sm text-green-600 hover:text-green-800">Select Add/Create/New</button>
                    <span class="text-gray-400">|</span>
                    <button type="button" onclick="selectByAction('view')"
                        class="text-sm text-blue-600 hover:text-blue-800">Select View/List</button>
                    <span class="text-gray-400">|</span>
                    <button type="button" onclick="selectByAction('edit')"
                        class="text-sm text-yellow-600 hover:text-yellow-800">Select Edit</button>
                    <span class="text-gray-400">|</span>
                    <button type="button" onclick="selectByAction('delete')"
                        class="text-sm text-red-600 hover:text-red-800">Select Delete</button>
                </div>

                @php
                    $categories = [
                        'Task Management' => ['task.all', 'task.create', 'task.view', 'task.view_any', 'task.edit', 'task.edit_any', 'task.delete', 'task.delete_any', 'task.approve', 'task.reject'],
                        'Comment Management' => ['comment.create', 'comment.delete'],
                        'User Management' => ['user.all', 'user.view', 'user.edit', 'user.delete'],
                        'Dashboard Management' => ['dashboard.all', 'dashboard.view', 'dashboard.edit', 'dashboard.delete'],
                        'Audit & Reports' => ['audit.view'],
                    ];
                @endphp

                @foreach($categories as $category => $perms)
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ $category }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($perms as $permName)
                                @php
                                    $perm = $allPermissions->firstWhere('name', $permName);
                                @endphp
                                @if($perm)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                id="permission_{{ $perm->id }}" {{ in_array($perm->id, $rolePermissions) ? 'checked' : '' }}
                                                class="permission-checkbox h-4 w-4 text-indigo-600 dark:text-indigo-400 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded focus:ring-indigo-500">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="permission_{{ $perm->id }}"
                                                class="font-medium text-gray-700 dark:text-gray-300">
                                                {{ $perm->name }}
                                            </label>
                                            @if($perm->description)
                                                <p class="text-gray-500 dark:text-gray-400">{{ $perm->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @if($allPermissions->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                        No permissions available. Run the seeder to create default permissions.
                    </p>
                @endif
            </div>

            <div
                class="px-4 py-3 sm:px-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 flex justify-between items-center">
                <a href="{{ route('roles.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Back to Roles
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Save Permissions
                </button>
            </div>
        </form>
    </div>

    <script>
        function selectAll() {
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
        }

        function clearAll() {
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        }

        function selectByAction(action) {
            document.querySelectorAll('.permission-checkbox').forEach(cb => {
                const label = document.querySelector(`label[for="${cb.id}"]`);
                if (label && label.textContent.toLowerCase().includes(action)) {
                    cb.checked = true;
                }
            });
        }
    </script>
@endsection