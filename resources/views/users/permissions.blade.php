@extends('layouts.app')

@section('title', 'Manage Permissions')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            Manage Permissions - {{ $user->name }}
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
            Assign specific permissions to this user. Admins have all permissions by default.
        </p>
    </div>
    
    @if(session('success'))
    <div class="mx-4 mt-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md p-4">
        <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
    </div>
    @endif
    
    <form method="POST" action="{{ route('users.updatePermissions', $user->id) }}">
        @csrf
        @method('PUT')
        
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($allPermissions as $permission)
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permission->id }}"
                            id="permission_{{ $permission->id }}"
                            {{ in_array($permission->id, $userPermissions) ? 'checked' : '' }}
                            class="h-4 w-4 text-indigo-600 dark:text-indigo-400 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded focus:ring-indigo-500"
                        >
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="permission_{{ $permission->id }}" class="font-medium text-gray-700 dark:text-gray-300">
                            {{ $permission->name }}
                        </label>
                        @if($permission->description)
                        <p class="text-gray-500 dark:text-gray-400">{{ $permission->description }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            
            @if($allPermissions->isEmpty())
            <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                No permissions available. Run the seeder to create default permissions.
            </p>
            @endif
        </div>
        
        <div class="px-4 py-3 sm:px-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 flex justify-end space-x-3">
            <a href="{{ route('users.show', $user->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Save Permissions
            </button>
        </div>
    </form>
</div>
@endsection